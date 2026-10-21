<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Blok A — perbaikan tabel `offer` yang membuat data pertama TIDAK MUNGKIN
 * disimpan (temuan audit, sudah dikonfirmasi bug nyata saat seeding).
 *
 * MASALAH 1 — `parrent_offer_id` NOT NULL + mengarah ke dirinya sendiri.
 *   Kolom ini menunjuk offer induk (rantai negosiasi: penawaran → balasan).
 *   Karena NOT NULL, baris PERTAMA pada tabel tidak punya induk untuk
 *   dirujuk → INSERT selalu gagal dengan error foreign key.
 *   → Dijadikan NULL. Offer pertama (nilai NULL) berarti "penawaran awal".
 *   → Nama kolom juga diperbaiki: `parrent_offer_id` → `parent_offer_id`.
 *
 * MASALAH 2 — `rejectReasong` NOT NULL (typo: harusnya rejectReason).
 *   Tidak semua offer ditolak, sehingga alasan penolakan wajar kosong.
 *   → Dijadikan NULL + ejaan diperbaiki.
 *
 * PENTING: kedua kolom masih kosong (0 baris) saat perbaikan ini berjalan,
 * jadi tidak ada data yang perlu dipindahkan.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('offer')) {
            return;
        }

        $driver = DB::connection()->getDriverName();

        if (! in_array($driver, ['mysql', 'mariadb'], true)) {
            return;
        }

        // ---------- 1. parrent_offer_id → parent_offer_id, nullable ----------
        if (Schema::hasColumn('offer', 'parrent_offer_id')) {
            // Lepas FK lama lebih dulu (nama constraint dari DDL ERD).
            $this->dropForeign('offer', 'parrent_offer_id');

            DB::statement('ALTER TABLE `offer` CHANGE `parrent_offer_id` `parent_offer_id` INT NULL DEFAULT NULL');

            // FK baru: on delete set null (induk dihapus → anak jadi penawaran awal)
            DB::statement(
                'ALTER TABLE `offer` ADD CONSTRAINT `offer_parent_offer_id_foreign` '.
                'FOREIGN KEY (`parent_offer_id`) REFERENCES `offer`(`idOffer`) ON DELETE SET NULL'
            );
        }

        // ---------- 2. rejectReasong → rejectReason, nullable ----------
        if (Schema::hasColumn('offer', 'rejectReasong')) {
            DB::statement('ALTER TABLE `offer` CHANGE `rejectReasong` `rejectReason` TEXT NULL DEFAULT NULL');
        }

        // ---------- 3. Perbaiki primary key tanpa AUTO_INCREMENT ----------
        // `riviews.idRiviews` dan `subscription.idSubscription` bertipe primary
        // key tetapi TIDAK di-set AUTO_INCREMENT pada DDL ERD. Akibatnya,
        // setiap INSERT wajib mengisi id secara manual — dan aplikasi normal
        // tidak melakukannya, sehingga penyimpanan gagal. Dijadikan
        // AUTO_INCREMENT supaya perilakunya seperti tabel lain.
        //
        // Catatan: `subscription.idSubscription` dirujuk oleh `transaksi`,
        // jadi FK-nya harus dilepas dulu lalu dipasang kembali.
        $this->dropForeign('transaksi', 'idSubscription');
        $this->makeAutoIncrement('riviews', 'idRiviews');
        $this->makeAutoIncrement('subscription', 'idSubscription');
        DB::statement(
            'ALTER TABLE `transaksi` ADD CONSTRAINT `transaksi_idSubscription_foreign` '.
            'FOREIGN KEY (`idSubscription`) REFERENCES `subscription`(`idSubscription`) ON DELETE CASCADE'
        );
    }

    /**
     * Jadikan kolom primary key sebagai AUTO_INCREMENT.
     */
    private function makeAutoIncrement(string $table, string $column): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
            return;
        }

        $col = DB::selectOne("SHOW COLUMNS FROM `{$table}` LIKE '{$column}'");
        if ($col && str_contains((string) $col->Extra, 'auto_increment')) {
            return;
        }

        DB::statement("ALTER TABLE `{$table}` MODIFY `{$column}` INT NOT NULL AUTO_INCREMENT");
    }

    public function down(): void
    {
        if (! Schema::hasTable('offer')) {
            return;
        }

        if (! in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true)) {
            return;
        }

        if (Schema::hasColumn('offer', 'parent_offer_id')) {
            $this->dropForeign('offer', 'parent_offer_id');
            DB::statement('ALTER TABLE `offer` CHANGE `parent_offer_id` `parrent_offer_id` INT NOT NULL');
            DB::statement(
                'ALTER TABLE `offer` ADD CONSTRAINT `offer_ibfk_3` '.
                'FOREIGN KEY (`parrent_offer_id`) REFERENCES `offer`(`idOffer`) ON DELETE CASCADE'
            );
        }

        if (Schema::hasColumn('offer', 'rejectReason')) {
            DB::statement('ALTER TABLE `offer` CHANGE `rejectReason` `rejectReasong` TEXT NOT NULL');
        }
    }

    /**
     * Hapus FK pada kolom tertentu (cari namanya lewat information_schema).
     */
    private function dropForeign(string $table, string $column): void
    {
        $db = config('database.connections.'.config('database.default').'.database');

        $rows = DB::select(
            'SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE '.
            'WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ? '.
            'AND REFERENCED_TABLE_NAME IS NOT NULL',
            [$db, $table, $column]
        );

        foreach ($rows as $r) {
            DB::statement("ALTER TABLE `{$table}` DROP FOREIGN KEY `{$r->CONSTRAINT_NAME}`");
        }
    }
};
