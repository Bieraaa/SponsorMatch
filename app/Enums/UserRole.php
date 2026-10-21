<?php

namespace App\Enums;

/**
 * Peran pengguna SponsorMatch.
 *
 * Ada dua peran, sesuai alur "Role Pick" saat pendaftaran:
 *   - EventOrganizer : pihak yang punya event dan mencari sponsor
 *   - Perusahaan     : pihak yang memberi sponsorship
 *
 * Nilai yang disimpan di database sengaja PENDEK ('eo', 'sponsor'):
 *   - aman dipakai sebagai kunci di kode & query
 *   - tidak ada spasi/kapitalisasi yang rawan salah ketik
 *
 * Sedangkan LABEL (untuk tampilan ke pengguna) memakai istilah Indonesia
 * sesuai desain Figma: "Event Organizer" dan "Perusahaan".
 *
 * Karena kolom `users.role` berupa ENUM('eo','sponsor'), nilai asing
 * otomatis ditolak database — mencegah user tanpa peran yang valid.
 */
enum UserRole: string
{
    case EventOrganizer = 'eo';
    case Perusahaan = 'sponsor';

    /**
     * Label untuk ditampilkan ke pengguna (sesuai Figma).
     */
    public function label(): string
    {
        return match ($this) {
            self::EventOrganizer => 'Event Organizer',
            self::Perusahaan => 'Perusahaan',
        };
    }

    /**
     * Nilai-nilai yang sah, berguna untuk aturan validasi.
     *
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
