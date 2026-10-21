
<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', fn () => Inertia::render('landing/Index'))->name('home');

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


Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard/Index')->name('dashboard');
});

require __DIR__.'/settings.php';
