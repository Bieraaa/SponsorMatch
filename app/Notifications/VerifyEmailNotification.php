<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

/**
 * Notifikasi email verifikasi akun (pengganti bawaan Laravel).
 *
 * Kenapa dibuat ulang:
 * 1. Teksnya dibahasa Indonesia dan memakai warna brand SponsorMatch.
 * 2. Mengandung instruksi yang jelas + link verifikasi bertanda tangan
 *    (signed URL) yang berlaku 5 menit.
 *
 * CATATAN: ini jalur CADANGAN. Jalur utama verifikasi email SponsorMatch
 * adalah kode OTP (6 digit) di halaman /verify-otp. Link ini tetap
 * disediakan supaya user tidak terkunci kalau email OTP tidak masuk.
 *
 * Dipanggil dari App\Models\User::sendEmailVerificationNotification().
 */
class VerifyEmailNotification extends Notification
{
    use Queueable;

    /**
     * Berapa lama link verifikasi valid (menit).
     *
     * 5 menit — sama dengan masa berlaku kode OTP.
     */
    public const EXPIRE_MINUTES = 5;

    /**
     * Channels notifikasi.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Bangun pesan email.
     */
    public function toMail(object $notifiable): MailMessage
    {
        // Signed URL: whoever punya link ini tidak bisa memalsukan
        // (ada hash dari id user + email), dan berlaku 5 menit.
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(self::EXPIRE_MINUTES),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ],
        );

        return (new MailMessage)
            ->subject('Verifikasi alamat email kamu di SponsorMatch')
            ->view('mail.verify-email', [
                'verificationUrl' => $verificationUrl,
                'expireMinutes' => self::EXPIRE_MINUTES,
                'userName' => $notifiable->name,
            ]);
    }
}
