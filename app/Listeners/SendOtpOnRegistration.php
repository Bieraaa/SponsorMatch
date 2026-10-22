<?php

namespace App\Listeners;

use App\Services\OtpService;
use Illuminate\Auth\Events\Registered;

/**
 * Listener pendaftaran: kirim KODE OTP ke email user yang baru mendaftar.
 *
 * KENAPA LISTENER INI ADA (dan listener bawaan Laravel dimatikan):
 *
 * Laravel secara bawaan memasang `SendEmailVerificationNotification` ke
 * event `Registered`. Efeknya: begitu user daftar, dia menerima email
 * "Verifikasi alamat email" berisi TAUTAN. Aplikasi SponsorMatch punya
 * alur verifikasi sendiri yang LEBIH AMAN dan sesuai desain Figma:
 * kode OTP 6 digit di halaman /verify-otp.
 *
 * Kalau keduanya dibiarkan, user menerima DUA email dan sering salah pilih
 * — dia klik tautan verifikasi, akun terverifikasi tanpa pernah memakai
 * kode OTP, dan halaman OTP jadi tidak pernah dipakai.
 *
 * Jadi: listener bawaan Laravel dilepas (lihat AppServiceProvider), dan
 * digantikan listener ini — satu-satunya email yang dikirim adalah email
 * berisi kode OTP.
 *
 * CATATAN PENTING: kode dikirim saat PENDAFTARAN, bukan saat halaman OTP
 * dibuka. Jadi begitu user selesai daftar, email OTP sudah dalam perjalanan
 * — tidak perlu menunggu halaman OTP selesai dimuat dulu.
 */
class SendOtpOnRegistration
{
    public function __construct(protected OtpService $otp)
    {
    }

    public function handle(Registered $event): void
    {
        $user = $event->user;

        // Pendaftaran lewat GOOGLE: email-nya sudah diverifikasi oleh
        // Google (scope `email` hanya mengembalikan email terverifikasi).
        // Jadi tidak perlu kirim OTP — user langsung masuk dashboard.
        if ($user->hasVerifiedEmail()) {
            return;
        }

        // force: true → ini kode PERTAMA, jadi cooldown kirim-ulang
        // (60 detik) tidak berlaku di sini.
        $this->otp->issue($user, OtpService::PURPOSE_REGISTER, force: true);

        // Email gagal terkirim (mis. SMTP down / domain demo Mailtrap
        // menolak recipient)?
        //
        // Kode yang gagal terkirim HARUS DIHAPUS. Kalau tidak, kodenya
        // masih tercatat "aktif" selama 5 menit → halaman OTP tidak akan
        // mengirim kode baru, dan user harus menunggu cooldown tanpa
        // pernah menerima email.
        //
        // Setelah dihapus, halaman OTP akan membuat & mengirim kode baru
        // otomatis, dan session flag di bawah supaya UI menampilkan
        // peringatan ("email gagal dikirim") alih-alih diam saja.
        if ($this->otp->lastMailError() !== null) {
            $this->otp->discard($user, OtpService::PURPOSE_REGISTER);

            session()->flash('otpMailFailed', true);
        }
    }
}
