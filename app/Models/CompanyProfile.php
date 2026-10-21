<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;

/**
 * Profil Perusahaan (tabel `company_profiles`, rancangan ERD).
 *
 * Relasi 1:1 dengan `users` — satu akun perusahaan punya tepat satu profil.
 *
 * ================== KEAMANAN DATA SENSITIF ==================
 * NPWP dan NIB adalah data legal perusahaan yang bersifat sensitif.
 * Keduanya diperlakukan berlapis:
 *
 *  1. `npwp` / `nib` → disimpan TERENKRIPSI (AES-256-CBC via APP_KEY).
 *     Cast `encrypted` Laravel melakukan ini otomatis: saat dibaca
 *     dikembalikan plaintext, saat disimpan ditulis ciphertext.
 *     Jadi kalau database bocor, isinya tidak terbaca tanpa APP_KEY.
 *
 *  2. `npwp_hash` / `nib_hash` → HMAC-SHA256 dengan APP_KEY sebagai kunci.
 *     Berguna untuk MENCARI data terenkripsi (ciphertext tidak bisa
 *     di-WHERE). Dipakai HMAC, bukan SHA-256 polos, supaya hash tidak
 *     bisa dihitung ulang tanpa APP_KEY — ini menutup serangan
 *     rainbow table / brute force karena NPWP/NIB punya pola terbatas.
 *
 * Mengapa hash duplikat perlu disimpan? Untuk mendeteksi NPWP/NIB ganda
 * (satu perusahaan tidak boleh mendaftar dua kali) tanpa perlu mendekripsi
 * seluruh tabel.
 */
#[Fillable(['idUser', 'companyName', 'nib', 'npwp', 'industry', 'campaign_Target'])]
class CompanyProfile extends Model
{
    protected $table = 'company_profiles';

    protected $primaryKey = 'idCompany';

    public $timestamps = false;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            // Nilai asli NPWP/NIB disimpan terenkripsi (AES-256 via APP_KEY).
            'nib' => 'encrypted',
            'npwp' => 'encrypted',
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
     * Jaga `npwp_hash` dan `nib_hash` selalu sinkron dengan nilai aslinya.
     *
     * Dipakai event `saving` (bukan accessor) supaya tidak bertabrakan
     * dengan cast `encrypted`: cast menangani enkripsi, event ini hanya
     * menghitung ulang kolom hash saat model disimpan.
     *
     * Dengan cara ini TIDAK ADA jalur kode yang bisa lupa mengisi hash —
     * termasuk seeder, factory, maupun update massal.
     */
    protected static function booted(): void
    {
        static::saving(function (self $profile) {
            $profile->npwp_hash = $profile->npwp === null
                ? null
                : self::hashSensitive($profile->npwp);

            $profile->nib_hash = $profile->nib === null
                ? null
                : self::hashSensitive($profile->nib);
        });
    }

    /**
     * Hitung HMAC-SHA256 dengan APP_KEY sebagai kunci rahasia.
     *
     * Normalisasi dulu (buang spasi & tanda hubung, jadikan huruf besar)
     * supaya "12.345.678.9-012.000" dan "123456789012000" menghasilkan
     * hash yang sama — mencegah duplikasi karena format penulisan berbeda.
     */
    public static function hashSensitive(string $value): string
    {
        $normalized = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $value) ?? $value);

        return hash_hmac('sha256', $normalized, (string) config('app.key'));
    }

    /**
     * Cari perusahaan berdasarkan NPWP/NIB tanpa mendekripsi seluruh tabel.
     * Hanya hash yang dibandingkan.
     */
    public function scopeWhereNpwp($query, string $npwp)
    {
        return $query->where('npwp_hash', self::hashSensitive($npwp));
    }

    public function scopeWhereNib($query, string $nib)
    {
        return $query->where('nib_hash', self::hashSensitive($nib));
    }
}
