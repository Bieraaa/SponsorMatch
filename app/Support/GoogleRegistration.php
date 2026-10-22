<?php

namespace App\Support;

/**
 * Penyimpanan sementara profil Google untuk pendaftaran yang BELUM SELESAI.
 *
 * ALUR PENDAFTARAN DENGAN GOOGLE:
 *
 *   1. User klik "Lanjutkan dengan Google" → /auth/google/redirect
 *   2. Callback (/auth/google/callback):
 *        - Email SUDAH terdaftar  → langsung login, redir ke dashboard.
 *        - Email BELUM terdaftar  → profil Google disimpan DI SESSION,
 *          lalu user diarahkan ke /register (pilih peran) untuk
 *          melengkapi data (jabatan, telepon, nama perusahaan, dst).
 *
 * KENAPA TIDAK LANGSUNG BUAT AKAN DI CALLBACK?
 *
 *   Karena tabel `users` Wajib punya peran (`role` ENUM('eo','sponsor') NOT
 *   NULL), sementara peran hanya bisa dipilih user. Kalau kita membuat akun
 *   dulu di callback, kita harus menebak perannya — dan kalau tebakan salah,
 *   user harus mengoreksi database.
 *
 *   Dengan menyimpan profil di session dulu, pembuatan akun tetap terjadi di
 *   `CreateNewUser` (SATU-SATUNYA tempat pembuatan akun), sehingga aturan
 *   validasi, pembuatan tabel profil peran, dan transaksi DB tetap sama
 *   untuk pengguna biasa maupun pengguna Google.
 *
 * CATATAN KEAMANAN:
 *   - Isi session Signed (Laravel menandatangani cookie session), jadi
 *     klien tidak bisa mengarang email sendiri.
 *   - Email TIDAK pernah diambil dari input form saat mode Google; nilainya
 *     selalu dari session (yang berasal dari Google).
 */
class GoogleRegistration
{
    /** Kunci session tempat profil Google disimpan. */
    public const SESSION_KEY = 'google.registration';

    /**
     * Ambil profil Google yang menunggu penyelesaian pendaftaran.
     *
     * @return array{email: string, name: string, avatar?: string|null}|null
     */
    public static function pending(): ?array
    {
        $profile = session(self::SESSION_KEY);

        if (! is_array($profile) || blank($profile['email'] ?? null)) {
            return null;
        }

        return [
            'email' => (string) $profile['email'],
            'name' => (string) ($profile['name'] ?? ''),
            'avatar' => $profile['avatar'] ?? null,
        ];
    }

    /**
     * Simpan profil Google ke session.
     *
     * @param  array{email: string, name?: string, avatar?: string|null}  $profile
     */
    public static function put(array $profile): void
    {
        session()->put(self::SESSION_KEY, [
            'email' => $profile['email'],
            'name' => $profile['name'] ?? '',
            'avatar' => $profile['avatar'] ?? null,
        ]);
    }

    /** Hapus profil Google dari session (dipanggil setelah akun berhasil dibuat). */
    public static function forget(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    /**
     * Bentuk data yang aman dikirim ke frontend (tanpa avatar, cukup email+nama).
     *
     * @param  array{email: string, name: string, avatar?: string|null}|null  $profile
     * @return array{email: string, name: string}|null
     */
    public static function toArray(?array $profile): ?array
    {
        if ($profile === null) {
            return null;
        }

        return [
            'email' => $profile['email'],
            'name' => $profile['name'],
        ];
    }
}
