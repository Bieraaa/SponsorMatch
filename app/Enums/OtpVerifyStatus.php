<?php

namespace App\Enums;

/**
 * Status hasil verifikasi kode OTP.
 *
 * Dipakai OtpController untuk memetakan status menjadi pesan error
 * yang berbeda-beda di halaman OTP.
 */
enum OtpVerifyStatus: string
{
    /** Kode cocok. */
    case Success = 'success';

    /** Kode salah, tapi masih ada kesempatan coba lagi. */
    case Invalid = 'invalid';

    /** Kode sudah lewat masa berlaku (60 detik). */
    case Expired = 'expired';

    /** Terlalu banyak percobaan — kode dihapus, harus minta ulang. */
    case TooManyAttempts = 'too_many_attempts';

    /** Tidak ada kode yang pernah dibuat untuk user + tujuan ini. */
    case NotFound = 'not_found';
}
