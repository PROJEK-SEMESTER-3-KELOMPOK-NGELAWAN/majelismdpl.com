<?php
/**
 * ============================================
 * CHANGE PASSWORD API
 * Mengubah password setelah verifikasi OTP
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
        $newPassword = trim($_POST['new_password'] ?? '');

        // Validasi input
        if (empty($email) || empty($newPassword)) {
            echo json_encode([
                'success' => false,
                'message' => 'Email dan password baru harus diisi'
            ]);
            exit;
        }

        // Validasi panjang password
        if (strlen($newPassword) < 6) {
            echo json_encode([
                'success' => false,
                'message' => 'Password minimal 6 karakter'
            ]);
            exit;
        }

        // Cek apakah ada file verifikasi OTP yang valid
        $tempDir = __DIR__ . '/../../email-verify/temp/reset_password';
        $resetFiles = glob($tempDir . '/*.json');
        $foundFile = null;

        if ($resetFiles && is_array($resetFiles)) {
            foreach ($resetFiles as $file) {
                $data = json_decode(file_get_contents($file), true);
                if ($data && isset($data['email']) && isset($data['type']) && 
                    $data['email'] === $email && $data['type'] === 'reset_password') {
                    // Cek apakah masih valid (belum expired)
                    if (strtotime($data['expires_at']) >= time()) {
                        $foundFile = $file;
                        break;
                    } else {
                        unlink($file);
                    }
                }
            }
        }

        if (!$foundFile) {
            echo json_encode([
                'success' => false,
                'message' => 'Sesi reset password tidak valid atau sudah kedaluwarsa. Silakan ulangi proses reset password.'
            ]);
            exit;
        }

        // Hash password baru
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        // Update password di database
        $conn->begin_transaction();
        
        try {
            $updateStmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
            
            if (!$updateStmt) {
                throw new Exception("Gagal menyiapkan statement update");
            }
            
            $updateStmt->bind_param("ss", $hashedPassword, $email);
            
            if (!$updateStmt->execute()) {
                throw new Exception("Gagal mengubah password: " . $updateStmt->error);
            }
            
            // Cek apakah ada row yang diupdate
            if ($updateStmt->affected_rows === 0) {
                throw new Exception("Email tidak ditemukan");
            }
            
            $updateStmt->close();
            $conn->commit();
            
            // Hapus file temporary setelah berhasil
            if (file_exists($foundFile)) {
                unlink($foundFile);
            }

            echo json_encode([
                'success' => true,
                'message' => 'Password berhasil diubah! Silakan login dengan password baru Anda.',
                'email' => $email
            ]);
            
        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    } catch (Exception $e) {
        error_log("Change password error: " . $e->getMessage());
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
