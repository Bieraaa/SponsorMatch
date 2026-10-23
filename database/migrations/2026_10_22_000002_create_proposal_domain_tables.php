<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel domain proposal sesuai rancangan ERD SponsorMatch.
 *
 * CATATAN PENTING
 * ---------------
 * Skema 33 tabel ERD (proposal, trackingpipeline, offer, campaign, dst.)
 * dimuat ke MySQL dari hasil export ERD di luar Laravel, sehingga TIDAK
 * punya file migration. Akibatnya tabel-tabel tersebut tidak ikut dibuat
 * di database SQLite (dipakai `php artisan test`).
 *
 * Migration ini menutup celah itu TANPA mengubah skema MySQL yang sudah
 * ada: setiap tabel hanya dibuat kalau BELUM ADA (`Schema::hasTable`).
 * Jadi:
 *   - MySQL  -> tabel sudah ada dari ERD, langkah ini dilewati (aman).
 *   - SQLite -> tabel dibuat ulang, sehingga fitur "Proposal Saya" bisa
 *               diuji otomatis lewat `php artisan test`.
 *
 * Definisi kolom sengaja disalin apa adanya dari skema MySQL (nama kolom
 * campur camelCase seperti `idProposal`, `createdAt`, `desctiption`)
 * supaya model Eloquent dan query yang sama persis bekerja di kedua
 * database tanpa adapter.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ---------------------------------------------------------------
        // kategori — master kategori event/proposal
        // ---------------------------------------------------------------
        if (! Schema::hasTable('kategori')) {
            Schema::create('kategori', function (Blueprint $table) {
                $table->increments('idKategori');
                $table->string('name')->unique();
                $table->string('deskripsiKategori');
            });
        }

        // ---------------------------------------------------------------
        // campaign — event milik Event Organizer
        // ---------------------------------------------------------------
        if (! Schema::hasTable('campaign')) {
            Schema::create('campaign', function (Blueprint $table) {
                $table->increments('idCampaign');
                $table->unsignedBigInteger('idUser');
                $table->unsignedInteger('idKategori');
                $table->string('title');
                $table->text('description');
                $table->text('target_audience');
                $table->integer('budgetMin');
                $table->integer('budgetMax');
                $table->string('supportType');
                $table->integer('estimatedParticipant');
                $table->date('periodStart');
                $table->date('periodEnd');
                $table->string('file_path');
                $table->enum('status', ['draft', 'active', 'close'])->default('draft');
                $table->timestamp('deleteAt')->nullable();
                $table->timestamp('createdAt')->useCurrent();
                $table->timestamp('updateAt')->useCurrent();

                $table->foreign('idUser')->references('id')->on('users')->onDelete('restrict');
                $table->foreign('idKategori')->references('idKategori')->on('kategori')->onDelete('restrict');
            });
        }

        // ---------------------------------------------------------------
        // proposal — proposal sponsorship yang dibuat Event Organizer
        // ---------------------------------------------------------------
        if (! Schema::hasTable('proposal')) {
            Schema::create('proposal', function (Blueprint $table) {
                $table->increments('idProposal');
                $table->unsignedBigInteger('idUser');
                $table->unsignedInteger('idKategori');
                $table->string('title');
                $table->text('desctiption');
                $table->text('target_audience');
                $table->string('location');
                $table->string('needType');
                $table->integer('budgetMin');
                $table->integer('budgetMax');
                $table->text('benefits');
                $table->string('file_path');
                $table->enum('status', ['draft', 'published'])->default('draft');
                $table->date('start_date');
                $table->date('end_date');
                $table->timestamp('deleteAt')->nullable();
                $table->timestamp('createdAt')->useCurrent();
                $table->timestamp('updateAt')->useCurrent();

                $table->foreign('idUser')->references('id')->on('users')->onDelete('restrict');
                $table->foreign('idKategori')->references('idKategori')->on('kategori')->onDelete('restrict');
            });
        }

        // ---------------------------------------------------------------
        // trackingpipeline — jejak proposal yg dikirim ke sponsor
        // ---------------------------------------------------------------
        if (! Schema::hasTable('trackingpipeline')) {
            Schema::create('trackingpipeline', function (Blueprint $table) {
                $table->increments('idTracking');
                $table->unsignedInteger('idProposal');
                $table->unsignedBigInteger('company_user_id');
                $table->unsignedInteger('idCampaign')->nullable();
                $table->enum('stage', [
                    'sent', 'viewed', 'negotiating', 'deal',
                    'waiting_report', 'done', 'rejected',
                ])->default('sent');
                $table->enum('initiatedBy', ['Event_Organizer', 'Perusahaan'])->default('Event_Organizer');
                $table->string('agreementPath')->nullable();
                $table->timestamp('createdAt')->useCurrent();
                $table->timestamp('updatedAt')->useCurrent();

                $table->unique(['idProposal', 'company_user_id']);
                $table->foreign('idProposal')->references('idProposal')->on('proposal')->onDelete('restrict');
                $table->foreign('company_user_id')->references('id')->on('users')->onDelete('restrict');
                $table->foreign('idCampaign')->references('idCampaign')->on('campaign')->onDelete('set null');
            });
        }

        // ---------------------------------------------------------------
        // offer — penawaran sponsor terhadap proposal
        // ---------------------------------------------------------------
        if (! Schema::hasTable('offer')) {
            Schema::create('offer', function (Blueprint $table) {
                $table->increments('idOffer');
                $table->unsignedInteger('idTracking');
                $table->unsignedBigInteger('from_user_id');
                $table->unsignedInteger('parent_offer_id')->nullable();
                $table->string('support_type');
                $table->integer('amount')->nullable();
                $table->text('description')->nullable();
                $table->text('benefits')->nullable();
                $table->enum('status', ['pending', 'accepted', 'rejected', 'counter'])->default('pending');
                $table->text('rejectReason')->nullable();
                $table->timestamp('createdAt')->useCurrent();
                $table->timestamp('updateAt')->useCurrent();

                $table->foreign('idTracking')->references('idTracking')->on('trackingpipeline')->onDelete('cascade');
                $table->foreign('from_user_id')->references('id')->on('users')->onDelete('restrict');
                $table->foreign('parent_offer_id')->references('idOffer')->on('offer')->onDelete('set null');
            });
        }

        // ---------------------------------------------------------------
        // proposalviewlogs — sponsor membuka proposal
        // ---------------------------------------------------------------
        if (! Schema::hasTable('proposalviewlogs')) {
            Schema::create('proposalviewlogs', function (Blueprint $table) {
                $table->increments('idProposLogs');
                $table->unsignedInteger('idProposal');
                $table->unsignedBigInteger('idViewer');
                $table->timestamp('viewedAt')->useCurrent();

                $table->foreign('idProposal')->references('idProposal')->on('proposal')->onDelete('cascade');
                $table->foreign('idViewer')->references('id')->on('users')->onDelete('cascade');
            });
        }

        // ---------------------------------------------------------------
        // follow_up_logs — riwayat follow-up yang dikirim EO
        // ---------------------------------------------------------------
        if (! Schema::hasTable('follow_up_logs')) {
            Schema::create('follow_up_logs', function (Blueprint $table) {
                $table->increments('idFollowUpLogs');
                $table->unsignedInteger('idTracking');
                $table->timestamp('sentAt')->useCurrent();

                $table->foreign('idTracking')->references('idTracking')->on('trackingpipeline')->onDelete('cascade');
            });
        }

        // ---------------------------------------------------------------
        // event_reports — laporan realization event sponsor
        // ---------------------------------------------------------------
        if (! Schema::hasTable('event_reports')) {
            Schema::create('event_reports', function (Blueprint $table) {
                $table->increments('idreports');
                $table->unsignedInteger('idTracking');
                $table->text('decription')->nullable();
                $table->boolean('goal_achieved')->nullable();
                $table->timestamp('submittedAt')->useCurrent();

                $table->unique('idTracking');
                $table->foreign('idTracking')->references('idTracking')->on('trackingpipeline')->onDelete('cascade');
            });
        }
    }

    /**
     * TIDAK dropping tabel apa pun.
     *
     * Tabel-tabel di atas sudah ada (dan berisi data) di MySQL dari hasil
     * export ERD. `migrate:rollback` tidak boleh menghapus data tersebut,
     * jadi `down()` sengaja dibuat tidak menghapus apa pun. Tabel yang
     * dibuat migration ini (SQLite) ikut hilang saat `migrate:fresh`
     * (drop semua tabel) lalu dibuat ulang oleh `up()`.
     */
    public function down(): void
    {
        // no-op: lihat penjelasan di atas.
    }
};
