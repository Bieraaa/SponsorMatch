<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model untuk tabel `otp_codes`.
 *
 * Isi tabel:
 *   idOTP, idUser, purpose, code_hash, attemp, expiredAt, createdAt
 *
 * CATATAN KEAMANAN:
 * Kolom `code_hash` berisi HASH dari kode OTP, bukan kode aslinya.
 * Jadi kalau database bocor,_codes ini tidak bisa langsung dipakai
 * untuk menyamar sebagai pengguna — attacker harus cracking hash-nya.
 * (Hash OTP pakai HMAC-SHA256, jadi tahan rainbow table.)
 *
 * Untuk membandingkan kode yang di-input user dengan yang tersimpan,
 * selalu lewat scope `whereCode()` atau method `verifyCode()` —
 * jangan pernah membandingkan dengan `===` langsung.
 *
 * @property int $idOTP
 * @property int $idUser
 * @property string $purpose
 * @property string $code_hash
 * @property int $attemp
 * @property \Illuminate\Support\Carbon $expiredAt
 * @property \Illuminate\Support\Carbon $createdAt
 * @property-read User $user
 */
#[Fillable(['idUser', 'purpose', 'code_hash', 'attemp', 'expiredAt'])]
class OtpCode extends Model
{
    /**
     * Primary key tabel `otp_codes` bukan "id", tapi "idOTP".
     */
    protected $primaryKey = 'idOTP';

    /**
     * `idOTP` bertipe AUTO_INCREMENT di database, jadi Eloquent perlu
     * mengisi nilainya kembali setelah INSERT (incrementing = true).
     */
    public $incrementing = true;

    /**
     * Tabel terkait.
     */
    protected $table = 'otp_codes';

    /**
     * Kolom timestamp memakai nama tidakstandar (createdAt, bukan created_at).
     */
    public const CREATED_AT = 'createdAt';

    public const UPDATED_AT = null;

    /**
     * Cast kolom.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'attemp' => 'integer',
            'expiredAt' => 'datetime',
            'createdAt' => 'datetime',
        ];
    }

    /**
     * Pemilik kode OTP ini.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'idUser', 'id');
    }

    /**
     * Kode OTP ini sudah kedaluwarsa?
     */
    public function isExpired(): bool
    {
        return $this->expiredAt->isPast();
    }

    /**
     * Sisa waktu berlaku dalam detik (0 kalau sudah habis).
     */
    public function secondsRemaining(): int
    {
        if ($this->isExpired()) {
            return 0;
        }

        return (int) now()->diffInSeconds($this->expiredAt, false);
    }
}
