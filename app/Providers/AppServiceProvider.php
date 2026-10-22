<?php

namespace App\Providers;

use App\Listeners\RecordFailedLogin;
use App\Listeners\RecordSuccessfulLogin;
use App\Listeners\SendOtpOnRegistration;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->configureEventListeners();
    }

    /**
     * Daftarkan pendengar event autentikasi (Blok A — keamanan).
     *
     * Laravel 11+ tidak lagi punya EventServiceProvider bawaan, jadi
     * pemetaan event → listener dilakukan di sini:
     *   - Login  berhasil → isi last_login_at + catat ke loginattemp
     *   - Failed          → catat percobaan login gagal ke loginattemp
     */
    protected function configureEventListeners(): void
    {
        Event::listen(Login::class, RecordSuccessfulLogin::class);
        Event::listen(Failed::class, RecordFailedLogin::class);

        // ---------------------------------------------------------------
        // PENDAFTARAN → KIRIM KODE OTP (bukan link verifikasi email).
        //
        // Kenapa dipasang di dalam callback `booted`?
        //
        // Framework Laravel memasang listener bawaan
        // `SendEmailVerificationNotification` ke event `Registered` DI
        // DALAM callback `booted` milik EventServiceProvider-nya.
        // Callback `booted` dijalankan berurutan sesuai urutan registrasi
        // provider, dan provider framework didaftarkan lebih dulu — jadi
        // listener bawaan sudah terpasang ketika callback kita berjalan.
        //
        // Efeknya: user yang daftar hanya menerima SATU email, yaitu
        // email berisi kode OTP. Tidak ada lagi email "verifikasi email"
        // berisi tautan yang membuat user salah pilih alur.
        // ---------------------------------------------------------------
        $this->app->booted(function (): void {
            // Buang listener bawaan Laravel.
            Event::forget(Registered::class);

            // Ganti dengan listener kita: kirim kode OTP.
            Event::listen(Registered::class, SendOtpOnRegistration::class);
        });
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
