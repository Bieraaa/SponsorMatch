<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Blok A — menambahkan kolom `role` ke tabel users.
 *
 * Nilai role (dipilih lewat layar "Role Pick" saat mendaftar):
 *   - 'eo'      = Event Organizer (pembuat event & proposal)
 *   - 'sponsor' = Perusahaan/Sponsor (pemasang campaign & penerima proposal)
 *
 * Default 'eo' dipakai agar baris users lama (jika ada) tetap valid.
 * (Sesuai SRS: pengguna aplikasi terbagi atas dua peran tersebut.)
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('eo')->after('email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
