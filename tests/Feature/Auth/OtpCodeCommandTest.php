<?php

namespace Tests\Feature\Auth;

use App\Enums\OtpVerifyStatus;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

/**
 * Tes untuk perintah CLI `php artisan otp:code`.
 *
 * Perintah ini adalah CAGINAN DEV: karena kode OTP disimpan sebagai
 * HMAC-SHA256 (bukan teks polos), kode polos tidak bisa dibaca ulang dari
 * database. Perintah ini membuat kode baru lalu menampilkannya di terminal
 * supaya alur OTP bisa diuji tanpa SMTP.
 */
class OtpCodeCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_perintah_menampilkan_kode_yang_bisa_langsung_dipakai(): void
    {
        $user = User::factory()->create(['email' => 'uji.otp@contoh.id']);

        $exitCode = Artisan::call('otp:code', ['email' => 'uji.otp@contoh.id']);
        $output = Artisan::output();

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('KODE OTP SIAP', $output);

        // Ambil 6 digit kode dari output perintah.
        $this->assertSame(1, preg_match('/Kode\s+:\s+(\d{6})/', $output, $matches));

        // Kode yang ditampilkan HARUS benar-benar valid untuk user tersebut.
        $result = app(OtpService::class)->verify($user, $matches[1]);

        $this->assertSame(OtpVerifyStatus::Success, $result->status);
    }

    public function test_perintah_mengganti_kode_lama(): void
    {
        $user = User::factory()->create(['email' => 'uji.ganti@contoh.id']);

        Artisan::call('otp:code', ['email' => 'uji.ganti@contoh.id']);
        $kodePertama = $this->ambilKode(Artisan::output());

        Artisan::call('otp:code', ['email' => 'uji.ganti@contoh.id']);
        $kodeKedua = $this->ambilKode(Artisan::output());

        $this->assertNotSame($kodePertama, $kodeKedua);

        // Kode lama harus tidak berlaku lagi (sudah dihapus saat digantikan).
        $hasilLama = app(OtpService::class)->verify($user, $kodePertama);
        $this->assertSame(OtpVerifyStatus::Invalid, $hasilLama->status);

        // Kode baru masih berlaku.
        $hasilBaru = app(OtpService::class)->verify($user, $kodeKedua);
        $this->assertSame(OtpVerifyStatus::Success, $hasilBaru->status);
    }

    public function test_gagal_jika_email_tidak_ditemukan(): void
    {
        $exitCode = Artisan::call('otp:code', ['email' => 'tidak.ada@contoh.id']);

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('tidak ditemukan', Artisan::output());
    }

    public function test_gagal_jika_purpose_tidak_valid(): void
    {
        $user = User::factory()->create(['email' => 'uji.purpose@contoh.id']);

        $exitCode = Artisan::call('otp:code', [
            'email' => 'uji.purpose@contoh.id',
            '--purpose' => 'ngawur',
        ]);

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('Purpose tidak dikenal', Artisan::output());
        $this->assertDatabaseMissing('otp_codes', ['idUser' => $user->id]);
    }

    /**
     * Ambil kode 6 digit dari output perintah.
     */
    protected function ambilKode(string $output): string
    {
        $this->assertSame(1, preg_match('/Kode\s+:\s+(\d{6})/', $output, $matches));

        return $matches[1];
    }
}
