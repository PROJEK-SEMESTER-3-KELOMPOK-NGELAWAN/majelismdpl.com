<?php
session_start();
require_once '../backend/koneksi.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../vendor/autoload.php';

$message = '';
$step = 1; // default: minta OTP

// Tahap 1: Minta OTP
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_otp'])) {
  $email = trim($_POST['email'] ?? '');
  if (!$email) {
    $message = 'Email wajib diisi.';
    $step = 1;
  } else {
    $stmt = $conn->prepare("SELECT id_user, email FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows) {
      $user = $result->fetch_assoc();
      $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT); // 6 digit
      $expires = date("Y-m-d H:i:s", strtotime("+10 minutes"));
      $stmt2 = $conn->prepare("INSERT INTO reset_tokens (user_id, token, otp_code, expires_at, used) VALUES (?, '', ?, ?, 0)");
      $stmt2->bind_param("iss", $user['id_user'], $otp, $expires);
      $stmt2->execute();
      // Kirim ke email
      $mail = new PHPMailer(true);

      try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'dimasdwinugroho15@gmail.com'; // email kamu
        $mail->Password = 'ptut xpxs tajt nikm'; // app password
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;
        $mail->setFrom('dimasdwinugroho15@gmail.com', 'Majelis MDPL');
        $mail->addAddress($email);

        $mail->Subject = 'Kode OTP Reset Password Majelis MDPL';

        // Email HTML body lebih menarik
        $mail->isHTML(true);
        $mail->Body = '
        <div style="background:#f3f3f3;padding:30px;font-family:Arial,sans-serif;">
          <center>
            <div style="background:#fff;border-radius:12px;padding:24px 28px;max-width:420px;margin:auto;box-shadow:0 8px 32px rgba(80,80,80,0.13);">
              <h2 style="color:#4752be;margin-bottom:10px">Permintaan Reset Password</h2>
              <p style="font-size:15px;color:#222;margin-bottom:16px">
                Halo, <b>' . htmlentities($email) . '</b>!<br>
                Berikut adalah kode OTP untuk reset password akun Anda di Majelis MDPL.
              </p>
              <div style="background:#f6f8ff;border-radius:8px;padding:14px 0;margin-bottom:18px;border:1px solid #e2e6f8">
                <span style="font-size:26px;letter-spacing:7px;font-weight:bold;color:#4752be;">' . htmlentities($otp) . '</span>
              </div>
              <div style="font-size:14px;color:#888;margin-bottom:12px">
                Kode OTP hanya berlaku selama <b>10 menit</b>.
              </div>
              <hr style="border:none;border-top:1px solid #eee;margin:24px 0 16px">
              <div style="font-size:12px;color:#999;line-height:1.6">
                Jika Anda tidak meminta reset password, abaikan email ini.<br>
                Salam,<br>
                <b>Majelis MDPL</b>
              </div>
            </div>
          </center>
        </div>
    ';

        $mail->AltBody = "Kode OTP untuk reset password Anda: $otp\nKode berlaku 10 menit.";

        $mail->send();
        $message = 'Kode OTP sudah dikirim ke email Anda. Silakan cek inbox email Anda lalu masukkan kode di bawah!';
        $step = 2; // Tampilkan form OTP
        $_SESSION['reset_email'] = $email; // simpan agar tidak perlu input ulang
      } catch (Exception $e) {
        $message = 'Pengiriman email gagal: ' . $mail->ErrorInfo;
        $step = 1;
      }
    } else {
      $message = 'Email tidak terdaftar.';
      $step = 1;
    }
  }
}

// Tahap 2: Submit OTP - lolos OTP baru boleh reset PW
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_otp'])) {
  $email = trim($_POST['email'] ?? '');
  $otp = trim($_POST['otp'] ?? '');
  if (!$email || !$otp) {
    $message = 'Email dan Kode OTP wajib diisi.';
    $step = 2;
  } else {
    $stmt = $conn->prepare("SELECT t.user_id, t.expires_at, t.used FROM reset_tokens t INNER JOIN users u ON t.user_id=u.id_user WHERE u.email=? AND t.otp_code=? ORDER BY t.id DESC LIMIT 1");
    $stmt->bind_param("ss", $email, $otp);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
      if ($row['used']) {
        $message = 'Kode OTP sudah digunakan.';
        $step = 2;
      } elseif (strtotime($row['expires_at']) < time()) {
        $message = 'Kode OTP sudah kedaluwarsa.';
        $step = 2;
      } else {
        // OTP Valid: simpan di session, langsung tampilkan form reset password
        $_SESSION['reset_valid'] = true;
        $_SESSION['reset_user_id'] = $row['user_id'];
        $_SESSION['reset_email'] = $email;
        $_SESSION['reset_otp'] = $otp;
        $step = 3;
      }
    } else {
      $message = 'Kode OTP tidak valid.';
      $step = 2;
    }
  }
}

