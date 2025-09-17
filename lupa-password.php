<?php
session_start();
require_once 'backend/koneksi.php';

// Tambahkan autoloader Composer PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/vendor/autoload.php'; // Pastikan path sudah benar

$message = '';

// Fungsi buat token random
function randomToken($length = 32)
{
  return bin2hex(random_bytes($length / 2));
}

// --- TAHAP 1: Kirim Link Reset Password Lewat EMAIL via SMTP PHPMailer ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_GET['token'])) {
  $email = trim($_POST['email'] ?? '');
  if (!$email) {
    $message = 'Email wajib diisi.';
  } else {
    $stmt = $conn->prepare("SELECT id_user, email FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows) {
      $user = $result->fetch_assoc();
      $token = randomToken(32);
      $expires = date("Y-m-d H:i:s", strtotime("+1 hour"));
      $stmt2 = $conn->prepare("INSERT INTO reset_tokens (user_id, token, expires_at, used) VALUES (?, ?, ?, 0)");
      $stmt2->bind_param("iss", $user['id_user'], $token, $expires);
      $stmt2->execute();

      $reset_link = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]/majelismdpl.com/lupa-password.php?token=$token";

      // Kirim email menggunakan PHPMailer
      $mail = new PHPMailer(true);
      try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'dimasdwinugroho15@gmail.com'; // Ganti dengan email kamu
        $mail->Password   = 'ptut xpxs tajt nikm'; // Ganti dengan App Password Gmail
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        $mail->setFrom('dimasdwinugroho15@gmail.com', 'Majelis MDPL'); // Sama dengan username emailmu
        $mail->addAddress($email);
        $mail->Subject = 'Reset Password Majelis MDPL';
        $mail->Body    = "Klik link berikut untuk mengatur ulang password Anda:\n$reset_link\nLink hanya berlaku selama 1 jam.";

        $mail->send();
        $message = 'Instruksi reset sudah dikirim ke email Anda.';
      } catch (Exception $e) {
        $message = 'Pengiriman email gagal: ' . $mail->ErrorInfo;
      }
    } else {
      $message = 'Email tidak terdaftar.';
    }
  }
}

// --- TAHAP 2: Reset Password Baru ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['token'])) {
  $token = $_GET['token'];
  $password = $_POST['password'] ?? '';
  $confirm  = $_POST['confirm'] ?? '';
  if (!$password || !$confirm) {
    $message = 'Semua kolom wajib diisi.';
  } else if ($password !== $confirm) {
    $message = 'Password dan konfirmasi harus sama.';
  } else if (strlen($password) < 6) {
    $message = 'Password minimal 6 karakter.';
  } else {
    $stmt = $conn->prepare("SELECT user_id, expires_at, used FROM reset_tokens WHERE token=? LIMIT 1");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
      if ($row['used']) {
        $message = 'Link reset sudah digunakan.';
      } else if (strtotime($row['expires_at']) < time()) {
        $message = 'Link reset sudah kedaluwarsa.';
      } else {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt2 = $conn->prepare("UPDATE users SET password=? WHERE id_user=?");
        $stmt2->bind_param("si", $hashed, $row['user_id']);
        $stmt2->execute();
        $stmt3 = $conn->prepare("UPDATE reset_tokens SET used=1 WHERE token=?");
        $stmt3->bind_param("s", $token);
        $stmt3->execute();
        $message = 'Password berhasil direset. Silakan login.';
      }
    } else {
      $message = 'Token reset tidak valid.';
    }
  }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <title>Lupa Password</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background: #f6f0e8;
    }

    .forgot-container {
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 2px 12px #d9b68044;
      max-width: 340px;
      margin: 60px auto 0 auto;
      padding: 28px 26px;
      text-align: center;
    }

    .forgot-title {
      font-weight: 700;
      margin-bottom: 14px;
      color: #a97c50;
      font-size: 1.25em;
    }

    .forgot-btn {
      background: #a97c50;
      color: #fff;
      width: 100%;
      border: 0;
      border-radius: 7px;
      padding: 11px 0;
      font-weight: 600;
      letter-spacing: 0.5px;
      margin-top: 10px;
    }

    .info-message {
      color: #a97c50;
      margin-bottom: 14px;
    }

    .error-message {
      color: #b92d2d;
      margin-bottom: 14px;
    }

    .login-link {
      margin-top: 15px;
      display: block;
      color: #a97c50;
    }
  </style>
</head>

<body>
  <div class="forgot-container">
    <h3 class="forgot-title">Lupa Password</h3>
    <?php if ($message): ?>
      <div class="<?= strpos($message, 'berhasil') !== false ? 'info-message' : 'error-message' ?>">
        <?= htmlspecialchars($message) ?>
      </div>
    <?php endif; ?>

    <?php if (isset($_GET['token'])): ?>
      <form method="post" action="lupa-password.php?token=<?= htmlspecialchars($_GET['token']) ?>">
        <div class="mb-3">
          <input type="password" name="password" class="form-control" placeholder="Password baru" required>
        </div>
        <div class="mb-3">
          <input type="password" name="confirm" class="form-control" placeholder="Konfirmasi password baru" required>
        </div>
        <button type="submit" class="forgot-btn">Reset Password</button>
      </form>
      <a href="/majelismdpl.com/index.php" class="login-link">← Kembali ke Login</a>
    <?php else: ?>
      <form method="post" action="lupa-password.php">
        <div class="mb-3">
          <input type="email" name="email" class="form-control" placeholder="Masukkan email anda" required>
        </div>
        <button type="submit" class="forgot-btn">Kirim Instruksi Reset</button>
      </form>
      <a href="/majelismdpl.com/index.php" class="login-link">← Kembali ke Login</a>
    <?php endif; ?>
  </div>
</body>

</html>