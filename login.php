<?php
session_start();

$admin_user = "admin";
$admin_pass = "12345";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $username = $_POST["username"];
  $password = $_POST["password"];

  if ($username === $admin_user && $password === $admin_pass) {
    $_SESSION["admin"] = $username;
    header("Location: admin/index.php");
    exit();
  } else {
    echo "<script>alert('Username atau password salah!'); window.history.back();</script>";
  }
}
?>
