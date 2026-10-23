<?php

namespace App\Enums;

/**
 * Status proposal yang dipakai halaman "Proposal Saya".
 *
 * ERD hanya menyimpan `proposal.status` (draft/published) dan
 * `trackingpipeline.stage` (sent/viewed/negotiating/deal/...). Halaman
 * proposal butuh status yang lebih ramah pengguna, jadi status tersebut
 * DITURUNKAN dari data nyata di tabel (lihat ProposalService::statusSql()).
 *
 * Urutan prioritas saat menurunkan status:
 *   Draft → Siap Dikirim → Disetujui → Menunggu Respons → Ditolak → Terkirim
 */
enum ProposalStatus: string
{
    case Draft = 'draft';
    case Ready = 'ready';
    case Sent = 'sent';
    case Waiting = 'waiting';
    case Approved = 'approved';
    case Rejected = 'rejected';

    /**
     * Label yang ditampilkan pada badge.
     */
    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Ready => 'Siap Dikirim',
            self::Sent => 'Terkirim',
            self::Waiting => 'Menunggu Respons',
            self::Approved => 'Disetujui',
            self::Rejected => 'Ditolak',
        };
    }

    /**
     * Daftar tab status (tanpa "Semua").
     *
     * @return array<int, self>
     */
    public static function tabs(): array
    {
        return [self::Draft, self::Ready, self::Sent, self::Waiting, self::Approved, self::Rejected];
    }

    /**
     * Cocokkan input user dengan status yang valid.
     */
    public static function tryFromRequest(?string $value): ?self
    {
        if ($value === null || $value === '' || $value === 'all') {
            return null;
        }

        return self::tryFrom($value);
    }
}
