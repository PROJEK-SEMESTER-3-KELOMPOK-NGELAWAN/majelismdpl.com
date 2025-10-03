<?php
session_start();

// Include helper untuk role management
require_once __DIR__ . '/helpers/RoleHelper.php';

// Cek apakah user sudah login
if (!isset($_SESSION['id_user']) || !isset($_SESSION['role'])) {
    header('Location: ../index.php?error=unauthorized');
    exit();
}

// Cek apakah user memiliki minimal role admin
if (!in_array($_SESSION['role'], ['admin', 'super_admin'])) {
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

// Global variables untuk role - mudah diakses di semua halaman admin
$user_role = $_SESSION['role'];
$is_super_admin = RoleHelper::isSuperAdmin($user_role);
$is_admin = RoleHelper::isAdmin($user_role);
$user_id = $_SESSION['id_user'];
$username = $_SESSION['username'];
?>
