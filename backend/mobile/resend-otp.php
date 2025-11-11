<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../koneksi.php';
require_once '../email-verify/EmailService.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    
    if (empty($email)) {
        echo json_encode([
            'success' => false,
            'message' => 'Email diperlukan'
        ]);
        exit;
    }

    $tempDir = __DIR__ . '/../email-verify/temp/pending_users';
    
    if (!file_exists($tempDir)) {
        echo json_encode([
            'success' => false,
            'message' => 'Tidak ada data pendaftaran'
        ]);
        exit;
    }

    $pendingFiles = glob($tempDir . '/*.json');
    $foundFile = null;
    $pendingData = null;

    foreach ($pendingFiles as $file) {
        $data = json_decode(file_get_contents($file), true);
        if ($data && $data['email'] === $email) {
            // Cek apakah expired
            if (strtotime($data['expires_at']) < time()) {
                unlink($file);
                continue;
            }
            $foundFile = $file;
            $pendingData = $data;
            break;
        }
    }

    if (!$foundFile) {
        echo json_encode([
            'success' => false,
            'message' => 'Email tidak ditemukan dalam daftar pending registrasi atau sudah kedaluwarsa'
        ]);
        exit;
    }

    // Generate OTP baru
    $newOtp = sprintf("%06d", random_int(0, 999999));
    $newExpires = date('Y-m-d H:i:s', strtotime('+24 hours'));

    $pendingData['verification_token'] = $newOtp;
    $pendingData['expires_at'] = $newExpires;

    // Hapus file lama
    unlink($foundFile);

    // Buat file baru dengan OTP baru
    $newFile = $tempDir . '/' . $newOtp . '.json';
    
    if (file_put_contents($newFile, json_encode($pendingData, JSON_PRETTY_PRINT))) {
        try {
            $emailService = new EmailService();
            if ($emailService->sendVerificationEmail($email, $pendingData['username'], $newOtp)) {
                echo json_encode([
                    'success' => true,
                    'message' => 'OTP baru berhasil dikirim ke email Anda',
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
                'message' => 'Terjadi kesalahan saat mengirim email'
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Gagal memperbarui OTP'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Metode request tidak valid'
    ]);
}
?>
