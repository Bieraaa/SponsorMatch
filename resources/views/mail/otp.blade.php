{{-- Template email berisi kode OTP SponsorMatch.

     @var string $code       Kode OTP 6 digit
     @var array  $digits     Kode dipecah jadi 6 karakter
     @var string $purpose    register atau login
     @var int    $expireSeconds
     @var int    $expireMinutes
     @var string $userName
--}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kode OTP SponsorMatch</title>
</head>
<body style="margin:0;padding:0;background-color:#F3F4F6;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Arial,sans-serif;">

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#F3F4F6;padding:32px 16px;">
        <tr>
            <td align="center">

                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
                       style="max-width:560px;background-color:#FFFFFF;border-radius:12px;overflow:hidden;box-shadow:0 1px 3px rgba(22,30,47,0.08);">

                    <!-- Header -->
                    <tr>
                        <td style="background-color:#161E2F;padding:28px 32px;text-align:center;">
                            <span style="display:inline-block;color:#FFFFFF;font-size:20px;font-weight:700;">
                                Sponsor<span style="color:#B51A2B;">Match</span>
                            </span>
                        </td>
                    </tr>

                    <!-- Judul -->
                    <tr>
                        <td style="padding:36px 32px 0;text-align:center;">
                            <h1 style="margin:0 0 10px;font-size:22px;line-height:1.3;color:#161E2F;font-weight:700;">
                                Kode Verifikasi Kamu
                            </h1>
                            <p style="margin:0 0 26px;font-size:15px;line-height:1.7;color:#384358;">
                                Halo <strong style="color:#161E2F;">{{ $userName }}</strong>, masukkan kode berikut
                                untuk memverifikasi akun kamu:
                            </p>
                        </td>
                    </tr>

                    <!-- 6 kotak kode -->
                    <tr>
                        <td style="padding:0 32px 26px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    @foreach ($digits as $digit)
                                        <td align="center" style="padding:0 3px;">
                                            <div style="width:56px;height:64px;line-height:64px;border:1px solid #384358;border-radius:10px;font-size:30px;font-weight:700;color:#161E2F;background-color:#FFFFFF;">
                                                {{ $digit }}
                                            </div>
                                        </td>
                                    @endforeach
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Masa berlaku -->
                    <tr>
                        <td style="padding:0 32px 30px;text-align:center;">
                            <p style="margin:0;font-size:14px;line-height:1.6;color:#8A8E97;">
                                Kode ini berlaku selama <strong style="color:#161E2F;">{{ $expireMinutes }} menit</strong>
                                dan hanya bisa dipakai <strong style="color:#161E2F;">satu kali</strong>.
                            </p>
                        </td>
                    </tr>

                    <!-- Peringatan -->
                    <tr>
                        <td style="padding:0 32px 32px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
                                   style="background-color:#FFF7ED;border-left:3px solid #F59E0B;border-radius:6px;">
                                <tr>
                                    <td style="padding:14px 16px;font-size:13px;line-height:1.6;color:#7C4A03;">
                                        <strong>Jangan bagikan kode ini ke siapa pun.</strong><br>
                                        SponsorMatch tidak pernah menanyakan kode OTP lewat telepon, chat, atau email.
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
