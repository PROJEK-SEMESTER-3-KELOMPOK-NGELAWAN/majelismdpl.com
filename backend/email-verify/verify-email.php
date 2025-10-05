<?php
require_once '../koneksi.php';

$success = false;
$message = '';
$username = '';

// Handle verification
if (isset($_GET['token']) && !empty($_GET['token'])) {
    $token = $_GET['token'];
    
    try {
        // Path ke file temporary
        $tempDir = __DIR__ . '/../temp/pending_users';
        $tempFile = $tempDir . '/' . $token . '.json';
        
        // Cek apakah file temporary ada
        if (!file_exists($tempFile)) {
            $success = false;
            $message = "Token verifikasi tidak valid atau sudah kedaluwarsa.";
        } else {
            // Baca data dari file temporary
            $pendingData = json_decode(file_get_contents($tempFile), true);
            
            if (!$pendingData) {
                $success = false;
                $message = "Data verifikasi tidak valid.";
            } else {
                // Cek apakah token sudah expired
                if (strtotime($pendingData['expires_at']) < time()) {
                    unlink($tempFile); // Hapus file yang expired
                    $success = false;
                    $message = "Token verifikasi sudah kedaluwarsa. Silakan daftar ulang.";
                } else {
                    // Mulai transaction untuk insert ke database
                    $conn->begin_transaction();
                    
                    try {
                        // Insert ke tabel users yang sesungguhnya
                        $insertStmt = $conn->prepare(
                            "INSERT INTO users (username, password, role, email, no_wa, alamat, email_verified) 
                             VALUES (?, ?, 'user', ?, ?, ?, 1)"
                        );
                        
                        if (!$insertStmt) {
                            throw new Exception("Gagal menyiapkan statement insert");
                        }
                        
                        $insertStmt->bind_param(
                            "sssss", 
                            $pendingData['username'], 
                            $pendingData['password'], 
                            $pendingData['email'], 
                            $pendingData['no_wa'], 
                            $pendingData['alamat']
                        );
                        
                        if (!$insertStmt->execute()) {
                            throw new Exception("Gagal membuat akun: " . $insertStmt->error);
                        }
                        
                        $insertStmt->close();
                        
                        // Commit transaction
                        $conn->commit();
                        
                        // Hapus file temporary setelah berhasil
                        unlink($tempFile);
                        
                        $success = true;
                        $message = "Email berhasil diverifikasi! Akun Anda telah dibuat dan aktif.";
                        $username = $pendingData['username'];
                        
                    } catch (Exception $e) {
                        // Rollback jika ada error
                        $conn->rollback();
                        $success = false;
                        $message = "Terjadi kesalahan saat membuat akun: " . $e->getMessage();
                    }
                }
            }
        }
        
    } catch (Exception $e) {
        error_log("Email verification error: " . $e->getMessage());
        $success = false;
        $message = "Terjadi kesalahan sistem. Silakan coba lagi nanti.";
    }
} else {
    $success = false;
    $message = "Token verifikasi tidak ditemukan.";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $success ? 'Verifikasi Berhasil' : 'Verifikasi Gagal'; ?> - Majelis MDPL</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .verification-container {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 500px;
            width: 100%;
        }
        
        .success-icon, .error-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            color: white;
        }
        
        .success-icon {
            background: linear-gradient(135deg, #27ae60, #2ecc71);
            animation: successPulse 2s ease-in-out;
        }
        
        .error-icon {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            animation: errorShake 0.5s ease-in-out;
        }
        
        @keyframes successPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }
        
        @keyframes errorShake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }
        
        h1 {
            color: #2c3e50;
            margin-bottom: 15px;
            font-size: 28px;
            font-weight: 600;
        }
        
        .welcome-message {
            font-size: 18px;
            color: #27ae60;
            margin-bottom: 10px;
            font-weight: 500;
        }
        
        p {
            color: #666;
            line-height: 1.6;
            margin-bottom: 25px;
            font-size: 16px;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            text-decoration: none;
            border-radius: 25px;
            transition: all 0.3s ease;
            font-weight: 600;
            margin: 10px;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
        }
        
        .btn-secondary {
            background: linear-gradient(135deg, #95a5a6, #7f8c8d);
        }
        
        .btn-secondary:hover {
            box-shadow: 0 5px 15px rgba(149, 165, 166, 0.3);
        }
    </style>
</head>
<body>
    <div class="verification-container">
        <div style="font-size: 60px; margin-bottom: 20px;"></div>
        
        <?php if ($success): ?>
            <div class="success-icon">✓</div>
            <h1>Akun Berhasil Dibuat!</h1>
            <?php if ($username): ?>
                <p class="welcome-message">Selamat datang, <?php echo htmlspecialchars($username); ?>! 🎉</p>
            <?php endif; ?>
            <p><?php echo htmlspecialchars($message); ?></p>
            <p>Anda sekarang dapat login dan menikmati semua fitur platform kami.</p>
            
            <a href="/majelismdpl.com/" class="btn">🏠 Login Sekarang</a>
            
        <?php else: ?>
            <div class="error-icon">✕</div>
            <h1>Verifikasi Gagal</h1>
            <p><?php echo htmlspecialchars($message); ?></p>
            
            <a href="/majelismdpl.com/" class="btn">🏠 Kembali ke Beranda</a>
            <a href="/majelismdpl.com/" class="btn btn-secondary">📝 Daftar Ulang</a>
        <?php endif; ?>
    </div>
</body>
</html>
