<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notifikasi reset password (pengganti bawaan Laravel).
 *
 * Isinya sama polanya dengan email verifikasi:
 * - Bahasa Indonesia
 * - Warna brand
 * - Pintasan (PIN) 5 menit untuk setting password baru
 */
class ResetPasswordNotification extends Notification
{
    use Queueable;

    /**
     * Berapa lama PIN reset password valid (menit).
     *
     * 5 menit — sama dengan masa berlaku kode OTP, supaya konsisten.
     * Perhatikan: nilai INI hanya untuk teks di email. Yang benar-benar
     * menentukan kedaluwarsa token adalah `config('auth.passwords.users.expire')`.
     */
    public const EXPIRE_MINUTES = 5;

    /**
     * Token reset password dari tabel `password_reset_tokens`.
     */
    public function __construct(public string $token)
    {
    }

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
        $resetUrl = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        return (new MailMessage)
            ->subject('Reset password akun SponsorMatch kamu')
            ->view('mail.reset-password', [
                'resetUrl' => $resetUrl,
                'expireMinutes' => self::EXPIRE_MINUTES,
                'userName' => $notifiable->name,
            ]);
    }
}
