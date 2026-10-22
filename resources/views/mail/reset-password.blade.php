{{-- Template email reset password SponsorMatch.

     @var string $resetUrl
     @var int    $expireMinutes
     @var string $userName
--}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password SponsorMatch</title>
</head>
<body style="margin:0;padding:0;background-color:#F3F4F6;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Arial,sans-serif;">

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#F3F4F6;padding:32px 16px;">
        <tr>
            <td align="center">

                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
                       style="max-width:560px;background-color:#FFFFFF;border-radius:12px;overflow:hidden;box-shadow:0 1px 3px rgba(22,30,47,0.08);">

                    <tr>
                        <td style="background-color:#161E2F;padding:28px 32px;text-align:center;">
                            <span style="display:inline-block;color:#FFFFFF;font-size:20px;font-weight:700;">
                                Sponsor<span style="color:#B51A2B;">Match</span>
                            </span>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:36px 32px 8px;">
                            <h1 style="margin:0 0 12px;font-size:22px;line-height:1.3;color:#161E2F;font-weight:700;">
                                Reset password kamu
                            </h1>
                            <p style="margin:0 0 16px;font-size:15px;line-height:1.7;color:#384358;">
                                Halo <strong style="color:#161E2F;">{{ $userName }}</strong>, kami menerima permintaan
                                untuk mengatur ulang password akun SponsorMatch kamu.
                            </p>
                            <p style="margin:0 0 28px;font-size:15px;line-height:1.7;color:#384358;">
                                Tekan tombol di bawah untuk membuat password baru. Tautan ini berlaku
                                <strong style="color:#161E2F;">{{ $expireMinutes }} menit</strong>.
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td align="center" style="padding:0 32px 32px;">
                            <a href="{{ $resetUrl }}"
                               style="display:inline-block;background-color:#B51A2B;color:#FFFFFF;font-size:16px;font-weight:600;text-decoration:none;padding:16px 40px;border-radius:12px;line-height:1;">
                                Atur Password Baru
                            </a>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:0 32px 32px;">
                            <p style="margin:0 0 8px;font-size:13px;line-height:1.6;color:#8A8E97;">
                                Kalau tombolnya tidak bisa diklik, salin tautan ini:
                            </p>
                            <p style="margin:0;word-break:break-all;font-size:12px;line-height:1.6;color:#B51A2B;">
                                {{ $resetUrl }}
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:0 32px 32px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
                                   style="background-color:#FEF2F2;border-left:3px solid #B51A2B;border-radius:6px;">
                                <tr>
                                    <td style="padding:14px 16px;font-size:13px;line-height:1.6;color:#7F1D1D;">
                                        <strong>Tidak kamu yang minta ini?</strong><br>
                                        Abaikan email ini. Password kamu tidak akan berubah sampai kamu
                                        menekan tombol di atas.
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

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
