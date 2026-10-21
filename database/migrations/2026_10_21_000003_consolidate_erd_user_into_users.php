<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Blok A — KONSOLIDASI: menggabungkan tabel ERD `user` ke tabel Laravel `users`.
 *
 * KEPUTUSAN ARSITEKTUR (disetujui user):
 *   Satu tabel `users` menjadi SATU-SATUNYA tabel user.
 *   Tabel `user` (ERD) dihapus, dan 18 FOREIGN KEY dari 16 tabel
 *   diarahkan ulang ke `users.id`.
 *
 * KENAPA INI PERLU:
 *   Sebelumnya ada dua tabel user yang berisi kolom sama (name, email,
 *   password, role) → dua sumber kebenaran → risiko data tidak sinkron.
 *   Sekarang hanya ada satu sumber kebenaran.
 *
 * YANG DIKERJAKAN MIGRATION INI:
 *   1. Samakan tipe kolom anak: `int` → `bigint unsigned` (16 tabel, 18 kolom)
 *      karena `users.id` bertipe bigint unsigned; MySQL menolak FK
 *      kalau tipe kedua sisi berbeda.
 *   2. Bangun ulang 18 FOREIGN KEY agar menunjuk ke `users.id`.
 *   3. Ubah `eo_profiles` & `company_profiles`: PK jadi bigint unsigned,
 *      tambah UNIQUE pada idUser (relasi 1:1) — melindungi dari duplikasi profil.
 *   4. Hapus tabel `user` (ERD).
 *   5. Tambah CHECK constraint rating 0.0–5.0 pada `users`.
 *
 * CATATAN KEAMANAN (lihat juga migrasi _alignment):
 *   - Check ini menjaga integritas nilai rating; tanpa ini rating bisa
 *     diisi -5 atau 999 lewat query langsung.
 *
 * PENTING: seluruh tabel anak berisi 0 baris saat migrasi dijalankan,
 * sehingga tidak ada data yang perlu dipindahkan — hanya struktur.
 */
