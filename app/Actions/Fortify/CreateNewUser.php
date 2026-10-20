<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

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
        Validator::make($input, [
            ...$this->profileRules(),
            // Blok A: role wajib dipilih saat mendaftar (layar Role Pick):
            // 'eo' = Event Organizer, 'sponsor' = Perusahaan/Sponsor.
            'role' => ['required', 'string', Rule::in(['eo', 'sponsor'])],
            'password' => $this->passwordRules(),
        ])->validate();

        $user = User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'role' => $input['role'],
            'password' => $input['password'],
        ]);

        // Blok A: auto-verify saat pendaftaran. SMTP/email belum dikonfigurasi,
        // jadi link verifikasi tidak bisa dikirim ke inbox. Agar user baru
        // langsung masuk dashboard (tanpa hambatan middleware 'verified'),
        // tandai email sebagai terverifikasi sejak awal. Hapus baris ini
        // saat fitur email verifikasi sudah aktif di produksi.
        $user->forceFill(['email_verified_at' => now()])->save();

        return $user;
    }
}
