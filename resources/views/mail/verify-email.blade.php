{{-- Template email verifikasi akun SponsorMatch.

     CATATAN: Template ini dirender sebagai Blade biasa (bukan Markdown),
     makanya CSS-nya ditulis INLINE — banyak email client (Gmail, Outlook)
     tidak membaca tag <style> di dalam body.

     @var string $verificationUrl
     @var int    $expireMinutes
     @var string $userName
--}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Email SponsorMatch</title>
</head>
<body style="margin:0;padding:0;background-color:#F3F4F6;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Arial,sans-serif;">

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#F3F4F6;padding:32px 16px;">
        <tr>
            <td align="center">

                <!-- Kartu utama -->
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
                       style="max-width:560px;background-color:#FFFFFF;border-radius:12px;overflow:hidden;box-shadow:0 1px 3px rgba(22,30,47,0.08);">

                    <!-- Header navy -->
                    <tr>
                        <td style="background-color:#161E2F;padding:28px 32px;text-align:center;">
                            <span style="display:inline-block;color:#FFFFFF;font-size:20px;font-weight:700;letter-spacing:-0.2px;">
                                Sponsor<span style="color:#B51A2B;">Match</span>
                            </span>
                            <p style="margin:6px 0 0;font-size:12px;color:#8A8E97;letter-spacing:0.4px;">
                                Kolaborasi untuk Event Berdampak
                            </p>
                        </td>
                    </tr>

                    <!-- Isi -->
                    <tr>
                        <td style="padding:36px 32px 8px;">
                            <h1 style="margin:0 0 12px;font-size:22px;line-height:1.3;color:#161E2F;font-weight:700;">
                                Verifikasi alamat email kamu
                            </h1>

                            <p style="margin:0 0 16px;font-size:15px;line-height:1.7;color:#384358;">
                                Halo <strong style="color:#161E2F;">{{ $userName }}</strong>, terima kasih sudah mendaftar
                                di SponsorMatch. Untuk mengaktifkan akun kamu, mohon konfirmasi alamat email ini
                                dengan menekan tombol di bawah.
                            </p>

                            <p style="margin:0 0 28px;font-size:15px;line-height:1.7;color:#384358;">
                                Link ini hanya berlaku selama <strong style="color:#161E2F;">{{ $expireMinutes }} menit</strong>.
                                Kalau sudah kedaluwarsa, kamu bisa minta link baru dari halaman masuk.
                            </p>
                        </td>
                    </tr>

                    <!-- Tombol -->
                    <tr>
                        <td align="center" style="padding:0 32px 32px;">
                            <a href="{{ $verificationUrl }}"
                               style="display:inline-block;background-color:#B51A2B;color:#FFFFFF;font-size:16px;font-weight:600;text-decoration:none;padding:16px 40px;border-radius:12px;line-height:1;">
                                Verifikasi Email Saya
                            </a>
                        </td>
                    </tr>

                    <!-- Fallback link -->
                    <tr>
                        <td style="padding:0 32px 32px;">
                            <p style="margin:0 0 8px;font-size:13px;line-height:1.6;color:#8A8E97;">
                                Kalau tombolnya tidak bisa diklik, salin tautan ini ke browser kamu:
                            </p>
                            <p style="margin:0;word-break:break-all;font-size:12px;line-height:1.6;color:#B51A2B;">
                                {{ $verificationUrl }}
                            </p>
                        </td>
                    </tr>

                    <!-- Catatan keamanan -->
                    <tr>
                        <td style="padding:0 32px 32px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
                                   style="background-color:#FFF7ED;border-left:3px solid #F59E0B;border-radius:6px;">
                                <tr>
                                    <td style="padding:14px 16px;font-size:13px;line-height:1.6;color:#7C4A03;">
                                        <strong>Tidak pernah minta email ini?</strong><br>
                                        Abaikan saja. Kamu tetap bisa login dengan password kamu.
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color:#F9FAFB;padding:20px 32px;border-top:1px solid #E5E7EB;">
                            <p style="margin:0;font-size:12px;line-height:1.6;color:#8A8E97;">
                                Email ini dikirim otomatis oleh SponsorMatch. Mohon tidak membalas email ini.
                            </p>
                        </td>
                    </tr>
                </table>

                <p style="margin:20px 0 0;font-size:12px;color:#8A8E97;">
                    &copy; {{ now()->year }} SponsorMatch. Semua hak cipta dilindungi.
                </p>

            </td>
        </tr>
    </table>
</body>
</html>