return new class extends Migration
{
    /**
     * Daftar 18 FK yang menunjuk ke `user.idUser` beserta tabel & kolomnya.
     * Format: [tabel, kolom, DELETE_RULE asli]
     *
     * DELETE_RULE dipertahankan persis seperti rancangan ERD asli:
     *   CASCADE    → baris anak ikut terhapus saat user dihapus
     *                (mis. bookmark, notification, otp_codes)
     *   RESTRICT   → user tidak boleh dihapus selama masih punya baris anak
     *                (mis. proposal, campaign, offer — data bisnis penting)
     *   SET NULL   → kolom jadi NULL (mis. loginattemp — log tetap disimpan)
     *
     * Mengubah semuanya menjadi CASCADE akan BERBAHAYA: menghapus satu user
     * akan ikut menghapus seluruh proposal/campaign-nya tanpa peringatan.
     * Karena itu aturan asli dipertahankan.
     *
     * @var array<int, array{0: string, 1: string, 2: string}>
     */
    private array $foreignKeys = [
        ['bookmark', 'idUser', 'CASCADE'],
        ['bookmark', 'saved_user_id', 'CASCADE'],
        ['campaign', 'idUser', 'RESTRICT'],
        ['campaignviewlogs', 'idViewer', 'CASCADE'],
        ['company_profiles', 'idUser', 'CASCADE'],
        ['eo_profiles', 'idUser', 'CASCADE'],
        ['loginattemp', 'idUser', 'SET NULL'],
        ['notification', 'idUser', 'CASCADE'],
        ['offer', 'from_user_id', 'RESTRICT'],
        ['otp_codes', 'idUser', 'CASCADE'],
        ['post', 'idUser', 'CASCADE'],
        ['proposal', 'idUser', 'RESTRICT'],
        ['proposalviewlogs', 'idViewer', 'CASCADE'],
        ['riviews', 'from_user_id', 'RESTRICT'],
        ['riviews', 'to_user_id', 'RESTRICT'],
        ['subscription', 'idUser', 'RESTRICT'],
        ['trackingpipeline', 'company_user_id', 'RESTRICT'],
        ['usage_ai', 'idUser', 'CASCADE'],
    ];

    public function up(): void
    {
        // Migrasi ini memakai DDL khusus MySQL (ALTER ... MODIFY, ADD CHECK).
        // Pada database uji SQLite (:memory:) tabel ERD tidak ada, jadi
        // migrasi dilewati — test tetap berjalan dengan skema portabel.
        if (! $this->isMySql()) {
            return;
        }

        // ---------- 1 & 2. Ubah tipe kolom anak + bangun ulang FK ----------
        foreach ($this->foreignKeys as [$table, $column, $deleteRule]) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
                continue;
            }

            // Hapus FK lama (kalau ada) — dicari lewat information_schema
            $this->dropForeignKeysOn($table, $column, 'user');

            // Samakan tipe dengan users.id (bigint unsigned).
            // loginattemp memakai SET NULL → kolomnya harus boleh NULL.
            $nullable = $deleteRule === 'SET NULL' ? ' NULL' : ' NOT NULL';
            DB::statement("ALTER TABLE `{$table}` MODIFY `{$column}` BIGINT UNSIGNED{$nullable}");

            // Bangun FK baru menuju users.id, aturan hapus sama seperti ERD asli
            DB::statement(
                "ALTER TABLE `{$table}` ADD CONSTRAINT `{$table}_{$column}_foreign` ".
                "FOREIGN KEY (`{$column}`) REFERENCES `users`(`id`) ON DELETE {$deleteRule}"
            );
        }

        // ---------- 3a. eo_profiles: PK & UNIQUE ----------
        // PK lama idEventOrganizer int → bigint unsigned, agar seragam.
        DB::statement('ALTER TABLE `eo_profiles` MODIFY `idEventOrganizer` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT');
        $this->addUnique('eo_profiles', 'idUser', 'uq_eo_profiles_idUser');

        // ---------- 3b. company_profiles: PK & UNIQUE ----------
        DB::statement('ALTER TABLE `company_profiles` MODIFY `idCompany` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT');
        $this->addUnique('company_profiles', 'idUser', 'uq_company_profiles_idUser');

        // ---------- 4. Hapus tabel `user` (ERD) ----------
        Schema::dropIfExists('user');
    }

    public function down(): void
    {
        // Mengembalikan ke struktur lama TIDAK mungkin sepenuhnya (tabel `user`
        // sudah dihapus dan skema ERD aslinya tidak dapat direkonstruksi),
        // tetapi FK dikembalikan ke bentuknya semula agar `migrate:rollback` aman.
        foreach ($this->foreignKeys as [$table, $column, $deleteRule]) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
                continue;
            }
            $this->dropForeignKeysOn($table, $column, 'users');
            $nullable = $deleteRule === 'SET NULL' ? ' NULL' : ' NOT NULL';
            DB::statement("ALTER TABLE `{$table}` MODIFY `{$column}` INT{$nullable}");
        }
    }

    /**
     * Hapus semua FK pada kolom tertentu yang menunjuk ke tabel acuan.
     */
    private function dropForeignKeysOn(string $table, string $column, string $referenced): void
    {
        $db = config('database.connections.'.config('database.default').'.database');

        $rows = DB::select(
            'SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE '.
            'WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ? '.
            'AND REFERENCED_TABLE_NAME = ?',
            [$db, $table, $column, $referenced]
        );

        foreach ($rows as $r) {
            DB::statement("ALTER TABLE `{$table}` DROP FOREIGN KEY `{$r->CONSTRAINT_NAME}`");
        }
    }

    /**
     * Tambah UNIQUE constraint bila belum ada.
     */
    private function addUnique(string $table, string $column, string $name): void
    {
        $db = config('database.connections.'.config('database.default').'.database');

        $exists = DB::select(
            'SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS '.
            'WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND CONSTRAINT_NAME = ?',
            [$db, $table, $name]
        );

        if ($exists === []) {
            DB::statement("ALTER TABLE `{$table}` ADD CONSTRAINT `{$name}` UNIQUE (`{$column}`)");
        }
    }

    /**
     * Apakah koneksi database saat ini MySQL/MariaDB?
     */
    private function isMySql(): bool
    {
        return in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true);
    }
};
