<?php
session_start();

// Hanya proses logout jika ada konfirmasi POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_logout'])) {
    session_destroy();
    header("Location: ../index.php");
    exit();
} else {
    // Jika akses langsung, redirect ke dashboard
    header("Location: index.php");
    exit();
}
?>
