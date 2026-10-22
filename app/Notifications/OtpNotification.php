<?php

namespace App\Notifications;

use App\Services\OtpService;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notifikasi email berisi kode OTP.
 *
 * Kodenya ditampilkan dalam 6 kotak besar supaya mudah dibaca user
 * dan tidak perlu diketik ulang (bisa di-select & paste).
 */
class OtpNotification extends Notification
{
    use Queueable;

    /**
     * @param  string  $code  Kode OTP 6 digit (tebak, hanya berlaku sekali).
     * @param  string  $purpose  register atau login.
     * @param  int  $ttlSeconds  Masa berlaku kode dalam detik.
     */
    public function __construct(
        public string $code,
        public string $purpose = OtpService::PURPOSE_REGISTER,
        public int $ttlSeconds = OtpService::TTL_SECONDS,
    ) {
    }

    /**
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
        $seconds = (int) round($this->ttlSeconds / 60);

        return (new MailMessage)
            ->subject('Kode OTP SponsorMatch: '.$this->code)
            ->view('mail.otp', [
                'code' => $this->code,
                'digits' => str_split($this->code),
                'purpose' => $this->purpose,
                'expireSeconds' => $this->ttlSeconds,
                'expireMinutes' => $seconds,
                'userName' => $notifiable->name,
            ]);
    }
}
