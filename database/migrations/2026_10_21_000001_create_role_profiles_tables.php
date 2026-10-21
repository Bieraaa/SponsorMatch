<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel profil peran — versi PORTABEL.
 *
 * Di database MySQL milikmu, `eo_profiles` dan `company_profiles` sudah ada
 * (hasil impor DDL ERD), sehingga migrasi ini TIDAK melakukan apa pun
 * (`hasTable` bernilai true → dilewati).
 *
 * Namun database uji SQLite (:memory:) dibuat dari nol lewat `migrate`,
 * jadi tabel-tabel ini belum ada. Migrasi ini membuatnya dengan API
 * `Schema` Laravel yang portabel, sehingga:
 *   - test bisa benar-benar membuat & membaca profil peran
 *   - struktur kolom tetap sama persis dengan rancangan ERD
 *
 * Catatan: definisi di sini sengaja mengikuti struktur MySQL yang sudah ada
 * (nama kolom, nullable, unik) agar perilaku di SQLite dan MySQL sama.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ---------- eo_profiles ----------
        if (! Schema::hasTable('eo_profiles')) {
            Schema::create('eo_profiles', function (Blueprint $table) {
                $table->id('idEventOrganizer');
                $table->unsignedBigInteger('idUser')->unique();
                $table->string('eo_name', 150);
                $table->string('eo_field', 100)->nullable();
                $table->string('eo_email', 255)->nullable();

                $table->foreign('idUser')->references('id')->on('users')->cascadeOnDelete();
            });
        }

        // ---------- company_profiles ----------
        if (! Schema::hasTable('company_profiles')) {
            Schema::create('company_profiles', function (Blueprint $table) {
                $table->id('idCompany');
                $table->unsignedBigInteger('idUser')->unique();
                $table->string('companyName', 100);
                // nullable: perusahaan baru belum tentu punya NIB/NPWP.
                // Nilai disimpan terenkripsi oleh model (cast `encrypted`).
                $table->text('nib')->nullable();
                $table->char('nib_hash', 64)->nullable()->unique();
                $table->text('npwp')->nullable();
                $table->char('npwp_hash', 64)->nullable()->unique();
                $table->string('industry', 100)->nullable();
                $table->text('campaign_Target')->nullable();

                $table->foreign('idUser')->references('id')->on('users')->cascadeOnDelete();
            });
        }

        // ---------- loginattemp (jejak audit login) ----------
        // Dipakai oleh listener RecordSuccessfulLogin / RecordFailedLogin.
        // Kolom mengikuti DDL ERD (termasuk nama `succes`).
        if (! Schema::hasTable('loginattemp')) {
            Schema::create('loginattemp', function (Blueprint $table) {
                $table->id('idAttemp');
                $table->unsignedBigInteger('idUser')->nullable();
                $table->string('email', 255);
                $table->string('ip_adrress', 45);
                $table->boolean('succes');
                $table->timestamp('attemptedAt')->useCurrent();

                $table->foreign('idUser')->references('id')->on('users')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('company_profiles');
        Schema::dropIfExists('eo_profiles');
    }
};
