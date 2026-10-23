<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Log sponsor yang membuka proposal (tabel `proposalviewlogs`).
 *
 * @property int $idProposLogs
 * @property int $idProposal
 * @property int $idViewer
 * @property \Illuminate\Support\Carbon $viewedAt
 */
#[Fillable(['idProposal', 'idViewer'])]
class ProposalViewLog extends Model
{
    protected $table = 'proposalviewlogs';

    protected $primaryKey = 'idProposLogs';

    public const CREATED_AT = 'viewedAt';

    public const UPDATED_AT = null;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'viewedAt' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Proposal, $this>
     */
    public function proposal(): BelongsTo
    {
        return $this->belongsTo(Proposal::class, 'idProposal', 'idProposal');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function viewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'idViewer', 'id');
    }
}
