<?php

namespace App\Providers;

use App\Listeners\RecordFailedLogin;
use App\Listeners\RecordSuccessfulLogin;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
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
