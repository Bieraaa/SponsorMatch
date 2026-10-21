<?php

namespace Database\Seeders;

use App\Models\CompanyProfile;
use App\Models\EoProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Data contoh akun: beberapa Event Organizer dan beberapa Perusahaan.
 *
 * PENTING: password di-hash lewat Hash::make (bcrypt), sehingga akun
 * contoh TIDAK menyimpan password dalam bentuk terbaca. Password semua
 * akun demo di bawah adalah "password".
 *
 * NPWP/NIB perusahaan contoh dienkripsi otomatis oleh model
 * CompanyProfile (cast `encrypted`) + hash HMAC-nya dihitung di event
 * `saving`. Jadi seeder ini tetap aman meski nilainya ditulis apa adanya
 * di kode — yang tersimpan di database adalah ciphertext.
 */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        // ---------------- Event Organizer ----------------
        $eos = [
            [
                'name' => 'Budi Santoso',
                'email' => 'budi@eosnusantara.id',
                'nomor_telepon' => '081234500001',
                'jabatan' => 'Ketua Umum',
                'eo_name' => 'EO Seni Nusantara',
                'eo_field' => 'Seni & Budaya',
            ],
            [
                'name' => 'Rina Wijaya',
                'email' => 'rina@techmeet.id',
                'nomor_telepon' => '081234500002',
                'jabatan' => 'Event Director',
                'eo_name' => 'TechMeet Indonesia',
                'eo_field' => 'Teknologi',
            ],
            [
                'name' => 'Agus Pratama',
                'email' => 'agus@sportfest.id',
                'nomor_telepon' => '081234500003',
                'jabatan' => 'Koordinator Acara',
                'eo_name' => 'SportFest Community',
                'eo_field' => 'Olahraga',
            ],
        ];

        foreach ($eos as $e) {
            $user = User::create([
                'name' => $e['name'],
                'email' => $e['email'],
                'role' => 'eo',
                'nomor_telepon' => $e['nomor_telepon'],
                'jabatan' => $e['jabatan'],
                'status_subscription' => 'Reguler',
                'isActive' => true,
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]);

            EoProfile::create([
                'idUser' => $user->id,
                'eo_name' => $e['eo_name'],
                'eo_field' => $e['eo_field'],
                'eo_email' => $e['email'],
            ]);
        }

        // ---------------- Perusahaan ----------------
        $companies = [
            [
                'name' => 'Siti Aminah',
                'email' => 'siti@majujaya.co.id',
                'nomor_telepon' => '082134500001',
                'jabatan' => 'Marketing Manager',
                'companyName' => 'PT Maju Jaya',
                'npwp' => '01.234.567.8-901.000',
                'nib' => '1234567890123',
                'industry' => 'Consumer Goods',
                'campaign_Target' => 'Gen Z, Milenial muda',
                'rating' => 4.5,
            ],
            [
                'name' => 'Dewi Lestari',
                'email' => 'dewi@nusantarabank.co.id',
                'nomor_telepon' => '082134500002',
                'jabatan' => 'CSR Lead',
                'companyName' => 'Bank Nusantara',
                'npwp' => '02.345.678.9-012.000',
                'nib' => '2234567890123',
                'industry' => 'Perbankan',
                'campaign_Target' => 'UMKM, Komunitas lokal',
                'rating' => 4.8,
            ],
            [
                'name' => 'Hendra Gunawan',
                'email' => 'hendra@teknoelektrik.co.id',
                'nomor_telepon' => '082134500003',
                'jabatan' => 'Brand Partnership',
                'companyName' => 'PT Tekno Elektrik',
                'npwp' => '03.456.789.0-123.000',
                'nib' => '3234567890123',
                'industry' => 'Elektronik',
                'campaign_Target' => 'Mahasiswa, Startup',
                'rating' => 4.2,
            ],
        ];

        foreach ($companies as $c) {
            $user = User::create([
                'name' => $c['name'],
                'email' => $c['email'],
                'role' => 'sponsor',
                'nomor_telepon' => $c['nomor_telepon'],
                'jabatan' => $c['jabatan'],
                'status_subscription' => 'Subscription',
                'rating' => $c['rating'],
                'isActive' => true,
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]);

            CompanyProfile::create([
                'idUser' => $user->id,
                'companyName' => $c['companyName'],
                'npwp' => $c['npwp'],
                'nib' => $c['nib'],
                'industry' => $c['industry'],
                'campaign_Target' => $c['campaign_Target'],
            ]);
        }
    }
}
