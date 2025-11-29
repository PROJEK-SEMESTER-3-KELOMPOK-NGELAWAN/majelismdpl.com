<?php
/**
 * ============================================
 * VERIFY RESET PASSWORD OTP API
 * Memverifikasi kode OTP untuk reset password
 * ============================================
 */

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=utf-8');

// Include config dan koneksi dengan path relatif
require_once __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../../koneksi.php';

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $email = trim($_POST['email'] ?? '');
        $otp = trim($_POST['otp'] ?? '');
        
        if (empty($email) || empty($otp)) {
            echo json_encode([
                'success' => false,
                'message' => 'Email dan kode OTP harus diisi'
            ]);
            exit;
        }

        $tempDir = __DIR__ . '/../../email-verify/temp/reset_password';
        $tempFile = $tempDir . '/' . $otp . '.json';

        if (!file_exists($tempFile)) {
            echo json_encode([
                'success' => false,
                'message' => 'Kode OTP salah atau sudah kedaluwarsa'
            ]);
            exit;
        }

        $resetData = json_decode(file_get_contents($tempFile), true);
        
        if (!$resetData) {
            echo json_encode([
                'success' => false,
                'message' => 'Data OTP tidak valid'
            ]);
            exit;
        }

        // Validasi email cocok
        if (!isset($resetData['email']) || $resetData['email'] !== $email) {
            echo json_encode([
                'success' => false,
                'message' => 'Email tidak sesuai'
            ]);
            exit;
        }

        // Validasi tipe reset password
        if (!isset($resetData['type']) || $resetData['type'] !== 'reset_password') {
            echo json_encode([
                'success' => false,
                'message' => 'Kode OTP tidak valid untuk reset password'
            ]);
            exit;
        }

        // Cek apakah OTP expired
        if (strtotime($resetData['expires_at']) < time()) {
            unlink($tempFile);
            echo json_encode([
                'success' => false,
                'message' => 'Kode OTP sudah kedaluwarsa'
            ]);
            exit;
        }

        // OTP valid, tapi jangan hapus file dulu
        // File akan dihapus setelah password berhasil diubah
        echo json_encode([
            'success' => true,
            'message' => 'Kode OTP berhasil diverifikasi',
            'email' => $email
        ]);
    } catch (Exception $e) {
        error_log("Verify OTP error: " . $e->getMessage());
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
