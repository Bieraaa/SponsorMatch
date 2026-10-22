<?php

namespace App\Services;

use App\Enums\OtpVerifyStatus;

/**
 * Hasil proses verifikasi kode OTP.
 *
 * Immutable (readonly) supaya aman dibaca di banyak tempat tanpa
 * ada yang bisa mengubahnya di tengah jalan.
 */
final readonly class OtpVerificationResult
{
    /**
     * @param  OtpVerifyStatus  $status  Status verifikasi.
     * @param  int  $remainingAttempts  Sisa percobaan yang boleh dilakukan lagi.
     */
    public function __construct(
        public OtpVerifyStatus $status,
        public int $remainingAttempts = 0,
    ) {
    }

    /**
     * Verifikasi berhasil?
     */
    public function isSuccessful(): bool
    {
        return $this->status === OtpVerifyStatus::Success;
    }
}
