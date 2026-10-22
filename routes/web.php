
<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', fn () => Inertia::render('landing/Index'))->name('home');

/*
|--------------------------------------------------------------------------
| Login dengan Google (OAuth 2.0)
|--------------------------------------------------------------------------
| /auth/google/redirect  → diarahkan ke halaman persetujuan Google
| /auth/google/callback  → Google mengembalikan user ke sini
|
| Catatan: route ini SENGAJA di luar middleware `guest`. Kalau user punya
| session lama yang masih hidup, middleware `guest` akan memantulkannya ke
| dashboard sebelum callback sempat diproses. Pengecekan "sudah login atau
| belum" dilakukan di dalam controller.
*/
Route::get('auth/google/redirect', [\App\Http\Controllers\Auth\GoogleAuthController::class, 'redirect'])
    ->name('auth.google.redirect');

Route::get('auth/google/callback', [\App\Http\Controllers\Auth\GoogleAuthController::class, 'callback'])
    ->name('auth.google.callback');

/*
|--------------------------------------------------------------------------
| Pendaftaran per peran (Blok A)
|--------------------------------------------------------------------------
| Alur: pengguna memilih peran di /register, lalu diarahkan ke form yang
| sesuai. Keduanya memakai controller Fortify yang sama (RegisteredUserController)
| agar Inertia merender halaman sesuai nama route (lihat FortifyServiceProvider).
| Form di setiap halaman tetap POST ke route `register.store` (/register).
*/
Route::middleware('guest')->group(function () {
    Route::get('register/eo', [\Laravel\Fortify\Http\Controllers\RegisteredUserController::class, 'create'])
        ->name('register.eo');

    Route::get('register/perusahaan', [\Laravel\Fortify\Http\Controllers\RegisteredUserController::class, 'create'])
        ->name('register.perusahaan');
});


/*
|--------------------------------------------------------------------------
| Verifikasi Kode OTP
|--------------------------------------------------------------------------
| Halaman OTP dipakai untuk memverifikasi alamat email user.
| Setelah register berhasil, user diarahkan ke sini (lihat
| App\Http\Responses\RegisterResponse) sebelum boleh masuk dashboard.
|
| Middleware 'auth' saja — TAPI 'verified', karena user yang belum
| verifikasi justru HARUS bisa membuka halaman ini.
*/
Route::middleware('auth')->group(function () {
    Route::get('verify-otp', [\App\Http\Controllers\OtpController::class, 'show'])
        ->name('otp.show');

    Route::post('verify-otp/send', [\App\Http\Controllers\OtpController::class, 'send'])
        ->middleware('throttle:6,1')
        ->name('otp.send');

    Route::post('verify-otp/verify', [\App\Http\Controllers\OtpController::class, 'verify'])
        ->middleware('throttle:10,1')
        ->name('otp.verify');
});


Route::middleware(['auth', 'verified:otp.show'])->group(function () {
    Route::inertia('dashboard', 'dashboard/Index')->name('dashboard');
});

require __DIR__.'/settings.php';
