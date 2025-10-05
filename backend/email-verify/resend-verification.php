<?php
header('Content-Type: application/json');
require_once '../koneksi.php';
require_once 'EmailService.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    
    if (!$email) {
        echo json_encode(['success' => false, 'message' => 'Email diperlukan']);
        exit;
    }
    
    // Path ke folder temporary
    $tempDir = __DIR__ . '/../temp/pending_users';
    
    // Cari file pending berdasarkan email
    $pendingFiles = glob($tempDir . '/*.json');
    $foundFile = null;
    $pendingData = null;
    
    foreach ($pendingFiles as $file) {
        $data = json_decode(file_get_contents($file), true);
        if ($data && $data['email'] === $email) {
            // Cek apakah expired
            if (strtotime($data['expires_at']) < time()) {
                unlink($file); // Hapus yang expired
                continue;
            }
            $foundFile = $file;
            $pendingData = $data;
            break;
        }
    }
    
    if (!$foundFile) {
        echo json_encode(['success' => false, 'message' => 'Email tidak ditemukan dalam daftar pending registrasi atau sudah kedaluwarsa']);
        exit;
    }
    
    // Generate new token
    $newToken = bin2hex(random_bytes(32));
    $newExpires = date('Y-m-d H:i:s', strtotime('+24 hours'));
    
    // Update data dengan token baru
    $pendingData['verification_token'] = $newToken;
    $pendingData['expires_at'] = $newExpires;
    
    // Hapus file lama
    unlink($foundFile);
    
    // Simpan dengan token baru
    $newFile = $tempDir . '/' . $newToken . '.json';
    if (file_put_contents($newFile, json_encode($pendingData, JSON_PRETTY_PRINT))) {
        // Send email dengan token baru
        try {
            $emailService = new EmailService();
            if ($emailService->sendVerificationEmail($email, $pendingData['username'], $newToken)) {
                echo json_encode(['success' => true, 'message' => 'Email verifikasi telah dikirim ulang']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Gagal mengirim email']);
            }
        } catch (Exception $e) {
            error_log("Resend email error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan saat mengirim email']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal memperbarui token']);
    }
    
} else {
    echo json_encode(['success' => false, 'message' => 'Metode tidak diizinkan']);
}
?>
