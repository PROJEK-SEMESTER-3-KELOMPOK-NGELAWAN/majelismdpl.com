<?php
session_start();

// Cek apakah user sudah login dan memiliki role admin
if (!isset($_SESSION['id_user']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    // Redirect ke halaman utama dengan pesan error
    header('Location: ../index.php?error=unauthorized');
    exit();
}

// Optional: Cek timeout session (misal 30 menit)
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
    session_destroy();
    header('Location: ../index.php?error=session_expired');
    exit();
}

$_SESSION['last_activity'] = time();
?>
