<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Riwayat follow-up yang dikirim Event Organizer ke sponsor
 * (tabel `follow_up_logs`).
 *
 * @property int $idFollowUpLogs
 * @property int $idTracking
 * @property \Illuminate\Support\Carbon $sentAt
 */
#[Fillable(['idTracking'])]
class FollowUpLog extends Model
{
    protected $table = 'follow_up_logs';

    protected $primaryKey = 'idFollowUpLogs';

    public const CREATED_AT = 'sentAt';

    public const UPDATED_AT = null;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sentAt' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<TrackingPipeline, $this>
     */
    public function tracking(): BelongsTo
    {
        return $this->belongsTo(TrackingPipeline::class, 'idTracking', 'idTracking');
    }
}
