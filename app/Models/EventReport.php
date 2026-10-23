<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Laporan realisation event yang dikirim sponsor
 * (tabel `event_reports`).
 *
 * @property int $idreports
 * @property int $idTracking
 * @property string|null $decription
 * @property bool|null $goal_achieved
 * @property \Illuminate\Support\Carbon $submittedAt
 */
#[Fillable(['idTracking', 'decription', 'goal_achieved'])]
class EventReport extends Model
{
    protected $table = 'event_reports';

    protected $primaryKey = 'idreports';

    public const CREATED_AT = 'submittedAt';

    public const UPDATED_AT = null;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'goal_achieved' => 'boolean',
            'submittedAt' => 'datetime',
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
