<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Blok A — jadikan kolom `users.role` sebagai ENUM('eo','sponsor').
 *
 * SEBELUMNYA: varchar(255) dengan default 'eo'.
 * MASALAH: varchar 255 menerima nilai apa pun — 'eo', 'EO', 'sponsor ',
 *          'admin', 'test'. Salah ketik sekecil spasi akan membuat
 *          dashboard tidak mengenali peran user dan gagal menampilkan
 *          tampilan yang tepat, tanpa pesan error apa pun.
 *
 * SESUDAHNYA: ENUM('eo','sponsor'). Database MENOLAK nilai di luar dua
 *             ini, jadi peran selalu valid — tidak bisa lagi "user tanpa
 *             peran yang dikenali".
 *
 * Nilai tampilan ("Event Organizer"/"Perusahaan") TIDAK disimpan di sini,
 * melainkan dihasilkan oleh App\Enums\UserRole::label() saat ditampilkan.
 */
return new class extends Migration
{
    public function up(): void
    {
        // DDL khusus MySQL (MODIFY ... ENUM) — dilewati pada SQLite (database uji).
        // Pada SQLite, kolom role tetap varchar dan MySQL-style enum tidak
        // diperlukan; validasi tetap ditegakkan di lapisan aplikasi.
        if (! $this->isMySql()) {
            return;
        }

        // Amankan: kalau ada nilai aneh (mis. 'sponsor '), samakan dulu.
        DB::statement("UPDATE `users` SET `role` = 'eo' WHERE `role` NOT IN ('eo', 'sponsor')");
        DB::statement("UPDATE `users` SET `role` = TRIM(`role`)");

        DB::statement(
            "ALTER TABLE `users` MODIFY `role` ENUM('eo', 'sponsor') NOT NULL DEFAULT 'eo'"
        );
    }

    public function down(): void
    {
        if (! $this->isMySql()) {
            return;
        }

        DB::statement("ALTER TABLE `users` MODIFY `role` VARCHAR(255) NOT NULL DEFAULT 'eo'");
    }

    /**
     * Apakah koneksi database saat ini MySQL/MariaDB?
     */
    private function isMySql(): bool
    {
        return in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true);
    }
};
