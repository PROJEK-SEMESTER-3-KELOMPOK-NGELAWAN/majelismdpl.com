<?php
// backend/helpers/Mailer.php
namespace App\Helpers;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mailer
{
    // ====== KONFIGURASI SMTP (ganti sesuai akun Anda) ======
    private static string $SMTP_HOST = 'smtp.gmail.com';
    private static int    $SMTP_PORT = 587; // TLS
    private static string $SMTP_USER = 'dimasdwinugroho15@gmail.com';
    private static string $SMTP_PASS = 'ptut xpxs tajt nikm';
    private static string $FROM_EMAIL = 'majelismdpl@gmail.com';
    private static string $FROM_NAME  = 'Majelis MDPL';

    // Lokasi logo (path absolut file server), dan URL fallback jika embed gagal
    // Sesuaikan path agar menunjuk file logo Anda, misal: /var/www/html/img/logo-majelis.png
    private static string $LOGO_PATH_ABS = __DIR__ . '/../../assets/logo_majelis_noBg.png';
    private static string $LOGO_FALLBACK_URL = 'http://localhost/majelismdpl.com/assets/logo_majelis_noBg.png';

    private static bool   $USE_TLS    = true;

    // ====== KIRIM EMAIL UMUM ======
    public static function send(string $toEmail, string $toName, string $subject, string $htmlBody, string $altText = '', array $attachments = []): array
    {
        $mail = new PHPMailer(true);
        try {
            // SMTP
            $mail->isSMTP();
            $mail->Host       = self::$SMTP_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = self::$SMTP_USER;
            $mail->Password   = self::$SMTP_PASS;
            if (self::$USE_TLS) {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            }
            $mail->Port       = self::$SMTP_PORT;

            // Pengirim & Penerima
            $mail->setFrom(self::$FROM_EMAIL, self::$FROM_NAME);
            $mail->addAddress($toEmail, $toName ?: $toEmail);
            $mail->addReplyTo(self::$FROM_EMAIL, self::$FROM_NAME);

            // Lampiran (opsional)
            foreach ($attachments as $att) {
                if (is_array($att) && isset($att['path'])) {
                    if (is_file($att['path'])) $mail->addAttachment($att['path'], $att['name'] ?? '');
                } else {
                    if (is_file($att)) $mail->addAttachment($att);
                }
            }

            // Embed logo sebagai CID agar aman di email client
            $logoCid = null;
            if (is_file(self::$LOGO_PATH_ABS)) {
                // Pastikan tipe MIME benar (umumnya image/png)
                $mail->addEmbeddedImage(self::$LOGO_PATH_ABS, 'mdpl_logo', 'logo.png', 'base64', 'image/png'); // [web:153][web:231]
                $logoCid = 'cid:mdpl_logo';
            }

            $mail->isHTML(true);
            $mail->Subject = $subject;

            // Sisipkan CID logo jika tersedia, jika tidak gunakan URL fallback
            $logoHtml = $logoCid ? "<img src=\"{$logoCid}\" width=\"44\" height=\"44\" style=\"display:block;border:0;outline:none;text-decoration:none;border-radius:9px\" alt=\"Majelis MDPL\" />"
                : "<img src=\"" . htmlspecialchars(self::$LOGO_FALLBACK_URL) . "\" width=\"44\" height=\"44\" style=\"display:block;border:0;outline:none;text-decoration:none;border-radius:9px\" alt=\"Majelis MDPL\" />";

            // Bungkus body ke dalam frame agar konsisten gaya
            $mail->Body    = self::frame($htmlBody, $logoHtml);
            $mail->AltBody = $altText ?: strip_tags($htmlBody);

            $mail->send();
            return ['ok' => true];
        } catch (Exception $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    // ====== GAYA DASAR (table-based untuk kompatibilitas email) ======
    private static function styles(): array
    {
        // Warna utama mengikuti tema situs (cokelat keemasan)
        $brand = '#a97c50';
        $brandDark = '#8b5e3c';

        return [
            'brand'      => $brand,
            'brandDark'  => $brandDark,
            'bg'         => '#f5f3ef',
            'fg'         => '#333333',
            'muted'      => '#777777',
            'success'    => '#28a745',
            'warning'    => '#e65100',
            'danger'     => '#c82333',
            // Inline CSS (hindari margin, gunakan padding; gunakan tabel untuk layout) [web:233][web:228]
            'outer'      => 'width:100%;background:#f5f3ef;padding:18px 10px',
            'container'  => 'max-width:680px;margin:0 auto;background:#ffffff;border:1px solid #ece8e2;border-radius:14px;overflow:hidden',
            'header'     => 'background:#ffffff;padding:16px 22px;border-bottom:1px solid #f0ebe4',
            'title'      => 'font-family:Arial,Helvetica,sans-serif;color:#3D2F21;font-size:18px;font-weight:800;letter-spacing:.2px',
            'subtitle'   => 'font-family:Arial,Helvetica,sans-serif;color:#6B5847;font-size:12px',
            'body'       => 'padding:18px 22px;background:#ffffff',
            'box'        => 'padding:14px;border:1px solid #eee;border-radius:10px;background:#fbfbfb',
            'footer'     => 'padding:14px 22px;background:#fafafa;border-top:1px solid #eee;color:#777;font-size:12px',
            'cta'        => 'display:inline-block;background:#a97c50;color:#ffffff;padding:12px 18px;border-radius:10px;text-decoration:none;font-weight:700',
            'badge'      => 'display:inline-block;padding:4px 10px;border-radius:999px;color:#fff;font-size:11px;font-weight:700',
            'money'      => 'font-size:22px;font-weight:800;color:#3D2F21',
            'code'       => 'font-family:Menlo,Consolas,monospace;font-size:18px;letter-spacing:1px;color:#1b2b5a'
        ];
    }

    // ====== FRAME EMAIL UMUM (header + body + footer) ======
    private static function frame(string $contentHtml, string $logoHtml): string
    {
        $s = self::styles();

        // Header dengan logo & judul
        $header = "
        <table role=\"presentation\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" style=\"{$s['header']}\">
          <tr>
            <td style=\"vertical-align:middle;width:48px\">{$logoHtml}</td>
            <td style=\"vertical-align:middle\">
              <div style=\"{$s['title']}\">majelis mdpl</div>
              <div style=\"{$s['subtitle']}\">Transaksi • Informasi otomatis</div>
            </td>
            <td style=\"vertical-align:middle\" align=\"right\">
              <span style=\"font-family:Arial,Helvetica,sans-serif;color:#999;font-size:11px\">" . date('d M Y - H:i') . " WIB</span>
            </td>
          </tr>
        </table>";

        $footer = "
        <table role=\"presentation\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" style=\"{$s['footer']}\">
          <tr>
            <td>Anda menerima email ini karena melakukan transaksi di Majelis MDPL. Butuh bantuan? Balas email ini.</td>
          </tr>
        </table>";

        return "
        <div style=\"{$s['outer']}\">
          <table role=\"presentation\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" align=\"center\" style=\"{$s['container']}\">
            <tr><td>{$header}</td></tr>
            <tr><td style=\"{$s['body']}\">{$contentHtml}</td></tr>
            <tr><td>{$footer}</td></tr>
          </table>
        </div>";
    }

    // ====== BLOK RINGKASAN UMUM ======
    private static function orderSummary(array $d): string
    {
        $s = self::styles();
        $harga = number_format((int)($d['total_harga'] ?? 0), 0, ',', '.');

        return "
        <table role=\"presentation\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" style=\"{$s['box']}\">
          <tr>
            <td style=\"font-family:Arial,Helvetica,sans-serif;color:#6B5847;font-size:12px\">ID Pesanan</td>
            <td align=\"right\" style=\"font-family:Arial,Helvetica,sans-serif;color:#3D2F21;font-weight:700\">" . htmlspecialchars($d['order_id'] ?? '-') . "</td>
          </tr>
          <tr><td colspan=\"2\" height=\"8\"></td></tr>
          <tr>
            <td style=\"font-family:Arial,Helvetica,sans-serif;color:#6B5847;font-size:12px\">Trip</td>
            <td align=\"right\" style=\"font-family:Arial,Helvetica,sans-serif;color:#3D2F21\">" . htmlspecialchars($d['nama_gunung'] ?? '-') . "</td>
          </tr>
          <tr><td colspan=\"2\" height=\"8\"></td></tr>
          <tr>
            <td style=\"font-family:Arial,Helvetica,sans-serif;color:#6B5847;font-size:12px\">Tanggal Booking</td>
            <td align=\"right\" style=\"font-family:Arial,Helvetica,sans-serif;color:#3D2F21\">" . htmlspecialchars($d['tanggal_booking'] ?? '-') . "</td>
          </tr>
          <tr><td colspan=\"2\" height=\"8\"></td></tr>
          <tr>
            <td style=\"font-family:Arial,Helvetica,sans-serif;color:#6B5847;font-size:12px\">Total</td>
            <td align=\"right\" style=\"{$s['money']}\">Rp {$harga}</td>
          </tr>
        </table>";
    }

    // ====== TEMPLATE: PAID ======
    public static function buildPaidTemplate(array $d): string
    {
        $s = self::styles();
        $badge = "<span style=\"{$s['badge']};background:{$s['success']}\">BERHASIL</span>";
        $summary = self::orderSummary($d);
        $ctaUrl = htmlspecialchars($d['invoice_url'] ?? '#');

        $html = "
        <table role=\"presentation\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\">
          <tr>
            <td style=\"font-family:Arial,Helvetica,sans-serif;color:#3D2F21;font-size:18px;font-weight:800\">Pembayaran Selesai {$badge}</td>
          </tr>
          <tr><td height=\"10\"></td></tr>
          <tr>
            <td style=\"font-family:Arial,Helvetica,sans-serif;color:{$s['fg']}\">Terima kasih, " . htmlspecialchars($d['nama_user'] ?? 'Pelanggan') . ". Pembayaran Anda telah kami terima.</td>
          </tr>
          <tr><td height=\"14\"></td></tr>
          <tr><td>{$summary}</td></tr>
          <tr><td height=\"16\"></td></tr>
          <tr>
            <td><a href=\"{$ctaUrl}\" target=\"_blank\" style=\"{$s['cta']}\">Lihat Invoice</a></td>
          </tr>
        </table>";
        return $html;
    }

    // ====== TEMPLATE: FAILED ======
    public static function buildFailedTemplate(array $d): string
    {
        $s = self::styles();
        $badge = "<span style=\"{$s['badge']};background:{$s['danger']}\">GAGAL</span>";
        $summary = self::orderSummary($d);

        $html = "
        <table role=\"presentation\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\">
          <tr>
            <td style=\"font-family:Arial,Helvetica,sans-serif;color:#c82333;font-size:18px;font-weight:800\">Pembayaran Tidak Berhasil {$badge}</td>
          </tr>
          <tr><td height=\"10\"></td></tr>
          <tr>
            <td style=\"font-family:Arial,Helvetica,sans-serif;color:#333\">Halo " . htmlspecialchars($d['nama_user'] ?? 'Pelanggan') . ", pembayaran untuk pesanan Anda belum berhasil atau kedaluwarsa.</td>
          </tr>
          <tr><td height=\"14\"></td></tr>
          <tr><td>{$summary}</td></tr>
          <tr><td height=\"10\"></td></tr>
          <tr>
            <td style=\"font-family:Arial,Helvetica,sans-serif;color:#6B5847;font-size:12px\">Silakan lakukan pembayaran ulang dari halaman status pembayaran Anda.</td>
          </tr>
        </table>";
        return $html;
    }

    // ====== TEMPLATE: PENDING (Reminder) ======
    public static function buildPendingTemplate(array $d): string
    {
        $s = self::styles();
        $badge = "<span style=\"{$s['badge']};background:{$s['warning']}\">MENUNGGU</span>";
        $summary = self::orderSummary($d);
        $ctaUrl = htmlspecialchars($d['payment_status_url'] ?? '#');

        // Bila tersedia kode bayar (contoh VA/alfamart), kirimkan di blok khusus
        $kodeBayar = '';
        if (!empty($d['payment_code'])) {
            $kodeBayar = "
            <tr><td height=\"12\"></td></tr>
            <tr>
              <td style=\"font-family:Arial,Helvetica,sans-serif;color:#3D2F21\">Kode pembayaran:</td>
            </tr>
            <tr>
              <td style=\"{$s['box']};text-align:center\">
                <div style=\"{$s['code']}\">" . htmlspecialchars($d['payment_code']) . "</div>
              </td>
            </tr>";
        }

        $html = "
        <table role=\"presentation\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\">
          <tr>
            <td style=\"font-family:Arial,Helvetica,sans-serif;color:#e65100;font-size:18px;font-weight:800\">Tindakan Diperlukan {$badge}</td>
          </tr>
          <tr><td height=\"10\"></td></tr>
          <tr>
            <td style=\"font-family:Arial,Helvetica,sans-serif;color:#333\">Halo " . htmlspecialchars($d['nama_user'] ?? 'Pelanggan') . ", kami telah membuat pesanan Anda. Mohon segera selesaikan pembayaran.</td>
          </tr>
          <tr><td height=\"14\"></td></tr>
          <tr><td>{$summary}</td></tr>
          {$kodeBayar}
          <tr><td height=\"16\"></td></tr>
          <tr>
            <td><a href=\"{$ctaUrl}\" target=\"_blank\" style=\"{$s['cta']}\">Lanjutkan Pembayaran</a></td>
          </tr>
          <tr><td height=\"8\"></td></tr>
          <tr>
            <td style=\"font-family:Arial,Helvetica,sans-serif;color:#6B5847;font-size:12px\">Apabila Anda telah membayar tetapi status belum berubah, mohon tunggu beberapa saat lalu muat ulang halaman.</td>
          </tr>
        </table>";
        return $html;
    }
}
