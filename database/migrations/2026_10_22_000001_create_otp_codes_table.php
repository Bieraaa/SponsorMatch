<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel `otp_codes` — penyimpanan kode OTP (One-Time Password).
 *
 * Keterangan:
 *   - Tabel ini SUDAH ada di MySQL (dibuat lewat DDL ERD), jadi di
 *     MySQL migration ini melakukan NOTHING (skip) supaya data lama
 *     tidak hilang.
 *   - Tapi database testing memakai SQLite `:memory:` yang dibangun
 *     dari nol lewat migration — tanpa migration ini, tabel `otp_codes`
 *     tidak ada di test DB dan semua test OTP gagal dengan
 *     "no such table: otp_codes".
 *
 * Karena itu tabel dibuat dengan Schema API yang portable (bukan DDL
 * MySQL mentah), sehingga aman dijalankan di kedua database.
 *
 * Keamanan:
 *   - `code_hash` menyimpan HMAC-SHA256 dari kode, BUKAN kode aslinya.
 *     Kalau tabel ini bocor, kode OTP tidak bisa langsung dipakai.
 *   - `attemp` membatasi jumlah tebakan (dihapus di OtpService).
 */
return new class extends Migration
{
    public function up(): void
    {
        // Sudah ada (MySQL) → lewati, jangan sentuh data yang ada.
        if (Schema::hasTable('otp_codes')) {
            return;
        }

        Schema::create('otp_codes', function (Blueprint $table) {
            // Primary key: `idOTP` (nama tidakstandar, mengikuti ERD).
            $table->increments('idOTP');

            // Pemilik kode → user. Hapus user = hapus kodenya.
            $table->unsignedBigInteger('idUser');

            // Tujuan penggunaan: register (verifikasi email) / login.
            $table->enum('purpose', ['login', 'register']);

            // Hash kode OTP (HMAC-SHA256 + APP_KEY).
            $table->string('code_hash', 255);

            // Jumlah percobaan tebakan yang salah.
            $table->unsignedTinyInteger('attemp')->default(0);

            // Batas masa berlaku kode.
            $table->timestamp('expiredAt');

            // Waktu kode dibuat (dipakai untuk cooldown kirim ulang).
            $table->timestamp('createdAt')->useCurrent();

            $table->foreign('idUser')->references('id')->on('users')->cascadeOnDelete();
            $table->index('expiredAt');
            $table->index(['idUser', 'purpose']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('otp_codes');
    }
};
