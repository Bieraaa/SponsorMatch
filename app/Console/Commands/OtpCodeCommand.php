<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\OtpService;
use Illuminate\Console\Command;

/**
 * Perintah CLI untuk melihat kode OTP aktif secara langsung.
 *
 * PENGGUNAAN:
 *   php artisan otp:code emailkamu@contoh.id
 *
 * KENAPA ADA:
 *   Kode OTP di database disimpan sebagai HMAC-SHA256 (bukan teks polos)
 *   supaya kalau database bocor, attacker tidak bisa langsung memakainya.
 *   Konsekuensinya: kode polos TIDAK bisa dibaca ulang dari database.
 *
 *   Untuk development, perintah ini membuat kode baru (mengganti kode lama)
 *   lalu menampilkannya di terminal — tanpa perlu SMTP, tanpa perlu inbox.
 *   Gunanya: seluruh alur OTP bisa diuji 100% offline.
 *
 * CATATAN KEAMANAN:
 *   Perintah ini hanya jalan di CLI (tidak ada route HTTP), jadi tidak bisa
 *   dipanggil attacker dari luar. Tetap saja, jangan pernah jalankan di
 *   server production — siapa pun yang punya akses shell bisa melihat kode.
 */
class OtpCodeCommand extends Command
{
    /**
     * Signature perintah.
     *
     * {email}   Email user yang mau dilihat kodenya
     * {--purpose=register}  Purpose OTP: register atau login
     */
    protected $signature = 'otp:code
                            {email : Email user yang akan dibuatkan kode OTP}
                            {--purpose=register : Purpose kode (register atau login)}';

    /**
     * Deskripsi perintah (ditampilkan di `php artisan list`).
     */
    protected $description = 'Buat kode OTP baru untuk satu user lalu tampilkan kodenya di terminal (development)';

    public function handle(OtpService $otpService): int
    {
        $email = (string) $this->argument('email');
        $purpose = (string) $this->option('purpose');

        if (! in_array($purpose, [OtpService::PURPOSE_REGISTER, OtpService::PURPOSE_LOGIN], true)) {
            $this->error("Purpose tidak dikenal: {$purpose}. Pilihan: register atau login.");

            return self::FAILURE;
        }

        $user = User::where('email', $email)->first();

        if (! $user) {
            $this->error("User dengan email \"{$email}\" tidak ditemukan.");

            return self::FAILURE;
        }

        // issueForConsole() TIDAK mengirim email — cukup buat + simpan kode.
        [$otp, $code] = $otpService->issueForConsole($user, $purpose);

        $this->newLine();
        $this->line('  <fg=green;options=bold>KODE OTP SIAP</>');
        $this->line('  ---------------------------------------------');
        $this->line("  Email    : {$user->email}");
        $this->line("  Kode     : <fg=yellow;options=bold>{$code}</>");
        $this->line('  Berlaku  : sampai '.$otp->expiredAt->format('H:i:s').' (5 menit)');
        $this->line('  Sisa     : '.$otp->secondsRemaining().' detik');
        $this->line('  ---------------------------------------------');
        $this->newLine();
        $this->line('  <fg=gray>Kode ini menggantikan kode lama dan tidak dikirim ke email.</>');
        $this->newLine();

        return self::SUCCESS;
    }
}
