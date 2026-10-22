<?php

namespace App\Http\Controllers;

use App\Enums\OtpVerifyStatus;
use App\Models\OtpCode;
use App\Services\OtpService;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

/**
 * Controller halaman verifikasi kode OTP.
 *
 * Dipakai untuk memverifikasi alamat email user (purpose = 'register')
 * dan sebagai lapisan kedua saat login (purpose = 'login').
 *
 * Alur:
 *   GET  /verify-otp          → tampilkan halaman OTP (+ kirim kode pertama)
 *   POST /verify-otp/send     → kirim ulang kode (dibatasi cooldown 60 detik)
 *   POST /verify-otp/verify   → periksa kode yang di-input user
 */
class OtpController extends Controller
{
    /**
     * Tujuan OTP yang diizinkan.
     *
     * @var array<int, string>
     */
    protected const ALLOWED_PURPOSES = [
        OtpService::PURPOSE_REGISTER,
        OtpService::PURPOSE_LOGIN,
    ];

    public function __construct(protected OtpService $otp)
    {
    }

    /**
     * Tampilkan halaman OTP.
     */
    public function show(Request $request): Response|RedirectResponse
    {
        $user = $request->user();
        $purpose = $this->resolvePurpose($request);

        // Sudah verifikasi? Nggak perlu halaman ini lagi.
        if ($user->hasVerifiedEmail()) {
            return redirect()->route('dashboard');
        }

        // Belum ada kode aktif → kirim kode pertama kali.
        if (! $this->hasActiveCode($user, $purpose)) {
            $this->otp->issue($user, $purpose, force: true);
        }

        return Inertia::render('auth/otp/Index', [
            'purpose' => $purpose,
            'email' => $user->email,
            'userName' => $user->name,
            // Sisa detik sebelum kode expire (countdown "01:00" di desain).
            'expiresIn' => $this->otp->secondsUntilExpiry($user, $purpose),
            // Sisa detik sebelum boleh minta kode baru.
            'resendIn' => $this->resendSeconds($user, $purpose),
            // Pesan error / konfirmasi dari aksi sebelumnya (kirim ulang / verify).
            // Diambil dari flash session — otomatis hilang setelah dibaca sekali.
            'error' => session('otpError'),
            'sent' => (bool) session('otpSent'),
        ]);
    }

    /**
     * Kirim ulang kode OTP.
     */
    public function send(Request $request): RedirectResponse
    {
        $user = $request->user();
        $purpose = $this->resolvePurpose($request);

        $availableAt = $this->otp->resendAvailableAt($user, $purpose);

        if ($availableAt !== null) {
            $seconds = (int) now()->diffInSeconds($availableAt, false);

            return back()->with('otpError', "Kode baru belum bisa diminta. Coba lagi dalam {$seconds} detik.");
        }

        try {
            $this->otp->issue($user, $purpose);
        } catch (Throwable $e) {
            return back()->with('otpError', $e->getMessage());
        }

        return back()->with('otpSent', true);
    }

    /**
     * Verifikasi kode OTP yang di-input user.
     */
    public function verify(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6', 'regex:/^[0-9]{6}$/'],
        ]);

        $user = $request->user();
        $purpose = $this->resolvePurpose($request);

        $result = $this->otp->verify($user, (string) $request->input('code'), $purpose);

        if ($result->isSuccessful()) {
            // Tandai email sudah diverifikasi + trigger event Verified
            // (dipakai listener lain, misal untuk kirim email "selamat datang").
            $user->markEmailAsVerified();
            event(new Verified($user));

            // Toast muncul di dashboard (punya layout yang merender Toaster).
            Inertia::flash('toast', [
                'type' => 'success',
                'message' => 'Email berhasil diverifikasi. Selamat datang!',
            ]);

            return redirect()->route('dashboard');
        }

        $message = match ($result->status) {
            OtpVerifyStatus::Invalid => 'Kode OTP salah. Sisa percobaan: '.$result->remainingAttempts.'.',
            OtpVerifyStatus::Expired => 'Kode OTP sudah kedaluwarsa. Silakan minta kode baru.',
            OtpVerifyStatus::TooManyAttempts => 'Terlalu banyak percobaan salah. Silakan minta kode baru.',
            OtpVerifyStatus::NotFound => 'Belum ada kode OTP. Silakan minta kode baru.',
            default => 'Verifikasi gagal. Silakan coba lagi.',
        };

        return back()->with('otpError', $message);
    }

    /**
     * Tentukan purpose dari request, default 'register'.
     */
    protected function resolvePurpose(Request $request): string
    {
        $purpose = (string) $request->input('purpose', OtpService::PURPOSE_REGISTER);

        return in_array($purpose, self::ALLOWED_PURPOSES, true)
            ? $purpose
            : OtpService::PURPOSE_REGISTER;
    }

    /**
     * Apakah masih ada kode OTP aktif untuk user + purpose ini?
     */
    protected function hasActiveCode($user, string $purpose): bool
    {
        return OtpCode::where('idUser', $user->getKey())
            ->where('purpose', $purpose)
            ->where('expiredAt', '>', now())
            ->exists();
    }

    /**
     * Sisa detik cooldown sebelum boleh minta kode baru.
     */
    protected function resendSeconds($user, string $purpose): int
    {
        $availableAt = $this->otp->resendAvailableAt($user, $purpose);

        if ($availableAt === null) {
            return 0;
        }

        return (int) now()->diffInSeconds($availableAt, false);
    }
}
