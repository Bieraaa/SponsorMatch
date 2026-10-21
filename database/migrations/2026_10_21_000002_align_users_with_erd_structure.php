<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Blok A — menyelaraskan tabel `users` dengan struktur ERD (Opsi 2).
 *
 * KEPUTUSAN: tabel ERD `user` dan tabel bawaan Laravel `users` DIGABUNG
 * menjadi satu tabel `users`. Tabel `user` (ERD) dihapus pada migration
 * terpisah setelah kolom-kolomnya dipindahkan ke sini.
 *
 * Nama kolom ERD disesuaikan dengan kebiasaan Laravel supaya fitur bawaan
 * (remember me, timestamps, verifikasi email, factory) bekerja tanpa trik:
 *
 *   ERD                →  Laravel
 *   ---------------------------------------------------------------
 *   idUser             →  id             (primary key bawaan)
 *   email_verified     →  email_verified_at
 *   rememberToken      →  remember_token
 *   createdAt          →  created_at
 *   updateAt           →  updated_at
 *
 * Kolom ERD yang BELUM ADA di users ditambahkan di sini:
 *   nomor_telepon, jabatan, status_subscription, rating, isActive,
 *   last_login_at, anonymized_at
 *
 * Perbaikan kesalahan struktur ERD (lihat catatan audit):
 *   - nomor_telepon: `text NOT NULL` → varchar(30) nullable
 *     (TEXT tidak bisa dijadikan index dan tidak layak wajib;
 *      nomor telepon tidak pernah sepanjang itu)
 *   - jabatan: NOT NULL → nullable
 *     (akun bisa dibuat tanpa jabatan, mis. akun uji/admin)
 *   - rating: decimal(10,0) → decimal(2,1) NULL
 *     (decimal(10,0) = 0 angka desimal, sehingga 4.5 otomatis menjadi 5.
 *      Ini bug nyata; maksudnya jelas rating berdesimal 0.0–5.0)
 *   - status_subscription: enum typo 'Subsricption' → 'Subscription'
 *
 * Kolom 2FA & remember tetap dipertahankan supaya seluruh fitur Laravel
 * (two-factor, passkeys) tidak kehilangan tempat penyimpanan.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // --- Data pendaftaran (ERD) ---
            $table->string('nomor_telepon', 30)->nullable()->after('role');
            $table->string('jabatan', 100)->nullable()->after('nomor_telepon');

            // --- Langganan, rating, status akun (ERD) ---
            $table->enum('status_subscription', ['Reguler', 'Subscription'])
                ->default('Reguler')
                ->after('jabatan');

            $table->decimal('rating', 2, 1)->nullable()->after('status_subscription');
            $table->boolean('isActive')->default(true)->after('rating');

            // --- Jejak aktivitas & anonimisasi (ERD) ---
            $table->timestamp('last_login_at')->nullable()->after('remember_token');
            $table->timestamp('anonymized_at')->nullable()->after('last_login_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'nomor_telepon',
                'jabatan',
                'status_subscription',
                'rating',
                'isActive',
                'last_login_at',
                'anonymized_at',
            ]);
        });
    }
};
