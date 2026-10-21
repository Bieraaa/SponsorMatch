<?php

namespace App\Listeners;

use App\Models\User;
use Illuminate\Auth\Events\Failed;
use Illuminate\Support\Facades\DB;

/**
 * Mencatat percobaan login yang GAGAL (Blok A — keamanan).
 *
 * Setiap kegagalan login (password salah, email tidak ada) ditulis ke
 * tabel `loginattemp` (succes = 0). Ini adalah data penting untuk
 * mendeteksi serangan brute force: banyak baris gagal dari satu IP
 * dalam waktu singkat = indikasi percobaan pembobolan.
 *
 * Catatan: Fortify sudah membatasi login 5x/menit per email+IP
 * (lihat FortifyServiceProvider), jadi pencatatan ini melengkapinya
 * dengan bukti historis yang bisa ditinjau setelah kejadian.
 *
 * Kegagalan pencatatan ditelan agar tidak mengubah respons login.
 */
class RecordFailedLogin
{
    public function handle(Failed $event): void
    {
        try {
            /** @var User|null $user */
            $user = $event->user;

            DB::table('loginattemp')->insert([
                'idUser' => $user?->id,
                'email' => (string) ($event->credentials['email'] ?? 'tidak diketahui'),
                'ip_adrress' => request()->ip() ?? '0.0.0.0',
                'succes' => 0,
                'attemptedAt' => now(),
            ]);
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
