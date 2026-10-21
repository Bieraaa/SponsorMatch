<?php

/**
 * ============================================================
 *  cek.php — Alat bantu untuk MELIHAT ISI DATABASE
 * ============================================================
 *
 * Gunanya: memastikan data sensitif benar-benar aman di database.
 * Jalankan dari folder proyek (D:\SponsorMatch):
 *
 *     php cek.php
 *
 * Script ini TIDAK mengubah apa pun — hanya membaca dan menampilkan.
 * Hapus file ini kalau sudah tidak diperlukan.
 */

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo str_repeat('=', 70)."\n";
echo "  CEK KEAMANAN DATA — SponsorMatch\n";
echo str_repeat('=', 70)."\n\n";

// ----------------------------------------------------------------
// 1. PASSWORD — harus ter-hash (berawalan $2y$)
// ----------------------------------------------------------------
echo "1) TABEL `users` — PASSWORD\n";
echo str_repeat('-', 70)."\n";
echo sprintf("%-4s %-30s %-10s %s\n", 'id', 'email', 'role', 'password di database');

foreach (DB::table('users')->orderBy('id')->get() as $u) {
    $isHashed = str_starts_with($u->password, '$2y$')
        || str_starts_with($u->password, '$argon2id$');

    echo sprintf(
        "%-4d %-30s %-10s %s %s\n",
        $u->id,
        $u->email,
        $u->role,
        substr($u->password, 0, 30).'...',
        $isHashed ? '[HASH ✓]' : '[PLAINTEXT ✗ BAHAYA]'
    );
}
echo "\n";
echo "Cara membaca: awalan \$2y\$ artinya password sudah di-hash bcrypt.\n";
echo "Kalau yang muncul teks asli (mis. \"password\"), berarti BELUM di-hash.\n\n";

// ----------------------------------------------------------------
// 2. NPWP & NIB — harus terenkripsi (bukan angka biasa)
// ----------------------------------------------------------------
echo "2) TABEL `company_profiles` — NPWP & NIB\n";
echo str_repeat('-', 70)."\n";

$rows = DB::table('company_profiles')->get();

if ($rows->isEmpty()) {
    echo "(belum ada data perusahaan)\n\n";
} else {
    foreach ($rows as $r) {
        // Nilai terenkripsi = teks base64 panjang, BUKAN deretan angka.
        $npwpEnc = $r->npwp === null
            || ! preg_match('/^[0-9.\-]+$/', (string) $r->npwp);
        $nibEnc = $r->nib === null
            || ! preg_match('/^[0-9.\-]+$/', (string) $r->nib);

        echo "Perusahaan : {$r->companyName}  (idUser={$r->idUser})\n";
        echo '  npwp di DB : '.substr((string) $r->npwp, 0, 55)."...\n";
        echo '               -> '.($npwpEnc ? 'TERENKRIPSI ✓' : 'ANGKA ASLI ✗ BAHAYA')."\n";
        echo '  npwp_hash  : '.substr((string) $r->npwp_hash, 0, 55)."...\n";
        echo '  nib  di DB : '.substr((string) $r->nib, 0, 55)."...\n";
        echo '               -> '.($nibEnc ? 'TERENKRIPSI ✓' : 'ANGKA ASLI ✗ BAHAYA')."\n";
        echo '  nib_hash   : '.substr((string) $r->nib_hash, 0, 55)."...\n\n";
    }
}
echo "Cara membaca: kalau yang muncul eyJpdiI6... berarti terenkripsi.\n";
echo "Kalau yang muncul 1234567890123456 berarti masih angka asli (bahaya).\n\n";

// ----------------------------------------------------------------
// 3. RINGKASAN
// ----------------------------------------------------------------
echo "3) RINGKASAN\n";
echo str_repeat('-', 70)."\n";
echo 'Jumlah user            : '.DB::table('users')->count()."\n";
echo 'Jumlah profil EO       : '.DB::table('eo_profiles')->count()."\n";
echo 'Jumlah profil perusahaan: '.DB::table('company_profiles')->count()."\n";

$plain = DB::table('users')
    ->where('password', 'not like', '$2y$%')
    ->where('password', 'not like', '$argon2id$%')
    ->count();

echo "\nPassword yang BELUM di-hash: {$plain} ".($plain === 0 ? "✓ (aman)" : '✗ (perlu diperbaiki)')."\n";
echo "\nSelesai.\n";
