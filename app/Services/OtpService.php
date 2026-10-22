<?php

namespace App\Services;

use App\Enums\OtpVerifyStatus;
use App\Models\OtpCode;
use App\Models\User;
use App\Notifications\OtpNotification;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

/**
 * Layanan OTP (One-Time Password) SponsorMatch.
 *
 * Berisi seluruh aturan keamanan OTP:
 *  - Generate kode acak yang unpredictable (CSPRNG `random_int`)
 *  - Menyimpan kode dalam bentuk HASH, bukan teks polos
 *  - Masa berlaku 60 detik (sesuai desain halaman OTP)
 *  - Maksimal 5 percobaan tebak, lalu kode dihapus
 *  - Cooldown 60 detik sebelum user boleh minta kode baru
 *
 * Kenapa hash dan bukan simpan polos?
 * Kalau tabel `otp_codes` bocor (misal dump SQL atau SQL injection),
 * attacker TIDAK bisa langsung memakai kode yang tersimpan untuk login.
 * Dia harus cracking hash-nya dulu — dan karena pakai HMAC-SHA256 dengan
 * APP_KEY sebagai kunci, itu jauh lebih sulit daripada hash biasa.
 */
class OtpService
{
    /**
     * Panjang kode OTP (jumlah digit).
     */
    public const CODE_LENGTH = 6;

    /**
     * Masa berlaku kode dalam detik.
     *
     * 5 MENIT (300 detik) — cukup panjang supaya user sempat membuka
     * email dan mengetik ulang kodenya, tapi tetap pendek supaya kode
     * yang bocor (misal tertangkap di screenshot) cepat jadi tidak berguna.
     */
    public const TTL_SECONDS = 300;

    /**
     * Masa berlaku kode dalam menit (dipakai untuk teks di email).
     */
    public const TTL_MINUTES = 5;

    /**
     * Maksimal percobaan memasukkan kode yang salah.
     */
    public const MAX_ATTEMPTS = 5;

    /**
     * Cooldown (detik) sebelum user boleh meminta kode baru.
     *
     * Sengaja TETAP 60 detik (bukan 5 menit): kalau user salah baca
     * kode atau emailnya telat masuk, dia tidak perlu menunggu 5 menit
     * untuk minta kode baru. Ini rate-limit anti-spam, bukan masa
     * berlaku kode.
     */
    public const RESEND_COOLDOWN = 60;

    /**
     * Tujuan penggunaan OTP yang sah.
     */
    public const PURPOSE_REGISTER = 'register';

    public const PURPOSE_LOGIN = 'login';

    /**
     * Error terakhir saat mencoba mengirim email OTP.
     *
     * null = email berhasil dikirim.
     * Sengaja disimpan (bukan langsung dilempar) supaya halaman OTP tetap
     * bisa dirender — user tidak boleh melihat error 500 hanya karena
     * server SMTP-nya sedang bermasalah.
     */
    protected ?Throwable $lastMailError = null;

    /**
     * Error pengiriman email terakhir (null kalau sukses).
     */
    public function lastMailError(): ?Throwable
    {
        return $this->lastMailError;
    }

    /**
     * Buat & kirim kode OTP baru ke email user.
     *
     * Kode lama (untuk user + purpose yang sama) dibuang supaya hanya
     * ada satu kode aktif — ini mencegah "kode lama masih kepakai".
     *
     * @param  string  $purpose  PURPOSE_REGISTER atau PURPOSE_LOGIN
     *
     * @throws RuntimeException  kalau masa cooldown belum habis
     */
    public function issue(User $user, string $purpose = self::PURPOSE_REGISTER, bool $force = false): OtpCode
    {
        [$otp, $code] = $this->create($user, $purpose, $force);

        // Kirim email berisi kode. Plane (tidak queue) supaya user
        // langsung menerima; kalau queue diaktifkan, pindahkan ke ShouldQueue.
        //
        // PENTING: kegagalan pengiriman TIDAK boleh menggagalkan pembuatan
        // kode OTP (kode sudah tersimpan di database di atas). Kalau SMTP
        // sedang bermasalah — kredensial salah, domain pengirim belum
        // terverifikasi, koneksi putus — user tetap mendapat halaman OTP
        // yang normal, bukan halaman error 500. Detail error aslinya kita
        // catat di log supaya developer bisa menindaklanjuti.
        // catat error SMTP terakhir
        $this->lastMailError = null;

        try {
            $user->notify(new OtpNotification($code, $purpose, self::TTL_SECONDS));
        } catch (Throwable $e) {
            $this->lastMailError = $e;
            report($e);
        }

        return $otp;
    }

