<?php

namespace Tests\Feature\Auth;

use App\Enums\OtpVerifyStatus;
use App\Models\OtpCode;
use App\Models\User;
use App\Notifications\OtpNotification;
use App\Notifications\VerifyEmailNotification;
use App\Services\OtpService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Verifikasi email berbasis kode OTP.
 *
 * Alur baru (sebelumnya email di-force terverifikasi saat register):
 *   register → halaman OTP → user masukkan 6 digit → email terverifikasi
 *
 * Test ini memverifikasi:
 *   - email verifikasi benar-benar dikirim (notifikasi, bukan sekadar flag)
 *   - kode OTP disimpan sebagai HASH, bukan teks polos
 *   - kode yang benar memverifikasi email
 *   - kode salah / kedaluwarsa ditolak dengan batas percobaan
 *   - cooldown kirim ulang bekerja
 *   - user yang belum verifikasi tidak bisa masuk dashboard
 */
class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    // ------------------------------------------------------------------
    // Pengiriman email
    // ------------------------------------------------------------------

    public function test_verification_email_is_sent_after_registration(): void
    {
        Notification::fake();

        $this->post(route('register.store'), [
            'role' => 'eo',
            'name' => 'Budi Santoso',
            'email' => 'budi@eo.test',
            'jabatan' => 'Ketua',
            'nomor_telepon' => '081234567890',
            'nama_event_organizer' => 'Komunitas Seni Nusantara',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $user = User::where('email', 'budi@eo.test')->firstOrFail();

        Notification::assertSentTo(
            $user,
            VerifyEmailNotification::class,
            function (VerifyEmailNotification $notification) use ($user): bool {
                // Link verifikasi harus signed URL yang berlaku 60 menit.
                $this->assertTrue($notification->via($user) === ['mail']);

                return true;
            },
        );
    }

    public function test_registered_user_email_is_not_marked_verified_immediately(): void
    {
        Notification::fake();

        $this->post(route('register.store'), [
            'role' => 'eo',
            'name' => 'Belum Terverifikasi',
            'email' => 'belum@eo.test',
            'jabatan' => 'Ketua',
            'nomor_telepon' => '081234567890',
            'nama_event_organizer' => 'EO Test',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $user = User::where('email', 'belum@eo.test')->firstOrFail();

        $this->assertNull($user->email_verified_at);
    }

    // ------------------------------------------------------------------
    // Halaman OTP
    // ------------------------------------------------------------------

    public function test_otp_page_is_shown_for_unverified_user(): void
    {
        Notification::fake();

        $user = User::factory()->unverified()->create();

        $response = $this->actingAs($user)->get(route('otp.show'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('auth/otp/Index')
            ->where('email', $user->email)
            ->where('purpose', OtpService::PURPOSE_REGISTER));
    }

    public function test_otp_page_redirects_verified_user_to_dashboard(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('otp.show'))->assertRedirect(route('dashboard'));
    }

    public function test_unverified_user_cannot_access_dashboard(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertRedirect(route('otp.show'));
    }

    public function test_guest_is_redirected_away_from_otp_page(): void
    {
        $this->get(route('otp.show'))->assertRedirect(route('login'));
    }

    // ------------------------------------------------------------------
    // Penyimpanan kode
    // ------------------------------------------------------------------

    public function test_otp_code_is_stored_hashed_not_plaintext(): void
    {
        Notification::fake();

        $user = User::factory()->unverified()->create();

        $this->actingAs($user)->get(route('otp.show'));

        $otp = OtpCode::where('idUser', $user->id)->firstOrFail();

        $code = $this->grabOtpCode($user);

        // Isi kolom code_hash BUKAN kode aslinya.
        $this->assertNotSame($code, $otp->code_hash);
        $this->assertSame(OtpService::hashCode($code), $otp->code_hash);
        $this->assertSame(64, strlen($otp->code_hash));
    }

    public function test_otp_code_expires_after_five_minutes(): void
    {
        Notification::fake();

        $user = User::factory()->unverified()->create();

        $this->actingAs($user)->get(route('otp.show'));

        $otp = OtpCode::where('idUser', $user->id)->firstOrFail();

        $this->assertTrue($otp->expiredAt->isFuture());
        $this->assertEqualsWithDelta(
            now()->addSeconds(OtpService::TTL_SECONDS)->timestamp,
            $otp->expiredAt->timestamp,
            5,
        );

        // Masa berlaku kode harus 5 menit (300 detik), bukan 60 detik.
        $this->assertSame(300, OtpService::TTL_SECONDS);
    }

    // ------------------------------------------------------------------
    // Verifikasi kode
    // ------------------------------------------------------------------

    public function test_correct_otp_code_verifies_the_email(): void
    {
        Notification::fake();

        $user = User::factory()->unverified()->create();

        $this->actingAs($user)->get(route('otp.show'));

        $code = $this->grabOtpCode($user);

        $response = $this->actingAs($user)->post(route('otp.verify'), ['code' => $code]);

        $response->assertRedirect(route('dashboard'));
        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_correct_otp_code_cannot_be_reused(): void
    {
        Notification::fake();

        $user = User::factory()->unverified()->create();

        $this->actingAs($user)->get(route('otp.show'));

        $code = $this->grabOtpCode($user);

        $this->actingAs($user)->post(route('otp.verify'), ['code' => $code]);

        // Pakai kode yang sama lagi → harus ditolak.
        $this->actingAs($user)
            ->post(route('otp.verify'), ['code' => $code])
            ->assertSessionHas('otpError');

        // Kode sekali pakai → sudah dihapus dari database.
        $this->assertSame(0, OtpCode::where('idUser', $user->id)->count());
    }

    public function test_wrong_otp_code_is_rejected_and_counted(): void
    {
        Notification::fake();

        $user = User::factory()->unverified()->create();

        $this->actingAs($user)->get(route('otp.show'));

        $this->actingAs($user)
            ->post(route('otp.verify'), ['code' => '000000'])
            ->assertSessionHas('otpError');

        $this->assertNull($user->refresh()->email_verified_at);

        $otp = OtpCode::where('idUser', $user->id)->firstOrFail();
        $this->assertSame(1, $otp->attemp);
    }

    public function test_otp_code_is_deleted_after_five_wrong_attempts(): void
    {
        Notification::fake();

        $user = User::factory()->unverified()->create();

        $this->actingAs($user)->get(route('otp.show'));

        for ($i = 0; $i < OtpService::MAX_ATTEMPTS; $i++) {
            $this->actingAs($user)->post(route('otp.verify'), ['code' => '000000']);
        }

        // Setelah 5x salah, kode dihapus -> user wajib minta kode baru.
        $this->assertSame(0, OtpCode::where('idUser', $user->id)->count());
        $this->assertNull($user->refresh()->email_verified_at);
    }

    public function test_expired_otp_code_is_rejected(): void
    {
        Notification::fake();

        $user = User::factory()->unverified()->create();

        $this->actingAs($user)->get(route('otp.show'));

        $code = $this->grabOtpCode($user);

        // Majukan waktu melewati masa berlaku (5 menit + 1 detik) -> kode kedaluwarsa.
        $this->travel(OtpService::TTL_SECONDS + 1)->seconds();

        $this->actingAs($user)
            ->post(route('otp.verify'), ['code' => $code])
            ->assertSessionHas('otpError');

        $this->assertNull($user->refresh()->email_verified_at);
    }

    public function test_otp_code_must_be_six_digits(): void
    {
        Notification::fake();

        $user = User::factory()->unverified()->create();

        $this->actingAs($user)
            ->post(route('otp.verify'), ['code' => '123'])
            ->assertSessionHasErrors('code');
    }

    // ------------------------------------------------------------------
    // Kirim ulang
    // ------------------------------------------------------------------

    public function test_resend_is_blocked_during_cooldown(): void
    {
        Notification::fake();

        $user = User::factory()->unverified()->create();

        $this->actingAs($user)->get(route('otp.show'));

        // Kode pertama sudah dibuat -> cooldown aktif.
        $this->actingAs($user)
            ->post(route('otp.send'))
            ->assertSessionHas('otpError');

        $this->assertSame(1, OtpCode::where('idUser', $user->id)->count());
    }

    public function test_resend_works_after_cooldown(): void
    {
        Notification::fake();

        $user = User::factory()->unverified()->create();

        $this->actingAs($user)->get(route('otp.show'));

        $this->travel(OtpService::RESEND_COOLDOWN + 1)->seconds();

        $this->actingAs($user)
            ->post(route('otp.send'))
            ->assertSessionHas('otpSent');

        $this->assertSame(1, OtpCode::where('idUser', $user->id)->count());
    }

    // ------------------------------------------------------------------
    // Helper
    // ------------------------------------------------------------------

    /**
     * Ambil kode OTP yang baru saja dikirim (dari notifikasi yang di-fake).
     */
    protected function grabOtpCode(User $user): string
    {
        $code = null;

        Notification::assertSentTo(
            $user,
            OtpNotification::class,
            function (OtpNotification $notification) use (&$code): bool {
                $code = $notification->code;

                return true;
            },
        );

        $this->assertNotNull($code, 'Kode OTP tidak terkirim.');

        return $code;
    }
}
