<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Blok A — PENINGKATAN KEAMANAN & INTEGRITAS DATA.
 *
 * Hasil audit menemukan 4 hal yang perlu diperbaiki. Migrasi ini
 * menangani yang bersifat struktur database:
 *
 *   1. CHECK constraint rating (0.0 – 5.0)
 *      Tanpa ini, kolom rating bisa diisi nilai di luar rentang lewat
 *      query langsung (mis. -10 atau 999), merusak perhitungan rata-rata.
 *
 *   2. Perlindungan NPWP & NIB (data legal perusahaan).
 *      `nib` dan `npwp` menyimpan nilai asli. Nilai aslinya akan
 *      DIENKRIPSI oleh aplikasi (Laravel `encrypted` cast, AES-256-CBC
 *      dengan APP_KEY). Karena ciphertext jauh lebih panjang dari nilai
 *      aslinya dan berbentuk base64, kolom `text` tetap dipakai — namun
 *      kolom dinyatakan boleh NULL supaya perusahaan tanpa NIB dapat
 *      mendaftar (NIB tidak selalu dimiliki perusahaan baru).
 *
 *      `nib_hash` / `npwp_hash` menjadi kunci pencarian (lookup) hasil
 *      HMAC-SHA256, karena ciphertext tidak bisa dicari dengan WHERE.
 *      HMAC dipilih (bukan SHA-256 polos) supaya APP_KEY menjadi kunci
 *      rahasia: tanpa APP_KEY, hash tidak bisa dihitung ulang oleh penyerang
 *      (mencegah serangan tebak-tebakan / rainbow table).
 *
 *   3. Index untuk pencarian login.
 *      `last_login_at` diindeks untuk audit login; `email` sudah UNIQUE
 *      (index otomatis) sehingga tidak perlu ditambah.
 *
 *   4. Status akun: memastikan isActive punya default dan tidak NULL.
 */
return new class extends Migration
{
    public function up(): void
    {
        // DDL khusus MySQL (ADD CHECK) — dilewati pada SQLite (database uji).
        if (! $this->isMySql()) {
            return;
        }

        // ---------- 1. CHECK rating 0.0 – 5.0 ----------
        // MySQL 8 mendukung CHECK constraint. Nama dibuat unik & jelas.
        if (! $this->hasCheck('users', 'chk_users_rating_range')) {
            DB::statement(
                'ALTER TABLE `users` ADD CONSTRAINT `chk_users_rating_range` '.
                'CHECK (`rating` IS NULL OR (`rating` >= 0.0 AND `rating` <= 5.0))'
            );
        }

        // ---------- 2. NPWP & NIB boleh kosong (nullable) ----------
        // Perusahaan baru belum tentu punya NIB. Kolom tetap text karena
        // menampung ciphertext (panjang, base64).
        Schema::table('company_profiles', function (Blueprint $table) {
            $table->text('nib')->nullable()->change();
            $table->text('npwp')->nullable()->change();
        });

        // ---------- 3. Index untuk audit login ----------
        if (! $this->hasIndex('users', 'idx_users_last_login_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->index('last_login_at', 'idx_users_last_login_at');
            });
        }

        // ---------- 4. isActive: pastikan default & not null ----------
        $col = DB::selectOne("SHOW COLUMNS FROM `users` LIKE 'isActive'");
        if ($col && $col->Null === 'YES') {
            DB::statement('UPDATE `users` SET `isActive` = 1 WHERE `isActive` IS NULL');
            DB::statement('ALTER TABLE `users` MODIFY `isActive` TINYINT(1) NOT NULL DEFAULT 1');
        }
    }

    public function down(): void
    {
        if ($this->hasCheck('users', 'chk_users_rating_range')) {
            DB::statement('ALTER TABLE `users` DROP CHECK `chk_users_rating_range`');
        }

        if ($this->hasIndex('users', 'idx_users_last_login_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropIndex('idx_users_last_login_at');
            });
        }
    }

    /**
     * Cek keberadaan CHECK constraint.
     */
    private function hasCheck(string $table, string $name): bool
    {
        $db = config('database.connections.'.config('database.default').'.database');

        $rows = DB::select(
            'SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS '.
            'WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND CONSTRAINT_NAME = ? '.
            'AND CONSTRAINT_TYPE = ?',
            [$db, $table, $name, 'CHECK']
        );

        return $rows !== [];
    }

    /**
     * Cek keberadaan index.
     */
    private function hasIndex(string $table, string $name): bool
    {
        $db = config('database.connections.'.config('database.default').'.database');

        $rows = DB::select(
            'SELECT INDEX_NAME FROM information_schema.STATISTICS '.
            'WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND INDEX_NAME = ?',
            [$db, $table, $name]
        );

        if ($rows !== []) {
            return true;
        }

        return false;
    }

    /**
     * Apakah koneksi database saat ini MySQL/MariaDB?
     */
    private function isMySql(): bool
    {
        return in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true);
    }
};
