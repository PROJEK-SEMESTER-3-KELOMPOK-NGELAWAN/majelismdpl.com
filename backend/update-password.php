<?php
// backend/update-password.php

require_once '../config.php';
require_once 'koneksi.php';
session_start();

// Redirect URL setelah proses selesai
$redirect_url = getPageUrl('user/profile.php');

// Cek apakah user sudah login
if (!isset($_SESSION['id_user'])) {
    $_SESSION['pesan_error'] = "Silakan login untuk mengakses halaman ini.";
    header('Location: ' . getPageUrl('index.php'));
    exit();
}

$id_user = $_SESSION['id_user'];

// Pastikan request adalah POST dan field_to_update adalah password
if ($_SERVER["REQUEST_METHOD"] !== "POST" || ($_POST['field_to_update'] ?? '') !== 'password') {
    $_SESSION['pesan_error'] = "Permintaan tidak valid.";
    header('Location: ' . $redirect_url);
    exit();
}

$current_password = $_POST['current_password'] ?? '';
$new_password = $_POST['new_password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// === Validasi Input ===
if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
    $_SESSION['pesan_error'] = "Semua field kata sandi harus diisi.";
    header('Location: ' . $redirect_url);
    exit();
}

if ($new_password !== $confirm_password) {
    $_SESSION['pesan_error'] = "Kata sandi baru dan konfirmasi tidak cocok.";
    header('Location: ' . $redirect_url);
    exit();
}

if (strlen($new_password) < 6) {
    $_SESSION['pesan_error'] = "Kata sandi baru minimal 6 karakter.";
    header('Location: ' . $redirect_url);
    exit();
}

// 1. Ambil hash password lama dari database
$stmt_select = $conn->prepare("SELECT password FROM users WHERE id_user = ?");
$stmt_select->bind_param("i", $id_user);
$stmt_select->execute();
$result_select = $stmt_select->get_result();

if ($result_select->num_rows === 0) {
    $_SESSION['pesan_error'] = "Akun tidak ditemukan.";
    $stmt_select->close();
    header('Location: ' . $redirect_url);
    exit();
}

$user = $result_select->fetch_assoc();
$hashed_password_db = $user['password'];
$stmt_select->close();

// 2. Verifikasi kata sandi lama
if (!password_verify($current_password, $hashed_password_db)) {
    $_SESSION['pesan_error'] = "Kata sandi lama yang Anda masukkan salah.";
    header('Location: ' . $redirect_url);
    exit();
}

// 3. Hash kata sandi baru dan update
$new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

$stmt_update = $conn->prepare("UPDATE users SET password = ? WHERE id_user = ?");
$stmt_update->bind_param("si", $new_hashed_password, $id_user);

if ($stmt_update->execute()) {
    $_SESSION['pesan_sukses'] = "Kata sandi berhasil diubah! Silakan login kembali dengan password baru Anda.";
    
    // Opsional: Hapus sesi agar user login lagi
    session_destroy(); 
    // Redirect ke halaman login setelah berhasil ubah password
    header('Location: ' . getPageUrl('index.php')); 
    
} else {
    $_SESSION['pesan_error'] = "Gagal mengubah kata sandi: " . $stmt_update->error;
    header('Location: ' . $redirect_url);
}

$stmt_update->close();
$conn->close();
exit();
?>
