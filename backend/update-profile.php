<?php
// backend/update-profile.php

require_once 'koneksi.php';
session_start();

// Redirect URL setelah proses selesai
$redirect_url = '../user/profile.php';

// Cek apakah user sudah login
if (!isset($_SESSION['id_user'])) {
    $_SESSION['pesan_error'] = "Silakan login untuk mengakses halaman ini.";
    header('Location: ../login.php');
    exit();
}

$id_user = $_SESSION['id_user'];

// Pastikan request adalah POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    $_SESSION['pesan_error'] = "Metode request tidak valid.";
    header('Location: ' . $redirect_url);
    exit();
}

// Cek field mana yang ingin diupdate (diterima dari hidden input field_to_update)
$field_to_update = isset($_POST['field_to_update']) ? $_POST['field_to_update'] : '';
$value = '';
$sql = '';

// Validasi dan set query berdasarkan field yang diterima
switch ($field_to_update) {
    case 'username':
        $value = trim($_POST['username'] ?? '');
        if (empty($value) || strlen($value) < 3) {
            $_SESSION['pesan_error'] = "Nama Pengguna minimal 3 karakter.";
            header('Location: ' . $redirect_url);
            exit();
        }
        $sql = "UPDATE users SET username = ? WHERE id_user = ?";
        break;

    case 'email':
        $value = trim($_POST['email'] ?? '');
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['pesan_error'] = "Format Email tidak valid.";
            header('Location: ' . $redirect_url);
            exit();
        }
        $sql = "UPDATE users SET email = ? WHERE id_user = ?";
        break;
        
    case 'no_wa':
        $value = trim($_POST['no_wa'] ?? '');
        // Hapus karakter non-digit dan pastikan diawali '0' atau '+62'
        $value = preg_replace('/[^0-9+]/', '', $value);
        if (empty($value)) {
            $_SESSION['pesan_error'] = "Nomor WhatsApp tidak boleh kosong.";
            header('Location: ' . $redirect_url);
            exit();
        }
        $sql = "UPDATE users SET no_wa = ? WHERE id_user = ?";
        break;

    case 'alamat':
        $value = trim($_POST['alamat'] ?? '');
        if (empty($value)) {
             $_SESSION['pesan_error'] = "Alamat tidak boleh kosong.";
            header('Location: ' . $redirect_url);
            exit();
        }
        $sql = "UPDATE users SET alamat = ? WHERE id_user = ?";
        break;
        
    default:
        $_SESSION['pesan_error'] = "Permintaan update tidak valid.";
        header('Location: ' . $redirect_url);
        exit();
}

// Eksekusi Query
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $value, $id_user);

if ($stmt->execute()) {
    // Jika update username, update juga sesi username agar navbar/header langsung berubah
    if ($field_to_update == 'username') {
        $_SESSION['username'] = $value;
    }
    $_SESSION['pesan_sukses'] = ucfirst($field_to_update) . " berhasil diperbarui.";
} else {
    // Error jika terjadi masalah pada query
    $_SESSION['pesan_error'] = "Gagal memperbarui " . strtolower($field_to_update) . ": " . $stmt->error;
}

$stmt->close();
$conn->close();

header('Location: ' . $redirect_url);
exit();
?>