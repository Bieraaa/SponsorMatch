<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;
use Symfony\Component\HttpFoundation\Response;

/**
 * Response setelah pendaftaran berhasil.
 *
 * Response bawaan Fortify memakai `redirect()->intended(...)`, yang bisa
 * mengarahkan user ke URL lain kalau ada "intended URL" di session.
 * Karena pendaftaran kita selalu harus lanjut ke VERIFIKASI EMAIL,
 * kita buat response sendiri yang selalu mengarah ke halaman OTP.
 *
 * PENGECILIAN: user yang mendaftar lewat GOOGLE email-nya sudah diverifikasi
 * oleh Google, jadi tidak perlu halaman OTP sama sekali — langsung ke
 * dashboard. Tanpa pengecualian ini, user Google akan melihat halaman OTP
 * dengan hitung mundur ke kode yang memang tidak pernah dikirim.
 */
class RegisterResponse implements RegisterResponseContract
{
    /**
     * @param  \Illuminate\Http\Request  $request
     */
    public function toResponse($request): Response
    {
        if ($request->wantsJson()) {
            return new JsonResponse('', 201);
        }

        // Sudah terverifikasi (pendaftaran lewat Google) → langsung dashboard.
        if ($request->user()?->hasVerifiedEmail()) {
            return redirect()->route('dashboard');
        }

        return redirect()->route('otp.show');
    }
}
