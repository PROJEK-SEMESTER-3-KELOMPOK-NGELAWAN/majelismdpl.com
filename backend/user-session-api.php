<?php
require_once 'koneksi.php';
session_start();
if(!isset($_SESSION['id_user'])){
    echo json_encode(['logged_in'=>false]); exit;
}
$id_user = $_SESSION['id_user'];
$stmt = $conn->prepare("SELECT username,email,alamat,no_wa FROM users WHERE id_user=?");
$stmt->bind_param("i",$id_user);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();
$stmt->close();
echo json_encode(['logged_in'=>true,'user'=>$res]);
?>
