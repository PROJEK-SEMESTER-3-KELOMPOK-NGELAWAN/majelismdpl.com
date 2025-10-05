<?php
session_start();

require_once __DIR__ . '/helpers/RoleHelper.php';

if (!isset($_SESSION['id_user']) || !isset($_SESSION['role'])) {
    header('Location: ../index.php?error=unauthorized');
    exit();
}

if (!RoleHelper::isAdmin($_SESSION['role'])) {
    header('Location: ../index.php?error=unauthorized');
    exit();
}

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
    session_destroy();
    header('Location: ../index.php?error=session_expired');
    exit();
}

$_SESSION['last_activity'] = time();

$user_role = $_SESSION['role'];
$is_super_admin = RoleHelper::isSuperAdmin($user_role);
$is_admin = RoleHelper::isAdmin($user_role);
$user_id = $_SESSION['id_user'];
$username = $_SESSION['username'];
?>
