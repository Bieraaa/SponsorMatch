<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Seeder utama.
 *
 * Menjalankan seluruh seeder data contoh dengan URUTAN yang benar
 * (mengikuti ketergantungan FOREIGN KEY):
 *
 *   kategori → user (+profil peran) → proposal/campaign →
 *   matches → trackingpipeline → offer → riviews →
 *   subscription → transaksi → data pendukung
 *
 * Sebelum mengisi, semua tabel domain DIKOSONGKAN lebih dulu supaya hasil
 * seed bersifat idempoten (dijalankan dua kali tidak menumpuk data).
 * Pengosongan dilakukan dengan urutan terbalik + FOREIGN_KEY_CHECKS
 * dimatikan selama proses, sehingga tidak terhalang aturan RESTRICT.
 */
class DatabaseSeeder extends Seeder
{
    /**
     * Semua tabel domain (di luar tabel sistem Laravel) dalam urutan
     * yang aman untuk di-TRUNCATE (anak dulu, induk terakhir).
     *
     * @var array<int, string>
     */
    private array $tables = [
        // anak / turunan
        'transaksi', 'subscription', 'subscriptionplans',
        'riviews', 'offer', 'trackingpipeline', 'matches',
        'event_reports', 'follow_up_logs',
        'proposalviewlogs', 'campaignviewlogs',
        'notification', 'bookmark', 'post',
        'loginattemp', 'otp_codes', 'usage_ai',
        'proposal', 'campaign', 'kategori',
        // profil & akun
        'company_profiles', 'eo_profiles', 'users',
    ];

    public function run(): void
    {
        $this->clearDomainTables();

        $this->call([
            KategoriSeeder::class,
            UserSeeder::class,
            BusinessDataSeeder::class,
            SupportingDataSeeder::class,
        ]);
    }

    /**
     * Kosongkan tabel domain sebelum seeding.
     */
    private function clearDomainTables(): void
    {
        Schema::disableForeignKeyConstraints();

        foreach ($this->tables as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->truncate();
            }
        }

        Schema::enableForeignKeyConstraints();
    }
}
