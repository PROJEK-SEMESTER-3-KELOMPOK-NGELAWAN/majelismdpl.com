<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../../vendor/autoload.php';

class EmailService {
    private $smtp_host = 'smtp.gmail.com';
    private $smtp_port = 587;
    private $smtp_username = 'dimasdwinugroho15@gmail.com';
    private $smtp_password = 'ptut xpxs tajt nikm'; // App Password Gmail
    private $from_email = 'dimasdwinugroho15@gmail.com';
    private $from_name = 'Majelis MDPL';

    public function sendVerificationEmail($email, $username, $otp) {
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = $this->smtp_host;
            $mail->SMTPAuth = true;
            $mail->Username = $this->smtp_username;
            $mail->Password = $this->smtp_password;
            $mail->SMTPSecure = 'tls';
            $mail->Port = $this->smtp_port;

            $mail->setFrom($this->from_email, $this->from_name);
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';

            $mail->Subject = 'Kode OTP Verifikasi Akun - Majelis MDPL';
            $mail->Body = $this->getVerificationEmailTemplate($username, $otp);
            $mail->AltBody = "Halo $username!\n\nKode OTP verifikasi Anda: $otp\n\nMasukkan kode OTP ini di halaman verifikasi akun. Kode berlaku 24 jam.\n\nTim Majelis MDPL";

            return $mail->send();
        } catch (Exception $e) {
            error_log("Email sending failed: " . $e->getMessage());
            return false;
        }
    }

    private function getVerificationEmailTemplate($username, $otp) {
        return '<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Kode OTP Verifikasi Email</title>
    <style>
        body{font-family:Arial,sans-serif;background:#f7f7f7;}
        .container{max-width:600px;margin:0 auto;background:white;border-radius:10px;overflow:hidden;box-shadow:0 4px 15px rgba(0,0,0,0.1);}
        .header{background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);color:white;padding:40px 30px;text-align:center;}
        .content{padding:40px 30px;}
        .otp-code-box{font-family: monospace; font-size: 28px; color: #333; padding: 15px; background: #f0f0f0; border-radius: 8px; display: inline-block; letter-spacing: 7px; margin-top: 20px;}
        .footer{background:#f8f9fa;padding:30px;text-align:center;font-size:14px;color:#666;border-top:1px solid #eee;}
        .warning{background:#fff3cd;border:1px solid #ffeaa7;padding:20px;border-radius:8px;margin:20px 0;border-left:4px solid #f39c12;}
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🏔️ Majelis MDPL Open Trip</h1>
            <p style="margin: 10px 0 0 0; opacity: 0.9; font-size: 16px;">Kode OTP Verifikasi Akun</p>
        </div>
        <div class="content">
            <h2>Halo, ' . htmlspecialchars($username) . '! 👋</h2>
            <p>Ini adalah <strong>kode OTP verifikasi</strong> untuk aktivasi akun Anda:</p>
            <div class="otp-code-box">' . htmlspecialchars($otp) . '</div>
            <p>Salin dan masukkan kode OTP tersebut pada halaman verifikasi akun di website.</p>
            <div class="warning"><strong>⚠️ Penting:</strong> Kode OTP ini berlaku selama <b>24 jam</b>.</div>
            <p>Jika Anda tidak merasa mendaftar, abaikan email ini.</p>
            <p>Salam petualangan,<br><strong>Tim Majelis MDPL</strong> 🏔️⛰️</p>
        </div>
        <div class="footer">
            <p><strong>© 2025 Majelis MDPL Open Trip</strong><br>All rights reserved.</p>
            <p style="font-size: 13px;">Email ini dikirim otomatis, mohon jangan membalas.<br>Butuh bantuan? Hubungi: <strong>support@majelismdpl.com</strong></p>
        </div>
    </div>
</body>
</html>';
    }
}
?>
