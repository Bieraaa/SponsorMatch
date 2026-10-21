<?php

namespace App\Listeners;

use App\Models\User;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\DB;

/**
 * Mencatat aktivitas login yang BERHASIL (Blok A — keamanan).
 *
 * Dua hal yang dilakukan:
 *  1. Mengisi `users.last_login_at` — kolom ini sudah ada di rancangan ERD
 *     tetapi sebelumnya tidak pernah diisi. Berguna untuk mendeteksi
 *     login mencurigakan ("kapan terakhir akun ini dipakai?").
 *  2. Menulis baris ke `loginattemp` (succes = 1) sebagai jejak audit
 *     yang tidak bisa diubah dari sisi user.
 *
 * Kegagalan pencatatan TIDAK boleh menggagalkan login, karena itu
 * dibungkus try/catch — login tetap sukses meski pencatatan audit error.
 */
class RecordSuccessfulLogin
{
    public function handle(Login $event): void
    {
        /** @var User $user */
        $user = $event->user;

        try {
            $user->forceFill(['last_login_at' => now()])->save();

            DB::table('loginattemp')->insert([
                'idUser' => $user->id,
                'email' => $user->email,
                'ip_adrress' => request()->ip() ?? '0.0.0.0',
                'succes' => 1,
                'attemptedAt' => now(),
            ]);
        } catch (\Throwable $e) {
            // Sengaja ditelan: audit tidak boleh menghalangi login.
            report($e);
        }
    }
}
