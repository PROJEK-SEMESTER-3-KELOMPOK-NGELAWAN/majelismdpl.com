<?php
/**
 * ============================================
 * RESEND RESET PASSWORD OTP API
 * Mengirim ulang kode OTP untuk reset password
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

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $email = trim($_POST['email'] ?? '');
        
        if (empty($email)) {
            echo json_encode([
                'success' => false,
                'message' => 'Email diperlukan'
            ]);
            exit;
        }

        $tempDir = __DIR__ . '/../../email-verify/temp/reset_password';
        
        if (!file_exists($tempDir)) {
            echo json_encode([
                'success' => false,
                'message' => 'Tidak ada data reset password'
            ]);
            exit;
        }

        $resetFiles = glob($tempDir . '/*.json');
        $foundFile = null;
        $resetData = null;

        if ($resetFiles && is_array($resetFiles)) {
            foreach ($resetFiles as $file) {
                $data = json_decode(file_get_contents($file), true);
                if ($data && isset($data['email']) && isset($data['type']) && 
                    $data['email'] === $email && $data['type'] === 'reset_password') {
                    // Cek apakah expired
                    if (strtotime($data['expires_at']) < time()) {
                        unlink($file);
                        continue;
                    }
                    $foundFile = $file;
                    $resetData = $data;
                    break;
                }
            }
        }

        if (!$foundFile) {
            echo json_encode([
                'success' => false,
                'message' => 'Data reset password tidak ditemukan atau sudah kedaluwarsa'
            ]);
            exit;
        }

        // Generate OTP baru
        $newOtp = sprintf("%06d", random_int(0, 999999));
        $newExpires = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $resetData['verification_token'] = $newOtp;
        $resetData['expires_at'] = $newExpires;

        // Hapus file lama
        unlink($foundFile);

        // Buat file baru dengan OTP baru
        $newFile = $tempDir . '/' . $newOtp . '.json';
        
        if (file_put_contents($newFile, json_encode($resetData, JSON_PRETTY_PRINT))) {
            try {
                $emailService = new EmailService();
                if ($emailService->sendResetPasswordEmail($email, $resetData['username'], $newOtp)) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Kode OTP baru berhasil dikirim ke email Anda',
                        'expires_at' => $newExpires
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Gagal mengirim email OTP'
                    ]);
                }
            } catch (Exception $e) {
                error_log("Resend email error: " . $e->getMessage());
                echo json_encode([
                    'success' => false,
                    'message' => 'Terjadi kesalahan saat mengirim email: ' . $e->getMessage()
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Gagal memperbarui OTP'
            ]);
        }
    } catch (Exception $e) {
        error_log("Resend OTP error: " . $e->getMessage());
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
