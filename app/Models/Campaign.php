<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Event / campaign milik Event Organizer (tabel `campaign`).
 *
 * Event boleh dipasangkan dengan proposal melalui
 * `trackingpipeline.idCampaign`.
 *
 * @property int $idCampaign
 * @property int $idUser
 * @property int $idKategori
 * @property string $title
 * @property string $description
 * @property string $target_audience
 * @property int $budgetMin
 * @property int $budgetMax
 * @property string $supportType
 * @property int $estimatedParticipant
 * @property \Illuminate\Support\Carbon $periodStart
 * @property \Illuminate\Support\Carbon $periodEnd
 * @property string $file_path
 * @property string $status
 */
#[Fillable([
    'idUser',
    'idKategori',
    'title',
    'description',
    'target_audience',
    'budgetMin',
    'budgetMax',
    'supportType',
    'estimatedParticipant',
    'periodStart',
    'periodEnd',
    'file_path',
    'status',
])]
class Campaign extends Model
{
    protected $table = 'campaign';

    protected $primaryKey = 'idCampaign';

    public const CREATED_AT = 'createdAt';

    public const UPDATED_AT = 'updateAt';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'budgetMin' => 'integer',
            'budgetMax' => 'integer',
            'estimatedParticipant' => 'integer',
            'periodStart' => 'date',
            'periodEnd' => 'date',
            'createdAt' => 'datetime',
            'updateAt' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'idUser', 'id');
    }

    /**
     * @return BelongsTo<Kategori, $this>
     */
    public function kategori(): BelongsTo
    {
        return $this->belongsTo(Kategori::class, 'idKategori', 'idKategori');
    }

    /**
     * @return HasMany<TrackingPipeline, $this>
     */
    public function trackings(): HasMany
    {
        return $this->hasMany(TrackingPipeline::class, 'idCampaign', 'idCampaign');
    }
}
