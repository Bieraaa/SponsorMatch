<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Data contoh tabel pendukung: log tampilan, laporan event, follow-up,
 * notifikasi, bookmark, postingan, percobaan login, kode OTP, dan
 * pemakaian AI.
 *
 * Sebagian tabel ini murni catatan (append-only) sehingga isinya hanya
 * contoh untuk mengisi tampilan/statistik.
 */
class SupportingDataSeeder extends Seeder
{
    public function run(): void
    {
        // ---------- Log tampilan proposal & campaign ----------
        DB::table('proposalviewlogs')->insert([
            ['idProposal' => 1, 'idViewer' => 4, 'viewedAt' => now()->subHours(5)],
            ['idProposal' => 1, 'idViewer' => 5, 'viewedAt' => now()->subHours(3)],
            ['idProposal' => 2, 'idViewer' => 6, 'viewedAt' => now()->subHours(2)],
        ]);

        DB::table('campaignviewlogs')->insert([
            ['idCampaign' => 1, 'idViewer' => 1, 'viewedAt' => now()->subHours(6)],
            ['idCampaign' => 2, 'idViewer' => 2, 'viewedAt' => now()->subHours(4)],
            ['idCampaign' => 3, 'idViewer' => 3, 'viewedAt' => now()->subHour()],
        ]);

        // ---------- Laporan event & follow-up (milik pipeline 'deal') ----------
        DB::table('event_reports')->insert([
            [
                'idTracking' => 3,
                'decription' => 'Event berjalan lancar, target peserta terlampaui 15%.',
                'goal_achieved' => 1, 'submittedAt' => now()->subDays(2),
            ],
        ]);

        DB::table('follow_up_logs')->insert([
            ['idTracking' => 1, 'sentAt' => now()->subDays(3)],
            ['idTracking' => 2, 'sentAt' => now()->subDays(1)],
        ]);

        // ---------- Notifikasi ----------
        DB::table('notification')->insert([
            [
                'idUser' => 1, 'type' => 'match',
                'message' => 'Proposal Anda cocok dengan kampanye "Kampanye Produk Baru Maju Jaya".',
                'link' => '/dashboard/proposal/1', 'createdAt' => now()->subHours(8), 'readAt' => null,
            ],
            [
                'idUser' => 4, 'type' => 'offer',
                'message' => 'Ada penawaran balik untuk kolaborasi Festival Musik Nusantara.',
                'link' => '/dashboard/pipeline/1', 'createdAt' => now()->subHours(6), 'readAt' => now()->subHours(5),
            ],
            [
                'idUser' => 3, 'type' => 'deal',
                'message' => 'Selamat! Kolaborasi Maraton Kepedulian Sosial telah mencapai tahap deal.',
                'link' => '/dashboard/pipeline/3', 'createdAt' => now()->subDays(2), 'readAt' => null,
            ],
        ]);

        // ---------- Bookmark (perusahaan menyimpan user/proposal) ----------
        DB::table('bookmark')->insert([
            ['idUser' => 4, 'saved_user_id' => 1],
            ['idUser' => 5, 'saved_user_id' => 2],
        ]);

        // ---------- Post (postingan komunitas) ----------
        DB::table('post')->insert([
            [
                'idUser' => 1, 'description' => 'Terima kasih untuk semua sponsor yang mendukung Festival Musik Nusantara!',
                'imagePath' => 'posts/festival-preview.jpg', 'createdAt' => now()->subDays(1), 'updateAt' => now()->subDays(1),
            ],
            [
                'idUser' => 5, 'description' => 'Kami membuka pendaftaran proposal untuk program CSR literasi digital.',
                'imagePath' => null, 'createdAt' => now()->subHours(10), 'updateAt' => now()->subHours(10),
            ],
        ]);

        // ---------- Log percobaan login (audit keamanan) ----------
        DB::table('loginattemp')->insert([
            ['idUser' => 1, 'email' => 'budi@eosnusantara.id', 'ip_adrress' => '127.0.0.1', 'succes' => 1, 'attemptedAt' => now()->subHours(4)],
            ['idUser' => null, 'email' => 'budi@eosnusantara.id', 'ip_adrress' => '127.0.0.1', 'succes' => 0, 'attemptedAt' => now()->subHours(4)],
            ['idUser' => 4, 'email' => 'siti@majujaya.co.id', 'ip_adrress' => '127.0.0.1', 'succes' => 1, 'attemptedAt' => now()->subHours(2)],
        ]);

        // ---------- Kode OTP ----------
        DB::table('otp_codes')->insert([
            [
                'idUser' => 1, 'purpose' => 'login',
                'code_hash' => hash('sha256', '123456'), 'attemp' => 0,
                'expiredAt' => now()->addMinutes(10), 'createdAt' => now(),
            ],
        ]);

        // ---------- Pemakaian AI ----------
        DB::table('usage_ai')->insert([
            ['idUser' => 1, 'type' => 'extract_proposal', 'usage_date' => now()->toDateString(), 'used' => 3],
            ['idUser' => 4, 'type' => 'extract_campaign', 'usage_date' => now()->toDateString(), 'used' => 2],
        ]);
    }
}
