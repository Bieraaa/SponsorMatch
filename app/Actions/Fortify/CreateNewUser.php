<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Enums\UserRole;
use App\Models\CompanyProfile;
use App\Models\EoProfile;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

/**
 * Membuat akun baru saat pendaftaran (Blok A).
 *
 * ALUR: pengguna memilih peran DULU (layar "Role Pick"), lalu mengisi
 * form yang BERBEDA per peran:
 *
 *   Event Organizer (/register/eo)
 *     nama, email, password, konfirmasi password, jabatan,
 *     nomor_telepon, nama_event_organizer
 *
 *   Perusahaan (/register/perusahaan)
 *     nama, email, nomor_telepon, jabatan, password, konfirmasi password,
 *     nama_perusahaan, npwp, nib
 *
 * KENAPA FORM DIPISAH: field yang diminta berbeda, jadi validasinya pun
 * berbeda. Memaksa satu form untuk dua peran hanya menghasilkan field
 * yang tidak relevan dan validasi yang membingungkan.
 *
 * PENYIMPANAN (2 tabel sekaligus):
 *   `users`                    → data akun (nama, email, password, role, jabatan, telepon)
 *   `eo_profiles` ATAU
 *   `company_profiles`         → data khusus peran (nama EO / nama perusahaan, npwp, nib)
 *
 * Keduanya dibungkus DB::transaction: kalau pembuatan profil gagal,
 * akun di `users` ikut dibatalkan. Jadi tidak akan pernah ada
 * "akun tanpa profil" yang setengah jadi.
 */
class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        $role = $input['role'] ?? null;

        Validator::make($input, [
            ...$this->profileRules(),
            'role' => ['required', 'string', Rule::in(UserRole::values())],
            'password' => $this->passwordRules(),
            // Jabatan & nomor telepon diminta di KEDUA form (sesuai Figma).
            'jabatan' => ['required', 'string', 'max:100'],
            'nomor_telepon' => ['required', 'string', 'max:30'],

            // --- Field khusus Event Organizer ---
            'nama_event_organizer' => [
                Rule::requiredIf($role === UserRole::EventOrganizer->value),
                'nullable', 'string', 'max:150',
            ],
            'eo_field' => ['nullable', 'string', 'max:100'],

            // --- Field khusus Perusahaan ---
            'nama_perusahaan' => [
                Rule::requiredIf($role === UserRole::Perusahaan->value),
                'nullable', 'string', 'max:100',
            ],
            // NPWP: 15–16 digit (format lama 15, format baru 16), boleh
            // memakai titik/hubung. Cek duplikat lewat hash, bukan nilai asli.
            'npwp' => [
                Rule::requiredIf($role === UserRole::Perusahaan->value),
                'nullable', 'string', 'max:32',
                function (string $attribute, mixed $value, \Closure $fail) {
                    if ($value === null || $value === '') {
                        return;
                    }
                    if (! preg_match('/^[0-9.\-]{15,20}$/', (string) $value)) {
                        $fail('Format NPWP tidak valid (15–16 digit angka).');
                    }
                    if (CompanyProfile::whereNpwp((string) $value)->exists()) {
                        $fail('NPWP ini sudah terdaftar.');
                    }
                },
            ],
            // NIB: opsional — perusahaan baru mungkin belum punya.
            'nib' => [
                'nullable', 'string', 'max:32',
                function (string $attribute, mixed $value, \Closure $fail) {
                    if ($value === null || $value === '') {
                        return;
                    }
                    if (! preg_match('/^[0-9]{8,20}$/', (string) $value)) {
                        $fail('Format NIB tidak valid (8–20 digit angka).');
                    }
                    if (CompanyProfile::whereNib((string) $value)->exists()) {
                        $fail('NIB ini sudah terdaftar.');
                    }
                },
            ],
        ])->validate();

        return DB::transaction(function () use ($input, $role) {
            $user = User::create([
                'name' => $input['name'],
                'email' => $input['email'],
                'role' => $role,
                'nomor_telepon' => $input['nomor_telepon'],
                'jabatan' => $input['jabatan'],
                'password' => $input['password'],
            ]);

            // Profil sesuai peran — ditulis dalam transaksi yang sama.
            if ($role === UserRole::EventOrganizer->value) {
                EoProfile::create([
                    'idUser' => $user->id,
                    'eo_name' => $input['nama_event_organizer'],
                    'eo_field' => $input['eo_field'] ?? null,
                    'eo_email' => $input['email'],
                ]);
            } else {
                CompanyProfile::create([
                    'idUser' => $user->id,
                    'companyName' => $input['nama_perusahaan'],
                    // Nilai asli → otomatis dienkripsi (cast `encrypted`)
                    // dan hash-nya dihitung oleh accessor di model.
                    'npwp' => $input['npwp'] ?? null,
                    'nib' => $input['nib'] ?? null,
                ]);
            }

            // Blok A: auto-verify saat pendaftaran. SMTP/email belum
            // dikonfigurasi, jadi link verifikasi tidak bisa dikirim.
            // Hapus baris ini saat email verifikasi sudah aktif.
            $user->forceFill(['email_verified_at' => now()])->save();

            return $user;
        });
    }
}