// Tahap 3: Reset password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_pw'])) {
  if (empty($_SESSION['reset_valid']) || empty($_SESSION['reset_user_id']) || empty($_SESSION['reset_otp']) || empty($_SESSION['reset_email'])) {
    $message = 'Langkah reset tidak valid, silakan ulangi.';
    $step = 1;
  } else {
    $user_id = $_SESSION['reset_user_id'];
    $otp = $_SESSION['reset_otp'];
    $email = $_SESSION['reset_email'];
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';
    if (!$password || !$confirm) {
      $message = 'Semua kolom wajib diisi.';
      $step = 3;
    } elseif ($password !== $confirm) {
      $message = 'Password dan konfirmasi harus sama.';
      $step = 3;
    } elseif (strlen($password) < 6) {
      $message = 'Password minimal 6 karakter.';
      $step = 3;
    } else {
      // Pastikan OTP masih valid, belum digunakan, dan belum kedaluwarsa
      $stmt = $conn->prepare(
        "SELECT t.id, t.used, t.expires_at FROM reset_tokens t WHERE t.user_id=? AND t.otp_code=? ORDER BY t.id DESC LIMIT 1"
      );
      $stmt->bind_param("is", $user_id, $otp);
      $stmt->execute();
      $result = $stmt->get_result();
      if ($row = $result->fetch_assoc()) {
        if ($row['used']) {
          $message = 'Kode OTP sudah digunakan.';
          $step = 1;
        } elseif (strtotime($row['expires_at']) < time()) {
          $message = 'Kode OTP sudah kedaluwarsa.';
          $step = 1;
        } else {
          $hashed = password_hash($password, PASSWORD_DEFAULT);
          $stmt2 = $conn->prepare("UPDATE users SET password=? WHERE id_user=?");
          $stmt2->bind_param("si", $hashed, $user_id);
          $stmt2->execute();
          $stmt3 = $conn->prepare("UPDATE reset_tokens SET used=1 WHERE id=? ");
          $stmt3->bind_param("i", $row['id']);
          $stmt3->execute();
          $message = 'Password berhasil direset. Silakan login kembali.';
          session_unset(); // bersihkan session reset
          $step = 1;
        }
      } else {
        $message = 'Kode OTP tidak valid.';
        $step = 1;
      }
    }
  }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <title>Lupa Password - Majelis MDPL</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@sweetalert2/theme-material-ui/material-ui.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    body {
      min-height: 100vh;
      background: url('assets/bg-lupa-password.jpg') center center/cover no-repeat fixed;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #ffffffff;
    }

    /* GLASS EFFECT / TRANSPARENT CARD */
    .forgot-container {
      background: rgba(255, 255, 255, 0.17);
      border-radius: 18px;
      box-shadow: 0 6px 32px #0002;
      max-width: 370px;
      width: 100%;
      padding: 38px 32px 30px 32px;
      position: relative;
      text-align: center;
      margin-top: 40px;
      backdrop-filter: blur(6px);
      -webkit-backdrop-filter: blur(6px);
      border: 1.5px solid rgba(255, 255, 255, 0.21);
    }

    /* LOGO TRANSPARENT BG */
    .forgot-logo {
      width: 62px;
      height: 62px;
      margin-bottom: 12px;
      background: rgba(255, 255, 255, 0.18);
      border-radius: 50%;
      box-shadow: 0 1px 6px #e2c7fd33;
      object-fit: contain;
    }

    /* FORM INPUT TRANSPARENT */
    .form-control,
    .forgot-input {
      background: rgba(255, 255, 255, 0.40) !important;
      border: 1.5px solid rgba(140, 96, 43, 0.13);
      border-radius: 6px;
      color: #ffffffff !important;
      font-weight: 500;
    }

    .form-control:focus,
    .forgot-input:focus {
      background: rgba(255, 255, 255, 0.70) !important;
      border-color: #b089f4;
      box-shadow: 0 0 0 2px #b089f433;
      color: #ffffffff !important;
    }

    /* JUDUL DAN DESKRIPSI */
    .forgot-title {
      font-weight: 700;
      color: #ffffffff;
      font-size: 1.33em;
      margin-bottom: 8px;
      background: none;
    }

    .forgot-desc {
      font-size: .94em;
      color: #ffffffff;
      margin-bottom: 20px;
      background: none;
    }

    /* BUTTON */
    .forgot-btn {
      background: linear-gradient(90deg, #a97c50 60%, #b089f4 100%);
      color: #fff;
      width: 100%;
      border: 0;
      border-radius: 7px;
      padding: 12px 0;
      font-weight: 600;
      font-size: 1.05em;
      letter-spacing: .5px;
      margin-top: 10px;
      transition: background .3s;
      box-shadow: 0 2px 10px #b089f441;
    }

    .forgot-btn:hover {
      background: linear-gradient(90deg, #b089f4 20%, #a97c50 100%);
      color: #fff;
    }

    /* LINK */
    .login-link {
      margin-top: 21px;
      display: block;
      color: #ffffffff;
      font-size: .96em;
      text-decoration: none;
      transition: color .2s;
    }

    .login-link:hover {
      color: #000000ff;
      text-decoration: underline;
    }

    .mb-3 label {
      font-weight: 500;
      font-size: .98em;
      color: #ffffffff;
      margin-bottom: 4px;
      display: block;
      text-align: left;
      background: none;
    }
  </style>
</head>

<body>
  <div class="forgot-container">
    <img src="assets/logo_majelis_noBg.png" alt="Logo Majelis" class="forgot-logo">
    <div class="forgot-title">Lupa Password</div>
    <div class="forgot-desc">Masukkan email Anda untuk mendapatkan kode OTP dan mengatur ulang password.</div>
    <?php if ($step === 1): ?>
      <!-- Tahap 1 -->
      <form method="post" action="" class="mb-3" autocomplete="off">
        <div class="mb-3">
          <label>Email</label>
          <input type="email" name="email" value="<?= htmlspecialchars($_SESSION['reset_email'] ?? '') ?>" class="form-control forgot-input" placeholder="Email aktif" required>
        </div>
        <button type="submit" name="send_otp" class="forgot-btn"><i class="bi bi-envelope-fill me-1"></i>Kirim OTP</button>
      </form>
    <?php elseif ($step === 2): ?>
      <!-- Tahap 2 -->
      <form method="post" action="" class="mb-3" autocomplete="off">
        <div class="mb-3">
          <label>Email</label>
          <input type="email" name="email" value="<?= htmlspecialchars($_SESSION['reset_email'] ?? '') ?>" class="form-control forgot-input" placeholder="Email aktif" required>
        </div>
        <div class="mb-3">
          <label>Kode OTP</label>
          <input type="text" name="otp" maxlength="6" class="form-control forgot-input" placeholder="Kode OTP dari email" required>
        </div>
        <button type="submit" name="verify_otp" class="forgot-btn"><i class="bi bi-shield-lock me-1"></i>Verifikasi OTP</button>
      </form>
    <?php elseif ($step === 3): ?>
      <!-- Tahap 3 -->
      <form method="post" action="" class="mb-3" autocomplete="off">
        <div class="mb-3">
          <label>Password Baru</label>
          <input type="password" name="password" class="form-control forgot-input" placeholder="Password baru" required>
        </div>
        <div class="mb-3">
          <label>Konfirmasi Password</label>
          <input type="password" name="confirm" class="form-control forgot-input" placeholder="Konfirmasi password baru" required>
        </div>
        <button type="submit" name="reset_pw" class="forgot-btn"><i class="bi bi-arrow-repeat me-1"></i>Reset Password</button>
      </form>
    <?php endif; ?>
    <a href="/majelismdpl.com/index.php" class="login-link">← Kembali ke Login</a>
  </div>
  <!-- Popup SweetAlert2 jika ada pesan -->
  <?php if (isset($message) && $message): ?>
    <script>
      document.addEventListener("DOMContentLoaded", function() {
        // Deteksi jika pesan adalah untuk OTP success
        let isOtpSent = <?= json_encode(strpos($message, "Kode OTP sudah dikirim") !== false) ?>;
        let isSuccess = isOtpSent || <?= json_encode(strpos($message, "Password berhasil") !== false) ?>;
        Swal.fire({
          icon: isSuccess ? "success" : "error",
          title: isOtpSent ? "Kode OTP Berhasil Dikirim" : (isSuccess ? "Berhasil" : "Oops!"),
          text: "<?= htmlspecialchars(strip_tags($message)) ?>",
          confirmButtonText: 'OK'
        }).then((result) => {
          if (isSuccess && !isOtpSent) {
            window.location.href = "/majelismdpl.com";
          }
          // OTP success: tetap di halaman, tidak redirect
        });
      });
    </script>
  <?php endif; ?>



  <!-- Icon library for Bootstrap (optional, for icon inside buttons) -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</body>

</html>