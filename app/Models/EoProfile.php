<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Profil Event Organizer (tabel `eo_profiles`, rancangan ERD).
 *
 * Relasi 1:1 dengan `users` — satu akun EO punya tepat satu profil.
 * Inilah alasan tabel ini terpisah: data EO (nama organisasi, bidang)
 * tidak dimiliki oleh perusahaan, jadi tidak layak menumpuk semua kolom
 * di tabel `users`.
 *
 * @property int $idEventOrganizer
 * @property int $idUser
 * @property string $eo_name
 * @property string|null $eo_field
 * @property string|null $eo_email
 */
#[Fillable(['idUser', 'eo_name', 'eo_field', 'eo_email'])]
class EoProfile extends Model
{
    protected $table = 'eo_profiles';

    protected $primaryKey = 'idEventOrganizer';

    /**
     * Tabel ERD tidak memakai kolom created_at/updated_at bawaan Laravel.
     */
    public $timestamps = false;

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'idUser', 'id');
    }
}
