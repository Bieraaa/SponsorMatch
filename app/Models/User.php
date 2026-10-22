<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Fortify\Contracts\PasskeyUser;
use Laravel\Fortify\PasskeyAuthenticatable;
use Laravel\Fortify\TwoFactorAuthenticatable;

/**
 * Akun pengguna — SATU-SATUNYA tabel user aplikasi (`users`).
 *
 * Sejak konsolidasi Blok A, tabel ERD `user` telah dihapus dan seluruh
 * relasinya (`proposal`, `campaign`, `offer`, dst.) menunjuk ke tabel ini.
 * Tidak ada lagi dua tabel user yang harus disinkronkan.
 *
 * Data yang BERBEDA per peran tidak ditumpuk di sini, melainkan disimpan
 * di tabel profil terpisah sesuai rancangan ERD:
 *   - Event Organizer → `eo_profiles`  (relasi: eoProfile)
 *   - Perusahaan      → `company_profiles` (relasi: companyProfile)
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property UserRole $role
 * @property string|null $nomor_telepon
 * @property string|null $jabatan
 * @property string $status_subscription
 * @property string|null $rating
 * @property bool $isActive
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property Carbon|null $two_factor_confirmed_at
 * @property string|null $remember_token
 * @property Carbon|null $last_login_at
 * @property Carbon|null $anonymized_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read EoProfile|null $eoProfile
 * @property-read CompanyProfile|null $companyProfile
 */
#[Fillable(['name', 'email', 'role', 'password', 'nomor_telepon', 'jabatan'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail, PasskeyUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, PasskeyAuthenticatable, TwoFactorAuthenticatable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            // password otomatis di-hash saat diisi — mencegah lupa hashing
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
            // Kolom dari rancangan ERD
            'role' => UserRole::class,
            'rating' => 'decimal:1',
            'isActive' => 'boolean',
            'last_login_at' => 'datetime',
            'anonymized_at' => 'datetime',
        ];
    }

    /**
     * Profil Event Organizer (hanya ada bila role = Event Organizer).
     *
     * @return HasOne<EoProfile, $this>
     */
    public function eoProfile(): HasOne
    {
        return $this->hasOne(EoProfile::class, 'idUser', 'id');
    }

    /**
     * Profil Perusahaan (hanya ada bila role = Perusahaan).
     *
     * @return HasOne<CompanyProfile, $this>
     */
    public function companyProfile(): HasOne
    {
        return $this->hasOne(CompanyProfile::class, 'idUser', 'id');
    }

    /**
     * Kirim notifikasi email verifikasi.
     *
     * Override method bawaan Laravel supaya kita memakai notifikasi
     * sendiri (bahasa Indonesia + template branded), bukan class bawaan
     * Illuminate\Auth\Notifications\VerifyEmail.
     *
     * Dipanggil otomatis oleh listener SendEmailVerificationNotification
     * saat event `Registered` ditembakkan (yaitu setelah register berhasil).
     */
    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new \App\Notifications\VerifyEmailNotification);
    }

    /**
     * Kirim notifikasi reset password.
     *
     * Override method bawaan Laravel (Illuminate\Auth\Passwords\CanResetPassword)
     * supaya email reset password juga memakai template branded kita.
     */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new \App\Notifications\ResetPasswordNotification($token));
    }

    /**
     * Apakah akun ini Event Organizer?
     */
    public function isEventOrganizer(): bool
    {
        return $this->role === UserRole::EventOrganizer;
    }

    /**
     * Apakah akun ini Perusahaan?
     */
    public function isCompany(): bool
    {
        return $this->role === UserRole::Perusahaan;
    }
}
