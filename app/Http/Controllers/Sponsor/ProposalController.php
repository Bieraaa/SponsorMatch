<?php

namespace App\Http\Controllers\Sponsor;

use App\Http\Controllers\Controller;
use App\Models\Offer;
use App\Models\Proposal;
use App\Models\ProposalViewLog;
use App\Models\TrackingPipeline;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProposalController extends Controller
{
    protected function success(string $message): RedirectResponse
    {
        Inertia::flash('toast', ['type' => 'success', 'message' => $message]);

        return back();
    }

    protected function error(string $message): RedirectResponse
    {
        Inertia::flash('toast', ['type' => 'error', 'message' => $message]);

        return back();
    }
    public function index(Request $request): Response
    {
        $user = $this->sponsor($request);

        $items = TrackingPipeline::query()
            ->with(['proposal.user', 'proposal.kategori', 'offers'])
            ->where('company_user_id', $user->id)
            ->orderByDesc('updatedAt')
            ->get()
            ->map(function (TrackingPipeline $tracking) {
                $proposal = $tracking->proposal;

                return [
                    'trackingId' => $tracking->idTracking,
                    'proposalId' => $tracking->idProposal,
                    'title' => $proposal?->title,
                    'kategori' => $proposal?->kategori?->name,
                    'eoName' => $proposal?->user?->name,
                    'location' => $proposal?->location,
                    'budgetMin' => (int) ($proposal?->budgetMin ?? 0),
                    'budgetMax' => (int) ($proposal?->budgetMax ?? 0),
                    'stage' => $tracking->stage,
                    'stageLabel' => $this->stageLabel($tracking->stage),
                    'sentAt' => $tracking->createdAt?->toIso8601String(),
                    'offerCount' => $tracking->offers->count(),
                ];
            })
            ->values();

        return Inertia::render('sponsor/proposals/Index', [
            'items' => $items,
            'counts' => [
                'all' => $items->count(),
                'new' => $items->whereIn('stage', ['sent', 'viewed'])->count(),
                'negotiating' => $items->where('stage', 'negotiating')->count(),
                'deal' => $items->whereIn('stage', ['deal', 'waiting_report', 'done'])->count(),
                'rejected' => $items->where('stage', 'rejected')->count(),
            ],
        ]);
    }

    public function show(Request $request, TrackingPipeline $tracking): Response
    {
        $user = $this->sponsor($request);
        $this->authorizeTracking($tracking, $user->id);

        $tracking->load([
            'proposal.user',
            'proposal.kategori',
            'offers' => fn ($query) => $query->orderByDesc('createdAt'),
            'followUps',
        ]);

        $proposal = $tracking->proposal;

        // Catat kunjungan sponsor (sekali per sponsor per proposal).
        $alreadyViewed = ProposalViewLog::query()
            ->where('idProposal', $tracking->idProposal)
            ->where('idViewer', $user->id)
            ->exists();

        if (! $alreadyViewed) {
            ProposalViewLog::create([
                'idProposal' => $tracking->idProposal,
                'idViewer' => $user->id,
            ]);
        }

        if ($tracking->stage === 'sent') {
            $tracking->update(['stage' => 'viewed']);
            $tracking->refresh();
        }

        return Inertia::render('sponsor/proposals/Show', [
            'tracking' => [
                'id' => $tracking->idTracking,
                'stage' => $tracking->stage,
                'stageLabel' => $this->stageLabel($tracking->stage),
                'initiatedBy' => $tracking->initiatedBy,
                'sentAt' => $tracking->createdAt?->toIso8601String(),
            ],
            'proposal' => [
                'id' => $proposal->idProposal,
                'title' => $proposal->title,
                'description' => $proposal->desctiption,
                'targetAudience' => $proposal->target_audience,
                'location' => $proposal->location,
                'needType' => $proposal->needType,
                'budgetMin' => (int) $proposal->budgetMin,
                'budgetMax' => (int) $proposal->budgetMax,
                'benefits' => $proposal->benefits,
                'startDate' => $proposal->start_date?->toDateString(),
                'endDate' => $proposal->end_date?->toDateString(),
                'kategori' => $proposal->kategori?->name,
                'eoName' => $proposal->user?->name,
                'documentAvailable' => $this->documentExists($proposal),
            ],
            'offers' => $tracking->offers->map(fn (Offer $offer) => [
                'id' => $offer->idOffer,
                'fromSponsor' => (int) $offer->from_user_id === (int) $user->id,
                'supportType' => $offer->support_type,
                'amount' => $offer->amount === null ? null : (int) $offer->amount,
                'status' => $offer->status,
                'statusLabel' => $this->offerLabel($offer->status),
                'message' => $offer->description,
                'createdAt' => $offer->createdAt?->toIso8601String(),
            ])->values(),
        ]);
    }

    public function offer(Request $request, TrackingPipeline $tracking): RedirectResponse
    {
        $user = $this->sponsor($request);
        $this->authorizeTracking($tracking, $user->id);

        if (in_array($tracking->stage, ['deal', 'done', 'rejected'], true)) {
            return $this->error('Proposal ini sudah selesai diproses.');
        }

        $validated = $request->validate([
            'support_type' => ['required', 'string', 'in:Dana,Produk,Dana & produk,Jasa'],
            'amount' => ['required', 'integer', 'min:1'],
            'message' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($tracking, $user, $validated) {
            Offer::create([
                'idTracking' => $tracking->idTracking,
                'from_user_id' => $user->id,
                'support_type' => $validated['support_type'],
                'amount' => $validated['amount'],
                'description' => $validated['message'] ?? null,
                'status' => 'pending',
            ]);

            if (in_array($tracking->stage, ['sent', 'viewed'], true)) {
                $tracking->update(['stage' => 'negotiating']);
            }
        });

        return $this->success('Penawaran berhasil dikirim ke EO.');
    }

    public function respond(Request $request, TrackingPipeline $tracking): RedirectResponse
    {
        $user = $this->sponsor($request);
        $this->authorizeTracking($tracking, $user->id);

        if (in_array($tracking->stage, ['deal', 'done', 'rejected'], true)) {
            return $this->error('Proposal ini sudah selesai diproses.');
        }

        $validated = $request->validate([
            'action' => ['required', 'string', 'in:accept,reject'],
        ]);

        $accepted = $validated['action'] === 'accept';

        DB::transaction(function () use ($tracking, $accepted) {
            $tracking->update(['stage' => $accepted ? 'deal' : 'rejected']);

            // Semua penawaran yang masih menggantung ikut diselesaikan.
            $tracking->offers()
                ->where('status', 'pending')
                ->update(['status' => $accepted ? 'accepted' : 'rejected']);
        });

        return $this->success($accepted ? 'Proposal disetujui. Kolaborasi siap dijalankan.' : 'Proposal ditolak.');
    }

    public function download(Request $request, Proposal $proposal): StreamedResponse|RedirectResponse
    {
        $user = $this->sponsor($request);

        if (! $proposal->trackings()->where('company_user_id', $user->id)->exists()) {
            abort(403);
        }

        $path = (string) $proposal->file_path;

        if ($path === '') {
            return $this->error('Proposal ini belum memiliki dokumen.');
        }

        foreach (['local', 'public'] as $disk) {
            if (Storage::disk($disk)->exists($path)) {
                return Storage::disk($disk)->download($path);
            }
        }

        return $this->error('Dokumen proposal belum tersedia di server.');
    }

    /**
     * Pastikan akun yang mengakses benar-benar sponsor (perusahaan).
     */
    private function sponsor(Request $request): \App\Models\User
    {
        $user = $request->user();

        abort_unless($user && $user->isCompany(), 403, 'Halaman ini khusus akun sponsor.');

        return $user;
    }

    private function authorizeTracking(TrackingPipeline $tracking, int $userId): void
    {
        abort_unless((int) $tracking->company_user_id === $userId, 403);
    }

    private function documentExists(Proposal $proposal): bool
    {
        $path = (string) $proposal->file_path;

        if ($path === '') {
            return false;
        }

        return Storage::disk('local')->exists($path) || Storage::disk('public')->exists($path);
    }

    private function stageLabel(string $stage): string
    {
        return match ($stage) {
            'sent' => 'Baru dikirim',
            'viewed' => 'Sudah dibuka',
            'negotiating' => 'Negosiasi',
            'deal' => 'Disetujui',
            'waiting_report' => 'Menunggu laporan',
            'done' => 'Selesai',
            'rejected' => 'Ditolak',
            default => $stage,
        };
    }

    private function offerLabel(string $status): string
    {
        return match ($status) {
            'pending' => 'Menunggu respons',
            'accepted' => 'Diterima',
            'rejected' => 'Ditolak',
            'counter' => 'Penawaran balik',
            default => $status,
        };
    }
}
