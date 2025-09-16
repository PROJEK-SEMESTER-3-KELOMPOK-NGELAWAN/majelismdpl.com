<?php
header('Content-Type: application/json');
require_once 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data form (pastikan name di form: username, password, email, no_wa, alamat)
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $email    = trim($_POST['email'] ?? '');
    $no_wa    = trim($_POST['no_wa'] ?? '');
    $alamat   = trim($_POST['alamat'] ?? '');

    // Validasi sederhana
    if (!$username || !$password || !$email || !$no_wa || !$alamat) {
        echo json_encode(['success'=>false,'message'=>'Semua kolom wajib diisi']);
        exit;
    }

    // Hash password
    $hashed = password_hash($password, PASSWORD_DEFAULT); // bcrypt [web:3][web:6]

    // Siapkan prepared statement
    $stmt = $conn->prepare(
        "INSERT INTO users (username, password, role, email, no_wa, alamat) VALUES (?, ?, 'user', ?, ?, ?)"
    );
    if (!$stmt) {
        echo json_encode(['success'=>false,'message'=>'Statement error: '.$conn->error]);
        exit;
    }

    $stmt->bind_param("sssss", $username, $hashed, $email, $no_wa, $alamat);

    // Eksekusi & respon
    if ($stmt->execute()) {
        echo json_encode(['success'=>true,'message'=>'Registrasi berhasil']);
    } else {
        $err = ($conn->errno == 1062) ? 'Username/email sudah terdaftar' : $conn->error;
        echo json_encode(['success'=>false,'message'=>"Gagal: $err"]);
    }
    $stmt->close();
} else {
    echo json_encode(['success'=>false,'message'=>'Metode tidak diizinkan']);
}
?>
