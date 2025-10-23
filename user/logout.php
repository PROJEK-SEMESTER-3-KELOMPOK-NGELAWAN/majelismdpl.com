<?php
// logout.php (di root folder project)
session_start();

// Hanya proses logout jika ada konfirmasi POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_logout'])) {
    // Hapus semua session
    $_SESSION = array();
    
    // Hapus cookie session jika ada
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time()-3600, '/');
    }
    
    // Hancurkan session
    session_destroy();
    
    // Redirect ke halaman utama
    header("Location: index.php");
    exit();
} else {
    // Jika akses langsung tanpa POST, redirect ke halaman utama
    header("Location: index.php");
    exit();
}
?>
