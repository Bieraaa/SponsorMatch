<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Kategori event (tabel `kategori`).
 *
 * Dipakai event/proposal untuk mengelompokkan jenis acara
 * (Musik & Konser, Teknologi, Olahraga, dst).
 *
 * @property int $idKategori
 * @property string $name
 * @property string $deskripsiKategori
 */
#[Fillable(['name', 'deskripsiKategori'])]
class Kategori extends Model
{
    protected $table = 'kategori';

    protected $primaryKey = 'idKategori';

    public $timestamps = false;

    /**
     * @return HasMany<Proposal, $this>
     */
    public function proposals(): HasMany
    {
        return $this->hasMany(Proposal::class, 'idKategori', 'idKategori');
    }
}
