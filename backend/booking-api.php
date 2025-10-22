<?php
require_once "koneksi.php";
session_start();
header('Content-Type: application/json');
ini_set('display_errors', 1);
error_reporting(E_ALL);

$id_user = $_SESSION['id_user'] ?? null;
if (!$id_user) {
    echo json_encode(['success' => false, 'message' => 'Login diperlukan']);
    exit;
}

$id_trip = intval($_POST['id_trip'] ?? 0);
$jumlah_peserta = intval($_POST['jumlah_peserta'] ?? 1);
foreach (['nama', 'email', 'tanggal_lahir', 'tempat_lahir', 'nik', 'alamat', 'no_wa', 'no_wa_darurat', 'riwayat_penyakit'] as $f) {
    if (!isset($_POST[$f]) || !is_array($_POST[$f])) {
        echo json_encode(['success' => false, 'message' => 'Data peserta tidak valid']);
        exit;
    }
}

function uploadKTP($file, $idx)
{
    if (isset($file['name'][$idx]) && $file['error'][$idx] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        $maxSize = 5 * 1024 * 1024;
        if (!in_array($file['type'][$idx], $allowedTypes)) return '';
        if ($file['size'][$idx] > $maxSize) return '';
        $ext = pathinfo($file['name'][$idx], PATHINFO_EXTENSION);
        $fileName = 'ktp_' . time() . '_' . rand(10, 99) . '.' . $ext;
        $upDir = '../uploads/ktp/';
        if (!is_dir($upDir)) mkdir($upDir, 0755, true);
        $target = $upDir . $fileName;
        if (move_uploaded_file($file['tmp_name'][$idx], $target)) return 'uploads/ktp/' . $fileName;
    }
    return '';
}

$tripQ = $conn->query("SELECT slot, harga, status FROM paket_trips WHERE id_trip=$id_trip");
$trip = $tripQ->fetch_assoc();
if (!$trip) {
    echo json_encode(['success' => false, 'message' => 'Trip tidak ditemukan']);
    exit;
}
if ($trip['status'] != 'available' || intval($trip['slot']) < $jumlah_peserta) {
    echo json_encode(['success' => false, 'message' => 'Peserta Sudah Penuh']);
    exit;
}

$nama = $_POST['nama'];
$email = $_POST['email'];
$tanggal_lahir = $_POST['tanggal_lahir'];
$tempat_lahir = $_POST['tempat_lahir'];
$nik = $_POST['nik'];
$alamat = $_POST['alamat'];
$no_wa = $_POST['no_wa'];
$no_wa_darurat = $_POST['no_wa_darurat'];
$riwayat_penyakit = $_POST['riwayat_penyakit'];
$foto_ktp_files = $_FILES['foto_ktp'] ?? null;
$participant_ids = [];

// Simpan data peserta dulu
for ($i = 0; $i < $jumlah_peserta; $i++) {
    $ktpPath = '';
    if ($foto_ktp_files) $ktpPath = uploadKTP($foto_ktp_files, $i);
    $stmt = $conn->prepare("INSERT INTO participants (nama,email,tanggal_lahir,tempat_lahir,nik,alamat,no_wa,no_wa_darurat,riwayat_penyakit,foto_ktp) VALUES (?,?,?,?,?,?,?,?,?,?)");
    $stmt->bind_param("ssssssssss", $nama[$i], $email[$i], $tanggal_lahir[$i], $tempat_lahir[$i], $nik[$i], $alamat[$i], $no_wa[$i], $no_wa_darurat[$i], $riwayat_penyakit[$i], $ktpPath);
    if (!$stmt->execute()) {
        echo json_encode(['success' => false, 'message' => 'Gagal simpan peserta: ' . $conn->error]);
        exit;
    }
    $participant_ids[] = $conn->insert_id;
    $stmt->close();
}

$total_harga = $trip['harga'] * $jumlah_peserta;
$tgl = date('Y-m-d');
$status = "pending";

$stmt2 = $conn->prepare("INSERT INTO bookings (id_user,id_trip,jumlah_orang,total_harga,tanggal_booking,status) VALUES (?,?,?,?,?,?)");
$stmt2->bind_param("iiisss", $id_user, $id_trip, $jumlah_peserta, $total_harga, $tgl, $status);

if (!$stmt2->execute()) {
    echo json_encode(['success' => false, 'message' => 'Gagal booking: ' . $conn->error]);
    exit;
}
$id_booking = $conn->insert_id;
$stmt2->close();

// Update peserta supaya punya id_booking supaya relasi lengkap
if (!empty($participant_ids)) {
    $ids_str = implode(',', $participant_ids);
    $conn->query("UPDATE participants SET id_booking = $id_booking WHERE id_participant IN ($ids_str)");
}


// Update slot dan status trip
$conn->query("UPDATE paket_trips SET slot=slot-$jumlah_peserta WHERE id_trip=$id_trip AND slot>=$jumlah_peserta");
$sisaSlotQ = $conn->query("SELECT slot FROM paket_trips WHERE id_trip=$id_trip");
$sisaSlot = $sisaSlotQ->fetch_assoc()['slot'];
if ($sisaSlot <= 0) {
    $conn->query("UPDATE paket_trips SET status='sold' WHERE id_trip=$id_trip");
}

echo json_encode(['success' => true, 'id_booking' => $id_booking, 'message' => 'Pendaftaran berhasil, silakan lanjut ke pembayaran.']);
