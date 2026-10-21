<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
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
        $this->configureActions();
        $this->configureViews();
        $this->configureRateLimiting();
    }

    /**
     * Configure Fortify actions.
     */
    private function configureActions(): void
    {
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::authenticateUsing($this->authenticateUser(...));
    }

    /**
     * Autentikasi kustom (Blok A — keamanan).
     *
     * Menggantikan pencarian kredensial bawaan supaya akun yang
     * dinonaktifkan (`isActive = false`) TIDAK bisa login. Pencocokan
     * password tetap memakai Hash::check (aman, constant-time).
     *
     * Mengembalikan null bila kredensial tidak cocok atau akun nonaktif —
     * Fortify akan menampilkan pesan error umum untuk keduanya, sehingga
     * status akun tidak bocor ke penyerang.
     */
    private function authenticateUser(Request $request): ?\App\Models\User
    {
        $email = (string) $request->input(Fortify::username());
        $password = (string) $request->input('password');

        $user = \App\Models\User::where('email', $email)->first();

        if ($user === null || ! Hash::check($password, $user->password)) {
            return null;
        }

        if (! $user->isActive) {
            return null;
        }

        return $user;
    }

    /**
     * Configure Fortify views.
     *
     * Blok A — setiap halaman auth tinggal di foldernya masing-masing
     * (pages/auth/login/Index.vue, pages/auth/register/Index.vue, dst).
     * Komponen utamanya selalu bernama "Index.vue" supaya konvensinya seragam:
     * nama folder = nama fitur, isi folder = bagian-bagiannya.
     */
    private function configureViews(): void
    {
        Fortify::loginView(fn (Request $request) => Inertia::render('auth/login/Index', [
            'canResetPassword' => Features::enabled(Features::resetPasswords()),
            'status' => $request->session()->get('status'),
        ]));

        Fortify::resetPasswordView(fn (Request $request) => Inertia::render('auth/reset-password/Index', [
            'email' => $request->email,
            'token' => $request->route('token'),
            'passwordRules' => Password::defaults()->toPasswordRulesString(),
        ]));

        Fortify::requestPasswordResetLinkView(fn (Request $request) => Inertia::render('auth/forgot-password/Index', [
            'status' => $request->session()->get('status'),
        ]));

        Fortify::verifyEmailView(fn (Request $request) => Inertia::render('auth/verify-email/Index', [
            'status' => $request->session()->get('status'),
        ]));

        // Blok A — pendaftaran 2 langkah dengan URL terpisah per peran:
        //   GET /register               → layar "Role Pick" (pilih peran)
        //   GET /register/eo            → form Event Organizer
        //   GET /register/perusahaan    → form Perusahaan
        // Ketiganya POST ke /register (route Fortify yang sama), dengan
        // <input type="hidden" name="role"> menentukan cabang validasinya.
        Fortify::registerView(function (Request $request) {
            $passwordRules = Password::defaults()->toPasswordRulesString();

            return match ($request->route()?->getName()) {
                'register.eo' => Inertia::render('auth/register/eo/Index', [
                    'passwordRules' => $passwordRules,
                ]),
                'register.perusahaan' => Inertia::render('auth/register/perusahaan/Index', [
                    'passwordRules' => $passwordRules,
                ]),
                default => Inertia::render('auth/register/Index'),
            };
        });

        Fortify::twoFactorChallengeView(fn () => Inertia::render('auth/two-factor/Index'));

        Fortify::confirmPasswordView(fn () => Inertia::render('auth/confirm-password/Index'));
    }

    /**
     * Configure rate limiting.
     */
    private function configureRateLimiting(): void
    {
        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('passkeys', function (Request $request) {
            return Limit::perMinute(10)->by(
                ($request->input('credential.id') ?: $request->session()->getId()).'|'.$request->ip(),
            );
        });
    }
}
