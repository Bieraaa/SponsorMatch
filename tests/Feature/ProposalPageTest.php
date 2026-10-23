<?php

namespace Tests\Feature;

use App\Models\FollowUpLog;
use App\Models\Kategori;
use App\Models\Offer;
use App\Models\Proposal;
use App\Models\TrackingPipeline;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Halaman "Proposal Saya" (Event Organizer).
 *
 * Yang diuji:
 *  - hanya proposal milik user yang tampil (bukan milik EO lain)
 *  - status diturunkan dari tracking/offer dan konsisten dengan tab filter
 *  - panel detail hanya mengembalikan proposal milik user
 *  - unduh dokumen dibatasi pemilik proposal / sponsor penerima
 *  - follow-up benar-benar menulis baris baru ke `follow_up_logs`
 *  - form tambah proposal menyimpan draft ke tabel `proposal`
 */
class ProposalPageTest extends TestCase
{
    use RefreshDatabase;

    /** Kategori event untuk fixture. */
    private Kategori $kategori;

    protected function setUp(): void
    {
        parent::setUp();

        $this->kategori = Kategori::create([
            'name' => 'Musik & Konser',
            'deskripsiKategori' => 'Konser dan festival musik.',
        ]);
    }

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $this->get(route('proposals.index'))->assertRedirect(route('login'));
    }

    public function test_only_the_owners_proposals_are_listed(): void
    {
        $owner = User::factory()->create();
        $otherEo = User::factory()->create();

        $this->proposal($owner, ['title' => 'Festival Musik Nusantara']);
        $this->proposal($otherEo, ['title' => 'Proposal EO Lain']);

        $props = $this->actingAs($owner)->get(route('proposals.index'))->assertOk()
            ->viewData('page')['props'];

        $this->assertCount(1, $props['proposals']['data']);
        $this->assertSame('Festival Musik Nusantara', $props['proposals']['data'][0]['title']);
        $this->assertSame(1, $props['proposals']['total']);
    }

    public function test_derived_status_matches_the_status_tab_filter(): void
    {
        $owner = User::factory()->create();
        $sponsor = User::factory()->company()->create();

        // Dikirim tapi belum dibuka → status "sent".
        $sent = $this->proposal($owner, ['title' => 'Proposal Terkirim']);
        $this->tracking($sent, $sponsor, 'sent');

        // Sudah deal → status "approved".
        $deal = $this->proposal($owner, ['title' => 'Proposal Deal']);
        $this->tracking($deal, $sponsor, 'deal');

        // Ditolak sponsor → status "rejected".
        $rejected = $this->proposal($owner, ['title' => 'Proposal Ditolak']);
        $this->tracking($rejected, $sponsor, 'rejected');

        // Masih draft (belum dikirim) → status "draft".
        $draft = $this->proposal($owner, ['title' => 'Proposal Draft', 'status' => 'draft']);

        $props = $this->actingAs($owner)->get(route('proposals.index'))->viewData('page')['props'];

        $statuses = collect($props['proposals']['data'])
            ->pluck('status', 'title')
            ->all();

        $this->assertSame('sent', $statuses['Proposal Terkirim']);
        $this->assertSame('approved', $statuses['Proposal Deal']);
        $this->assertSame('rejected', $statuses['Proposal Ditolak']);
        $this->assertSame('draft', $statuses['Proposal Draft']);

        $this->assertSame(1, $props['statusCounts']['draft']);
        $this->assertSame(1, $props['statusCounts']['sent']);
        $this->assertSame(1, $props['statusCounts']['approved']);
        $this->assertSame(1, $props['statusCounts']['rejected']);
        $this->assertSame(4, $props['statusCounts']['all']);

        // Tab "draft" hanya berisi proposal yang belum dikirim.
        $filtered = $this->actingAs($owner)
            ->get(route('proposals.index', ['status' => 'draft']))
            ->viewData('page')['props'];

        $this->assertCount(1, $filtered['proposals']['data']);
        $this->assertSame($draft->idProposal, $filtered['proposals']['data'][0]['id']);
    }

    public function test_selected_proposal_is_returned_only_for_its_owner(): void
    {
        $owner = User::factory()->create();
        $otherEo = User::factory()->create();
        $sponsor = User::factory()->company()->create();

        $mine = $this->proposal($owner, ['title' => 'Proposal Saya']);
        $this->tracking($mine, $sponsor, 'negotiating');

        $foreign = $this->proposal($otherEo, ['title' => 'Proposal Orang Lain']);

        $selected = $this->actingAs($owner)
            ->get(route('proposals.index', ['selected' => $mine->idProposal]))
            ->viewData('page')['props'];

        $this->assertNotNull($selected['selectedProposal']);
        $this->assertSame($mine->idProposal, $selected['selectedProposal']['id']);
        $this->assertSame('waiting', $selected['selectedProposal']['status']);

        // Proposal milik EO lain tidak boleh muncul di panel detail.
        $foreignSelected = $this->actingAs($owner)
            ->get(route('proposals.index', ['selected' => $foreign->idProposal]))
            ->viewData('page')['props'];

        $this->assertNull($foreignSelected['selectedProposal']);
    }

    public function test_follow_up_endpoint_records_a_new_log(): void
    {
        $owner = User::factory()->create();
        $sponsor = User::factory()->company()->create();

        $proposal = $this->proposal($owner);
        $tracking = $this->tracking($proposal, $sponsor, 'sent');

        $this->assertSame(0, FollowUpLog::count());

        $this->actingAs($owner)
            ->post(route('proposals.tindak-lanjut', $tracking))
            ->assertRedirect();

        $this->assertSame(1, FollowUpLog::count());
        $this->assertSame($tracking->idTracking, FollowUpLog::first()->idTracking);
    }

    public function test_follow_up_is_forbidden_for_another_eo(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $sponsor = User::factory()->company()->create();

        $tracking = $this->tracking($this->proposal($owner), $sponsor, 'sent');

        $this->actingAs($intruder)
            ->post(route('proposals.tindak-lanjut', $tracking))
            ->assertForbidden();

        $this->assertSame(0, FollowUpLog::count());
    }

    public function test_owner_can_download_the_proposal_document(): void
    {
        Storage::fake('local');

        $owner = User::factory()->create();
        $proposal = $this->proposal($owner, ['file_path' => 'proposals/demo.pdf']);

        Storage::disk('local')->put('proposals/demo.pdf', '%PDF-1.4 dokumen demo');

        $response = $this->actingAs($owner)->get(route('proposals.dokumen', $proposal));

        $response->assertOk();
        $response->assertDownload('demo.pdf');
    }

    public function test_strangers_cannot_download_the_proposal_document(): void
    {
        Storage::fake('local');

        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $proposal = $this->proposal($owner, ['file_path' => 'proposals/demo.pdf']);

        Storage::disk('local')->put('proposals/demo.pdf', '%PDF-1.4 dokumen demo');

        $this->actingAs($stranger)
            ->get(route('proposals.dokumen', $proposal))
            ->assertForbidden();
    }

    public function test_targeted_sponsor_can_download_the_proposal_document(): void
    {
        Storage::fake('local');

        $owner = User::factory()->create();
        $sponsor = User::factory()->company()->create();
        $proposal = $this->proposal($owner, ['file_path' => 'proposals/demo.pdf']);

        $this->tracking($proposal, $sponsor, 'sent');
        Storage::disk('local')->put('proposals/demo.pdf', '%PDF-1.4 dokumen demo');

        $this->actingAs($sponsor)
            ->get(route('proposals.dokumen', $proposal))
            ->assertOk();
    }

    public function test_store_creates_a_draft_proposal_for_the_logged_in_user(): void
    {
        $owner = User::factory()->create();

        $response = $this->actingAs($owner)->post(route('proposals.store'), [
            'title' => 'Sponsorship Pameran Seni Digital',
            'idKategori' => $this->kategori->idKategori,
            'location' => 'Jakarta Convention Center',
            'needType' => 'Dana',
            'desctiption' => 'Pameran seni digital tahunan.',
            'target_audience' => 'Mahasiswa dan profesional muda.',
            'budgetMin' => 10000000,
            'budgetMax' => 50000000,
            'start_date' => '2026-11-10',
            'end_date' => '2026-11-12',
            'benefits' => 'Logo pada panggung utama, booth eksklusif',
        ]);

        $response->assertRedirect();

        $proposal = Proposal::firstWhere('title', 'Sponsorship Pameran Seni Digital');

        $this->assertNotNull($proposal);
        $this->assertSame($owner->id, $proposal->idUser);
        $this->assertSame('draft', $proposal->status);
        $this->assertSame('', $proposal->file_path);
    }

    public function test_store_rejects_invalid_payload(): void
    {
        $owner = User::factory()->create();

        $this->actingAs($owner)
            ->post(route('proposals.store'), [
                'title' => '',
                'idKategori' => 999,
                'budgetMin' => 90000,
                'budgetMax' => 1000,
            ])
            ->assertSessionHasErrors([
                'title',
                'idKategori',
                'location',
                'needType',
                'desctiption',
                'target_audience',
                'budgetMax',
                'start_date',
                'end_date',
            ]);

        $this->assertSame(0, Proposal::count());
    }

    public function test_store_saves_the_uploaded_pdf_document(): void
    {
        Storage::fake('local');

        $owner = User::factory()->create();

        $this->actingAs($owner)->post(route('proposals.store'), [
            'title' => 'Sponsorship dengan Dokumen PDF',
            'idKategori' => $this->kategori->idKategori,
            'location' => 'Bandung',
            'needType' => 'Dana',
            'desctiption' => 'Proposal dengan dokumen PDF.',
            'target_audience' => 'Mahasiswa.',
            'budgetMin' => 5000000,
            'budgetMax' => 15000000,
            'start_date' => '2026-12-01',
            'end_date' => '2026-12-02',
            'file' => UploadedFile::fake()->create('proposal.pdf', 120, 'application/pdf'),
        ])->assertRedirect();

        $proposal = Proposal::firstWhere('title', 'Sponsorship dengan Dokumen PDF');

        $this->assertNotNull($proposal);
        $this->assertStringStartsWith('proposals/', $proposal->file_path);
        Storage::disk('local')->assertExists($proposal->file_path);
    }

    public function test_store_accepts_budget_written_with_thousand_separators(): void
    {
        $owner = User::factory()->create();

        $this->actingAs($owner)->post(route('proposals.store'), [
            'title' => 'Anggaran Berformat',
            'idKategori' => $this->kategori->idKategori,
            'location' => 'Surabaya',
            'needType' => 'Dana',
            'desctiption' => 'Proposal dengan anggaran berformat.',
            'target_audience' => 'Umum.',
            'budgetMin' => '100.000.000',
            'budgetMax' => '250.000.000',
            'start_date' => '2026-12-10',
            'end_date' => '2026-12-11',
        ])->assertRedirect();

        $proposal = Proposal::firstWhere('title', 'Anggaran Berformat');

        $this->assertNotNull($proposal);
        $this->assertSame(100000000, (int) $proposal->budgetMin);
        $this->assertSame(250000000, (int) $proposal->budgetMax);
    }

    public function test_extract_reads_proposal_fields_from_a_text_pdf(): void
    {
        $owner = User::factory()->create();

        $pdf = $this->pdf([
            'DOKUMEN PROPOSAL SPONSORSHIP',
            'Judul    : Festival Musik Nusantara 2026',
            'Kategori : Musik & Konser',
            'Lokasi   : Gelora Bung Karno, Jakarta',
            'Anggaran : Rp50.000.000 - Rp150.000.000',
            'Periode  : 15 Nov 2026 - 16 Nov 2026',
        ]);

        $this->actingAs($owner)->post(route('proposals.ekstrak'), [
            'file' => UploadedFile::fake()->createWithContent('proposal.pdf', $pdf),
        ])->assertOk()->assertJson([
            'fields' => [
                'title' => 'Festival Musik Nusantara 2026',
                'idKategori' => $this->kategori->idKategori,
                'location' => 'Gelora Bung Karno, Jakarta',
                'budgetMin' => 50000000,
                'budgetMax' => 150000000,
                'start_date' => '2026-11-15',
                'end_date' => '2026-11-16',
            ],
        ]);
    }

    public function test_extract_rejects_a_file_that_is_not_a_pdf(): void
    {
        $owner = User::factory()->create();

        $this->actingAs($owner)->post(
            route('proposals.ekstrak'),
            ['file' => UploadedFile::fake()->createWithContent('proposal.txt', 'bukan pdf')],
            ['Accept' => 'application/json'],
        )->assertStatus(422)->assertJsonValidationErrors('file');
    }

    public function test_proposal_numbers_are_per_user_and_start_at_one(): void
    {
        $owner = User::factory()->create();
        $otherEo = User::factory()->create();

        $this->proposal($owner, ['title' => 'Proposal Pertama']);
        $this->proposal($owner, ['title' => 'Proposal Kedua']);
        $this->proposal($otherEo, ['title' => 'Proposal EO Lain']);

        $numbers = collect(
            $this->actingAs($owner)->get(route('proposals.index'))->assertOk()
                ->viewData('page')['props']['proposals']['data'],
        )->pluck('nomor', 'title');

        // Nomor bukan id database: user ini mulai dari 1, 2, ...
        $this->assertSame('1', $numbers['Proposal Pertama']);
        $this->assertSame('2', $numbers['Proposal Kedua']);

        // EO lain punya urutan sendiri → proposalnya juga nomor 1.
        $otherNumbers = collect(
            $this->actingAs($otherEo)->get(route('proposals.index'))->assertOk()
                ->viewData('page')['props']['proposals']['data'],
        )->pluck('nomor', 'title');

        $this->assertSame('1', $otherNumbers['Proposal EO Lain']);

        // Nomor tidak berubah saat tabel difilter (tidak menomori ulang 1..n
        // dari hasil filter).
        $filtered = collect(
            $this->actingAs($owner)->get(route('proposals.index', ['search' => 'Kedua']))->assertOk()
                ->viewData('page')['props']['proposals']['data'],
        )->pluck('nomor', 'title');

        $this->assertSame('2', $filtered['Proposal Kedua']);

        // Panel detail memakai nomor yang sama.
        $detail = $this->actingAs($owner)
            ->get(route('proposals.index', ['selected' => Proposal::firstWhere('title', 'Proposal Kedua')->idProposal]))
            ->assertOk()
            ->viewData('page')['props']['selectedProposal'];

        $this->assertSame('2', $detail['nomor']);
    }

    /**
     * Buat PDF satu halaman sederhana untuk menguji ekstraksi teks.
     *
     * @param  array<int, string>  $lines
     */
    private function pdf(array $lines): string
    {
        $content = "BT\n";
        $y = 780;

        foreach ($lines as $index => $line) {
            $size = $index === 0 ? 20 : 12;
            $escaped = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $line);
            $content .= "/F1 {$size} Tf\n1 0 0 1 60 {$y} Tm\n({$escaped}) Tj\n";
            $y -= $index === 0 ? 40 : 22;
        }

        $content .= "ET\n";

        $objects = [
            '<< /Type /Catalog /Pages 2 0 R >>',
            '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>',
            '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
            "<< /Length ".strlen($content)." >>\nstream\n".$content."endstream",
        ];

        $pdf = "%PDF-1.4\n";
        $offsets = [];

        foreach ($objects as $index => $object) {
            $offsets[$index + 1] = strlen($pdf);
            $pdf .= ($index + 1)." 0 obj\n".$object."\nendobj\n";
        }

        $xrefOffset = strlen($pdf);
        $count = count($objects) + 1;

        $pdf .= "xref\n0 {$count}\n0000000000 65535 f \n";

        for ($i = 1; $i < $count; $i++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
        }

        return $pdf."trailer\n<< /Size {$count} /Root 1 0 R >>\nstartxref\n{$xrefOffset}\n%%EOF\n";
    }

    /**
     * Buat satu proposal milik user.
     *
     * @param  array<string, mixed>  $attributes
     */
    private function proposal(User $owner, array $attributes = []): Proposal
    {
        return Proposal::create([
            'idUser' => $owner->id,
            'idKategori' => $this->kategori->idKategori,
            'title' => 'Proposal Uji',
            'desctiption' => 'Deskripsi proposal uji.',
            'target_audience' => 'Mahasiswa.',
            'location' => 'Bandung',
            'needType' => 'Dana',
            'budgetMin' => 10000000,
            'budgetMax' => 50000000,
            'benefits' => 'Logo utama, booth',
            'file_path' => '',
            'status' => 'published',
            'start_date' => '2026-11-01',
            'end_date' => '2026-11-03',
            ...$attributes,
        ]);
    }

    /**
     * Kirim proposal ke satu sponsor.
     */
    private function tracking(Proposal $proposal, User $sponsor, string $stage): TrackingPipeline
    {
        return TrackingPipeline::create([
            'idProposal' => $proposal->idProposal,
            'company_user_id' => $sponsor->id,
            'stage' => $stage,
            'initiatedBy' => 'Event_Organizer',
        ]);
    }

    /**
     * Penawaran sponsor (dipakai bila perlu menguji status dari offer).
     */
    private function offer(TrackingPipeline $tracking, User $sponsor, string $status): Offer
    {
        return Offer::create([
            'idTracking' => $tracking->idTracking,
            'from_user_id' => $sponsor->id,
            'support_type' => 'Dana',
            'amount' => 25000000,
            'status' => $status,
        ]);
    }
}