    /**
     * Buat kode OTP baru TANPA mengirim email, lalu kembalikan kodenya
     * dalam bentuk teks polos.
     *
     * Dipakai oleh perintah `php artisan otp:code` supaya developer bisa
     * menguji halaman OTP tanpa harus menyiapkan SMTP. Perintah ini hanya
     * berjalan di terminal — tidak ada HTTP route yang memanggilnya.
     *
     * @return array{0: OtpCode, 1: string}  [baris OTP, kode polos]
     */
    public function issueForConsole(User $user, string $purpose = self::PURPOSE_REGISTER): array
    {
        return $this->create($user, $purpose, true);
    }

    /**
     * Inti pembuatan kode: hapus kode lama, simpan kode baru (ter-hash).
     *
     * @return array{0: OtpCode, 1: string}  [baris OTP, kode polos]
     *
     * @throws RuntimeException  kalau masa cooldown belum habis
     */
    protected function create(User $user, string $purpose, bool $force): array
    {
        if (! $force && ! $this->canResend($user, $purpose)) {
            throw new RuntimeException('Kode OTP baru belum bisa diminta. Coba lagi nanti.');
        }

        $code = $this->generateCode();

        $otp = DB::transaction(function () use ($user, $purpose, $code) {
            // Buang kode lama supaya hanya ada satu yang aktif.
            OtpCode::where('idUser', $user->getKey())
                ->where('purpose', $purpose)
                ->delete();

            return OtpCode::create([
                'idUser' => $user->getKey(),
                'purpose' => $purpose,
                // DISIMPAN SEBAGAI HASH — bukan kode aslinya.
                'code_hash' => self::hashCode($code),
                'attemp' => 0,
                'expiredAt' => now()->addSeconds(self::TTL_SECONDS),
            ]);
        });

        return [$otp, $code];
    }

    /**
     * Buang kode OTP aktif milik user + purpose ini.
     *
     * Dipakai ketika email OTP gagal terkirim: kodenya harus dihapus supaya
     * halaman OTP bisa membuat & mengirim kode baru (user tidak boleh
     * menunggu kode yang tidak pernah sampai).
     */
    public function discard(User $user, string $purpose = self::PURPOSE_REGISTER): void
    {
        OtpCode::where('idUser', $user->getKey())
            ->where('purpose', $purpose)
            ->delete();
    }

