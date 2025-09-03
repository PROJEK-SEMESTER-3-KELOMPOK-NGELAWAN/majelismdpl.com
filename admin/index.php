<?php
session_start();
if (!isset($_SESSION["admin"])) {
  header("Location: ../index.php");
  exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Dashboard Admin - Majelis MDPL</title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>
  <h1>Selamat datang di Dashboard, <?= $_SESSION["admin"]; ?> 👋</h1>
  <p>Ini halaman khusus admin Majelis MDPL.</p>
  <a href="logout.php">Logout</a>
</body>
</html>
