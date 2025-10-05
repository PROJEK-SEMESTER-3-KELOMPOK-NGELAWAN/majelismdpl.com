<?php
// Gunakan Composer autoload yang sudah ada - sama seperti lupa-password.php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Path ke autoload dari backend/email-verify/ ke root
require __DIR__ . '/../../vendor/autoload.php';

class EmailService {
    private $smtp_host = 'smtp.gmail.com';
    private $smtp_port = 587;
    private $smtp_username = 'dimasdwinugroho15@gmail.com';
    private $smtp_password = 'ptut xpxs tajt nikm'; // App Password Gmail
    private $from_email = 'dimasdwinugroho15@gmail.com';
    private $from_name = 'Majelis MDPL';
    
    public function sendVerificationEmail($email, $username, $token) {
        // Sama seperti lupa-password.php: langsung buat instance PHPMailer
        $mail = new PHPMailer(true);
        
        try {
            // SMTP Configuration - persis sama dengan lupa-password.php
            $mail->isSMTP();
            $mail->Host = $this->smtp_host;
            $mail->SMTPAuth = true;
            $mail->Username = $this->smtp_username;
            $mail->Password = $this->smtp_password;
            $mail->SMTPSecure = 'tls';
            $mail->Port = $this->smtp_port;
            
            $mail->setFrom($this->from_email, $this->from_name);
            $mail->addAddress($email);
            
            // *** PENTING: Set isHTML() SEBELUM mengatur Body ***
            $mail->isHTML(true); // Set email format ke HTML
            $mail->CharSet = 'UTF-8'; // Set charset untuk emoji dan karakter khusus
            
            // URL verifikasi
            $verificationLink = "http://localhost/majelismdpl.com/backend/email-verify/verify-email.php?token=" . urlencode($token);
            
            $mail->Subject = 'Verifikasi Email - Majelis MDPL';
            $mail->Body = $this->getVerificationEmailTemplate($username, $verificationLink);
            
            // Set AltBody untuk email client yang tidak support HTML
            $mail->AltBody = "Halo $username!\n\nSilakan verifikasi email Anda dengan mengklik link berikut:\n$verificationLink\n\nLink akan kedaluwarsa dalam 24 jam.\n\nTerima kasih,\nTim Majelis MDPL";
            
            return $mail->send();
            
        } catch (Exception $e) {
            error_log("Email sending failed: " . $e->getMessage());
            return false;
        }
    }
    
    private function getVerificationEmailTemplate($username, $link) {
        return '<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Email</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f7f7f7;
            line-height: 1.6;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 600;
        }
        .content {
            padding: 40px 30px;
        }
        .content h2 {
            color: #2c3e50;
            margin-bottom: 20px;
            font-size: 24px;
        }
        .content p {
            line-height: 1.8;
            margin-bottom: 16px;
            color: #555;
            font-size: 16px;
        }
        .btn {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white !important;
            padding: 18px 40px;
            text-decoration: none;
            border-radius: 50px;
            margin: 25px 0;
            font-weight: 600;
            font-size: 16px;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }
        .link-box {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            word-break: break-all;
            margin: 20px 0;
            border-left: 4px solid #667eea;
            font-family: monospace;
            font-size: 14px;
        }
        .footer {
            background: #f8f9fa;
            padding: 30px;
            text-align: center;
            font-size: 14px;
            color: #666;
            border-top: 1px solid #eee;
        }
        .warning {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #f39c12;
        }
        .features {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .features ul {
            margin: 0;
            padding-left: 0;
            list-style: none;
        }
        .features li {
            padding: 8px 0;
            position: relative;
            padding-left: 30px;
        }
        .features li:before {
            content: "✓";
            position: absolute;
            left: 0;
            color: #27ae60;
            font-weight: bold;
            font-size: 18px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🏔️ Majelis MDPL Open Trip</h1>
            <p style="margin: 10px 0 0 0; opacity: 0.9; font-size: 16px;">Verifikasi Email Anda</p>
        </div>
        <div class="content">
            <h2>Halo, ' . htmlspecialchars($username) . '! 👋</h2>
            <p>Terima kasih telah bergabung dengan <strong>Majelis MDPL Open Trip</strong>! Kami sangat senang Anda menjadi bagian dari komunitas pecinta alam kami.</p>
            
            <p>Untuk mengaktifkan akun dan mulai menjelajahi petualangan seru bersama kami, silakan verifikasi alamat email Anda dengan mengklik tombol di bawah ini:</p>
            
            <div style="text-align: center; margin: 35px 0;">
                <a href="' . htmlspecialchars($link) . '" class="btn">
                    ✅ Verifikasi Email Saya
                </a>
            </div>
            
            <div class="warning">
                <strong>⚠️ Penting:</strong> Link verifikasi ini akan kedaluwarsa dalam <strong>24 jam</strong> untuk keamanan akun Anda.
            </div>
            
            <p style="margin-top: 30px;">Jika Anda tidak merasa mendaftar di platform kami, silakan abaikan email ini.</p>
            
            <p style="margin-top: 25px;">Salam petualangan,<br><strong>Tim Majelis MDPL</strong> 🏔️⛰️</p>
        </div>
        <div class="footer">
            <p><strong>© 2025 Majelis MDPL Open Trip</strong><br>
            Semua hak dilindungi undang-undang.</p>
            <p style="margin-top: 15px; font-size: 13px;">
                Email ini dikirim secara otomatis, mohon jangan membalas.<br>
                Butuh bantuan? Hubungi: <strong>support@majelismdpl.com</strong>
            </p>
        </div>
    </div>
</body>
</html>';
    }
}
?>
