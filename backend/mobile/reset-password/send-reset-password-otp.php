<?php
/**
 * ============================================
 * SEND RESET PASSWORD OTP API
 * Mengirim kode OTP untuk reset password
 * ============================================
 */

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=utf-8');

// Include config dan koneksi dengan path relatif
require_once __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../../koneksi.php';
require_once __DIR__ . '/../../email-verify/EmailService.php';

ini_set('display_errors', 0);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/php_errors.log');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $email = trim($_POST['email'] ?? '');

        // Validasi input
        if (empty($email)) {
            echo json_encode([
                'success' => false,
                'message' => 'Email harus diisi'
            ]);
            exit;
        }

        // Validasi format email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode([
                'success' => false,
                'message' => 'Format email tidak valid'
            ]);
            exit;
        }

        // Cek apakah email terdaftar di database
        $checkEmail = $conn->prepare("SELECT id_user, username FROM users WHERE email = ?");
        if (!$checkEmail) {
            throw new Exception("Prepare statement failed: " . $conn->error);
        }
        $checkEmail->bind_param("s", $email);
        $checkEmail->execute();
        $resultEmail = $checkEmail->get_result();

        if ($resultEmail->num_rows === 0) {
            echo json_encode([
                'success' => false,
                'message' => 'Email tidak terdaftar'
            ]);
            exit;
        }

        $userData = $resultEmail->fetch_assoc();
        $username = $userData['username'];
        $checkEmail->close();

        // Generate OTP 6 digit
        $otp = sprintf("%06d", random_int(0, 999999));
        $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));

        // Simpan data pending reset password ke file JSON
        $tempDir = __DIR__ . '/../../email-verify/temp/reset_password';
        
        // Buat folder jika belum ada
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        // Hapus file OTP lama untuk email ini jika ada
        $existingFiles = glob($tempDir . '/*.json');
        if ($existingFiles && is_array($existingFiles)) {
            foreach ($existingFiles as $file) {
                $data = json_decode(file_get_contents($file), true);
                if ($data && isset($data['email']) && $data['email'] === $email) {
                    unlink($file);
                }
            }
        }

        $resetData = [
            'email' => $email,
            'username' => $username,
            'verification_token' => $otp,
            'expires_at' => $expires_at,
            'created_at' => date('Y-m-d H:i:s'),
            'type' => 'reset_password'
        ];

        $tempFile = $tempDir . '/' . $otp . '.json';

        if (file_put_contents($tempFile, json_encode($resetData, JSON_PRETTY_PRINT))) {
            // Kirim email OTP
            try {
                $emailService = new EmailService();
                if ($emailService->sendResetPasswordEmail($email, $username, $otp)) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Kode OTP berhasil dikirim ke email Anda',
                        'email' => $email,
                        'expires_at' => $expires_at
                    ]);
                } else {
                    // Hapus file temp jika email gagal dikirim
                    if (file_exists($tempFile)) {
                        unlink($tempFile);
                    }
                    echo json_encode([
                        'success' => false,
                        'message' => 'Gagal mengirim email OTP'
                    ]);
                }
            } catch (Exception $e) {
                // Hapus file temp jika terjadi error
                if (file_exists($tempFile)) {
                    unlink($tempFile);
                }
                error_log("Email sending error: " . $e->getMessage());
                echo json_encode([
                    'success' => false,
                    'message' => 'Terjadi kesalahan saat mengirim email: ' . $e->getMessage()
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Gagal menyimpan data reset password'
            ]);
        }
    } catch (Exception $e) {
        error_log("Reset password error: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Metode request tidak valid'
    ]);
}

if (isset($conn)) {
    $conn->close();
}
?>
