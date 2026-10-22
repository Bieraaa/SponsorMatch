<?php

/**
 * TES SMTP: kirim 1 email lewat Mailtrap untuk memastikan kredensial jalan.
 * File ini sementara, boleh dihapus setelah tes.
 */

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Mail;

echo "=== KONFIGURASI MAIL AKTIF ===\n";
echo 'mailer   : '.config('mail.default')."\n";
echo 'host     : '.config('mail.mailers.smtp.host')."\n";
echo 'port     : '.config('mail.mailers.smtp.port')."\n";
echo 'scheme   : '.var_export(config('mail.mailers.smtp.scheme'), true)."\n";
echo 'username : '.config('mail.mailers.smtp.username')."\n";
echo 'from     : '.config('mail.from.address').' ('.config('mail.from.name').")\n";

echo "\n=== KIRIM EMAIL UJI ===\n";

try {
    Mail::raw(
        'Halo! Ini email uji dari SponsorMatch. Kalau kamu baca ini di inbox Mailtrap, berarti SMTP sudah benar-benar jalan.',
        function ($message) {
            $message->to('uji@sponsormatch.test')
                ->subject('Uji SMTP SponsorMatch - '.now()->format('d/m/Y H:i:s'));
        }
    );

    echo "BERHASIL: email diterima Mailtrap (cek Email Logs di mailtrap.io).\n";
} catch (Throwable $e) {
    echo 'GAGAL: '.get_class($e)."\n";
    echo '  pesan : '.$e->getMessage()."\n";
    echo '  trace : '.$e->getFile().':'.$e->getLine()."\n";
}