    /**
     * Verifikasi kode OTP yang di-input user.
     *
     * Alur:
     *  1. Cari kode aktif (belum dipakai) untuk user + purpose ini.
     *  2. Kalau tidak ada → NotFound.
     *  3. Kalau sudah kedaluwarsa → Expired (kode dihapus).
     *  4. Bandingkan hash dengan hash_equals() (waktu-tetap, anti timing attack).
     *  5. Kalau salah → attemp++; kalau sudah >= MAX_ATTEMPTS → hapus kode.
     *  6. Kalau benar → hapus kode (kode sekali pakai) → sukses.
     */
    public function verify(User $user, string $code, string $purpose = self::PURPOSE_REGISTER): OtpVerificationResult
    {
        // SELURUH proses ini dibungkus transaksi + row-level lock
        // (`lockForUpdate`).
        //
        // KENAPA WAJIB:
        //   Tanpa lock, beberapa request yang datang BERBARENGAN (misal
        //   attacker mengirim 10 request sekaligus dengan 10 kode salah)
        //   semuanya membaca nilai `attemp` yang sama (mis. 0), lalu
        //   semuanya menaikkan ke 1. Akibatnya batas 5 percobaan jadi tidak
        //   berarti — attacker bisa menebak ratusan kombinasi per menit.
        //
        //   Dengan lock, request yang datang paralel diantrikan: yang
        //   kedua menunggu sampai yang pertama selesai, lalu membaca nilai
        //   `attemp` yang SUDAH dinaikkan.
        //
        // Catatan: di SQLite `lockForUpdate()` diabaikan (SQLite tidak punya
        // SELECT ... FOR UPDATE), tapi SQLite mengunci tabel saat menulis,
        // jadi tes lokal tetap aman. Di production (MySQL) lock ini
        // benar-benar bekerja.
        return DB::transaction(function () use ($user, $code, $purpose) {
            $otp = OtpCode::where('idUser', $user->getKey())
                ->where('purpose', $purpose)
                ->latest('idOTP')
                ->lockForUpdate()
                ->first();

            if (! $otp) {
                return new OtpVerificationResult(OtpVerifyStatus::NotFound);
            }

            // Sudah kedaluwarsa? Hapus biar tidak bisa dicoba lagi.
            if ($otp->isExpired()) {
                $otp->delete();

                return new OtpVerificationResult(OtpVerifyStatus::Expired);
            }

            // Bandingkan hash. hash_equals() aman terhadap timing attack:
            // waktu eksekusinya tetap sama baiknya kode benar maupun salah.
            if (! hash_equals($otp->code_hash, self::hashCode(trim($code)))) {
                $otp->attemp = (int) $otp->attemp + 1;
                $otp->save();
                $attempts = (int) $otp->attemp;

                // Udah salah terus? Hapus kodenya supaya harus minta baru.
                if ($attempts >= self::MAX_ATTEMPTS) {
                    $otp->delete();

                    return new OtpVerificationResult(OtpVerifyStatus::TooManyAttempts);
                }

                return new OtpVerificationResult(
                    OtpVerifyStatus::Invalid,
                    self::MAX_ATTEMPTS - $attempts,
                );
            }

            // Kode benar → sekali pakai, langsung hapus.
            $otp->delete();

            return new OtpVerificationResult(OtpVerifyStatus::Success);
        });
    }

    /**
     * Apakah user boleh meminta kode baru sekarang?
     */
    public function canResend(User $user, string $purpose = self::PURPOSE_REGISTER): bool
    {
        return $this->resendAvailableAt($user, $purpose) === null;
    }

    /**
     * Kapan user boleh minta kode baru (kalau belum bisa, dikembalikan
     * waktu it'll ready). Null = sudah boleh sekarang.
     *
     * Tipe kembalian `CarbonInterface` (bukan `Carbon` biasa) supaya aman
     * baik ketika cast timestamp Eloquent menghasilkan `Carbon` maupun
     * `CarbonImmutable` — dua-duanya mengimplementasikan interface ini.
     */
    public function resendAvailableAt(User $user, string $purpose = self::PURPOSE_REGISTER): ?CarbonInterface
    {
        $last = OtpCode::where('idUser', $user->getKey())
            ->where('purpose', $purpose)
            ->latest('idOTP')
            ->first();

        if (! $last) {
            return null;
        }

        $readyAt = $last->createdAt->copy()->addSeconds(self::RESEND_COOLDOWN);

        return $readyAt->isFuture() ? $readyAt : null;
    }

    /**
     * Sisa detik sebelum kode yang aktif expire (0 kalau sudah habis / belum ada).
     */
    public function secondsUntilExpiry(User $user, string $purpose = self::PURPOSE_REGISTER): int
    {
        $otp = OtpCode::where('idUser', $user->getKey())
            ->where('purpose', $purpose)
            ->latest('idOTP')
            ->first();

        return $otp ? $otp->secondsRemaining() : 0;
    }

    /**
     * Generate kode OTP acak yang unpredictable.
     *
     * PENTING: pakai random_int() (CSPRNG), bukan rand()/mt_rand().
     * rand() itu pseudo-random dan bisa ditebak —recipient yang
     * bisa menebak kode OTP = bypass verifikasi.
     */
    protected function generateCode(): string
    {
        $max = (10 ** self::CODE_LENGTH) - 1;

        return str_pad((string) random_int(0, $max), self::CODE_LENGTH, '0', STR_PAD_LEFT);
    }

    /**
     * Hitung hash kode OTP.
     *
     * HMAC-SHA256 dengan APP_KEY sebagai kunci rahasia — pola yang sama
     * dengan CompanyProfile::hashSensitive() untuk NPWP/NIB.
     */
    public static function hashCode(string $code): string
    {
        return hash_hmac('sha256', $code, (string) config('app.key'));
    }
}
