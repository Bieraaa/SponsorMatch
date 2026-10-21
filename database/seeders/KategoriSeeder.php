<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Kategori event/campaign. Dipakai sebagai acuan `idKategori` di
 * tabel `proposal` dan `campaign`.
 */
class KategoriSeeder extends Seeder
{
    public function run(): void
    {
        $kategori = [
            ['name' => 'Musik & Konser', 'deskripsiKategori' => 'Festival, konser, pertunjukan musik'],
            ['name' => 'Teknologi', 'deskripsiKategori' => 'Hackathon, seminar, konferensi teknologi'],
            ['name' => 'Olahraga', 'deskripsiKategori' => 'Turnamen, maraton, kompetisi olahraga'],
            ['name' => 'Pendidikan', 'deskripsiKategori' => 'Workshop, pelatihan, seminar edukasi'],
            ['name' => 'Seni & Budaya', 'deskripsiKategori' => 'Pameran, teater, festival budaya'],
            ['name' => 'Kesehatan', 'deskripsiKategori' => 'Bakti sosial, kampanye kesehatan'],
        ];

        DB::table('kategori')->insert($kategori);
    }
}
