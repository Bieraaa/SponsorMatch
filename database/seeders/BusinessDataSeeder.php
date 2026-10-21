<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Data contoh untuk alur bisnis inti: proposal → campaign → kecocokan →
 * pipeline → penawaran → transaksi pendukung.
 *
 * URUTAN PENTING (karena ada FOREIGN KEY):
 *   1. proposal & campaign   (butuh user + kategori)
 *   2. matches               (butuh proposal + campaign)
 *   3. trackingpipeline      (butuh proposal + campaign + user perusahaan)
 *   4. offer                 (butuh tracking)
 *   5. riviews               (butuh tracking + 2 user)
 *   6. subscription          (butuh user + subscriptionplans)
 *   7. transaksi             (butuh subscription)
 *   8. log & pendukung       (view logs, notifikasi, bookmark, dll.)
 */
class BusinessDataSeeder extends Seeder
{
    public function run(): void
    {
        // ---------- 1. PROPOSAL (dibuat Event Organizer) ----------
        $proposals = [
            [
                'idUser' => 1, 'idKategori' => 1,
                'title' => 'Festival Musik Nusantara 2026',
                'desctiption' => 'Festival musik tahunan yang menampilkan musisi lokal dan nasional selama dua hari.',
                'target_audience' => 'Remaja & dewasa muda 18-35 tahun, penggemar musik',
                'location' => 'Gelora Bung Karno, Jakarta',
                'needType' => 'Dana & produk',
                'budgetMin' => 50_000_000, 'budgetMax' => 150_000_000,
                'benefits' => 'Logo pada panggung utama, booth eksklusif, publikasi media sosial',
                'file_path' => 'proposals/festival-musik-nusantara-2026.pdf',
                'status' => 'published',
                'start_date' => '2026-11-15', 'end_date' => '2026-11-16',
            ],
            [
                'idUser' => 2, 'idKategori' => 2,
                'title' => 'Hackathon AI Indonesia',
                'desctiption' => 'Kompetisi 48 jam membangun solusi berbasis AI untuk masalah nyata di Indonesia.',
                'target_audience' => 'Mahasiswa, developer, startup early-stage',
                'location' => 'Gedung Innovation Center, Bandung',
                'needType' => 'Dana & mentor',
                'budgetMin' => 30_000_000, 'budgetMax' => 80_000_000,
                'benefits' => 'Mentoring session, rekrutmen talenta, logo pada materi acara',
                'file_path' => 'proposals/hackathon-ai-indonesia.pdf',
                'status' => 'published',
                'start_date' => '2026-12-05', 'end_date' => '2026-12-07',
            ],
            [
                'idUser' => 3, 'idKategori' => 3,
                'title' => 'Maraton Kepedulian Sosial',
                'desctiption' => 'Maraton amal yang hasilnya disalurkan untuk program pendidikan anak kurang mampu.',
                'target_audience' => 'Pelari amatir, komunitas olahraga, keluarga',
                'location' => 'Lapangan Puputan, Denpasar',
                'needType' => 'Dana & logistik',
                'budgetMin' => 25_000_000, 'budgetMax' => 60_000_000,
                'benefits' => 'Jersey sponsor, media coverage, penghargaan CSR',
                'file_path' => 'proposals/maraton-kepedulian-sosial.pdf',
                'status' => 'published',
                'start_date' => '2026-10-20', 'end_date' => '2026-10-20',
            ],
            [
                'idUser' => 1, 'idKategori' => 5,
                'title' => 'Pameran Seni Digital',
                'desctiption' => 'Pameran karya seni digital dari seniman muda Indonesia.',
                'target_audience' => 'Kolektor seni, desainer, mahasiswa seni',
                'location' => 'Museum Nasional, Jakarta',
                'needType' => 'Dana',
                'budgetMin' => 15_000_000, 'budgetMax' => 40_000_000,
                'benefits' => 'Logo pada katalog, undangan VIP pembukaan',
                'file_path' => 'proposals/pameran-seni-digital.pdf',
                'status' => 'draft',
                'start_date' => '2027-02-10', 'end_date' => '2027-02-14',
            ],
        ];

        foreach ($proposals as $p) {
            DB::table('proposal')->insert($p + [
                'createdAt' => now(), 'updateAt' => now(),
            ]);
        }

        // ---------- 1b. CAMPAIGN (dibuat Perusahaan) ----------
        $campaigns = [
            [
                'idUser' => 4, 'idKategori' => 1,
                'title' => 'Kampanye Produk Baru Maju Jaya',
                'description' => 'Memperkenalkan lini produk minuman baru kepada pasar anak muda lewat event musik.',
                'target_audience' => 'Gen Z, penggemar musik',
                'budgetMin' => 40_000_000, 'budgetMax' => 120_000_000,
                'supportType' => 'Dana & sampling produk',
                'estimatedParticipant' => 15000,
                'periodStart' => '2026-10-01', 'periodEnd' => '2026-12-31',
                'file_path' => 'campaigns/kampanye-maju-jaya.pdf',
                'status' => 'active',
            ],
            [
                'idUser' => 5, 'idKategori' => 2,
                'title' => 'Program CSR Literasi Digital',
                'description' => 'Mendukung edukasi literasi digital untuk pelajar di daerah terpencil.',
                'target_audience' => 'Pelajar SMA, guru',
                'budgetMin' => 20_000_000, 'budgetMax' => 50_000_000,
                'supportType' => 'Dana & mentor',
                'estimatedParticipant' => 5000,
                'periodStart' => '2026-09-15', 'periodEnd' => '2027-03-31',
                'file_path' => 'campaigns/csr-literasi-digital.pdf',
                'status' => 'active',
            ],
            [
                'idUser' => 6, 'idKategori' => 3,
                'title' => 'Sponsorship Event Olahraga Elektronik',
                'description' => 'Mempromosikan gadget terbaru lewat turnamen olahraga komunitas.',
                'target_audience' => 'Remaja, dewasa muda penggemar olahraga',
                'budgetMin' => 30_000_000, 'budgetMax' => 90_000_000,
                'supportType' => 'Produk & dana',
                'estimatedParticipant' => 8000,
                'periodStart' => '2026-11-01', 'periodEnd' => '2027-01-31',
                'file_path' => 'campaigns/sponsorship-olahraga.pdf',
                'status' => 'draft',
            ],
        ];

        foreach ($campaigns as $c) {
            DB::table('campaign')->insert($c + [
                'createdAt' => now(), 'updateAt' => now(),
            ]);
        }

        // ---------- 2. MATCHES (kecocokan proposal ↔ campaign) ----------
        $matches = [
            ['idProposal' => 1, 'idCampaign' => 1, 'score' => 92, 'reason' => 'Target audiens dan jenis event sangat sesuai dengan kampanye produk baru.'],
            ['idProposal' => 2, 'idCampaign' => 2, 'score' => 85, 'reason' => 'Tema pendidikan teknologi sejalan dengan program CSR literasi digital.'],
            ['idProposal' => 3, 'idCampaign' => 3, 'score' => 78, 'reason' => 'Event olahraga cocok dengan sponsorship gadget olahraga.'],
        ];

        foreach ($matches as $m) {
            DB::table('matches')->insert($m + [
                'input_hash' => hash('sha256', $m['idProposal'].'-'.$m['idCampaign']),
                'generateAt' => now(),
                'notifiedAt' => now(),
            ]);
        }

        // ---------- 3. TRACKING PIPELINE ----------
        // Sesuai CHECK constraint: idProposal + company_user_id unik.
        $trackings = [
            ['idProposal' => 1, 'company_user_id' => 4, 'idCampaign' => 1, 'stage' => 'negotiating', 'initiatedBy' => 'Event_Organizer'],
            ['idProposal' => 2, 'company_user_id' => 5, 'idCampaign' => 2, 'stage' => 'viewed', 'initiatedBy' => 'Perusahaan'],
            ['idProposal' => 3, 'company_user_id' => 6, 'idCampaign' => 3, 'stage' => 'deal', 'initiatedBy' => 'Event_Organizer'],
            ['idProposal' => 1, 'company_user_id' => 5, 'idCampaign' => null, 'stage' => 'sent', 'initiatedBy' => 'Event_Organizer'],
        ];

        foreach ($trackings as $t) {
            DB::table('trackingpipeline')->insert($t + [
                'agreementPath' => $t['stage'] === 'deal' ? 'agreements/deal-'.$t['idProposal'].'.pdf' : null,
                'createdAt' => now(), 'updatedAt' => now(),
            ]);
        }

        // ---------- 4. OFFER (penawaran dalam pipeline) ----------
        // parent_offer_id = NULL berarti "penawaran awal"; diisi idOffer
        // untuk penawaran balasan (counter offer).
        $offers = [
            [
                'idTracking' => 1, 'from_user_id' => 4, 'parent_offer_id' => null,
                'support_type' => 'Dana', 'amount' => 75_000_000,
                'description' => 'Penawaran dana sponsorship untuk panggung utama.',
                'benefits' => 'Logo di panggung utama & 10 booth',
                'status' => 'counter', 'rejectReason' => null,
            ],
            [
                'idTracking' => 1, 'from_user_id' => 1, 'parent_offer_id' => 1,
                'support_type' => 'Dana', 'amount' => 90_000_000,
                'description' => 'Penawaran balik dengan paket benefit tambahan.',
                'benefits' => 'Logo panggung utama, booth, dan publikasi media sosial',
                'status' => 'pending', 'rejectReason' => null,
            ],
            [
                'idTracking' => 3, 'from_user_id' => 6, 'parent_offer_id' => null,
                'support_type' => 'Produk & dana', 'amount' => 50_000_000,
                'description' => 'Sponsorship berupa produk gadget dan dana tunai.',
                'benefits' => 'Stand produk & jersey peserta',
                'status' => 'accepted', 'rejectReason' => null,
            ],
        ];

        foreach ($offers as $o) {
            DB::table('offer')->insert($o + ['createdAt' => now(), 'updateAt' => now()]);
        }

        // ---------- 5. RIVIEWS (ulasan antar pihak) ----------
        DB::table('riviews')->insert([
            [
                'idTracking' => 3, 'from_user_id' => 3, 'to_user_id' => 6,
                'rating' => 5, 'comment' => 'Kerja sama sangat profesional, komunikasi lancar.',
                'createdAt' => now(), 'updateAt' => now(),
            ],
            [
                'idTracking' => 1, 'from_user_id' => 1, 'to_user_id' => 4,
                'rating' => 4, 'comment' => 'Responsif, meski ada sedikit keterlambatan konfirmasi.',
                'createdAt' => now(), 'updateAt' => now(),
            ],
        ]);

        // ---------- 6. SUBSCRIPTION PLANS & SUBSCRIPTION ----------
        DB::table('subscriptionplans')->insert([
            ['name' => 'Gratis', 'price' => 0, 'targetRole' => 'Event Organizer', 'durationDays' => 30, 'isActive' => 1],
            ['name' => 'Premium EO', 'price' => 149_000, 'targetRole' => 'Event Organizer', 'durationDays' => 30, 'isActive' => 1],
            ['name' => 'Gratis', 'price' => 0, 'targetRole' => 'Perusahaan', 'durationDays' => 30, 'isActive' => 1],
            ['name' => 'Premium Perusahaan', 'price' => 299_000, 'targetRole' => 'Perusahaan', 'durationDays' => 30, 'isActive' => 1],
        ]);

        DB::table('subscription')->insert([
            [
                'idUser' => 5, 'idSubsPlans' => 4,
                'starts' => now()->subDays(10), 'ends' => now()->addDays(20),
                'status' => 'active', 'createdAt' => now(), 'updateAt' => now(),
            ],
            [
                'idUser' => 6, 'idSubsPlans' => 4,
                'starts' => now()->subDays(40), 'ends' => now()->subDays(10),
                'status' => 'expired', 'createdAt' => now(), 'updateAt' => now(),
            ],
        ]);

        // ---------- 7. TRANSAKSI (pembayaran langganan) ----------
        DB::table('transaksi')->insert([
            [
                'idSubscription' => 1, 'idOrder' => 'ORD-2026-0001', 'provider' => 'midtrans',
                'provider_ref' => 'MID-REF-0001', 'amount' => 299_000, 'status' => 'paid',
                'paidAt' => now()->subDays(10), 'createdAt' => now(), 'updateAt' => now(),
            ],
            [
                'idSubscription' => 2, 'idOrder' => 'ORD-2026-0002', 'provider' => 'midtrans',
                'provider_ref' => 'MID-REF-0002', 'amount' => 299_000, 'status' => 'expired',
                'paidAt' => now()->subDays(40), 'createdAt' => now(), 'updateAt' => now(),
            ],
        ]);
    }
}
