<?php
header('Content-Type: application/json');
require_once 'koneksi.php';

$action = $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action) {

    // DeleteTrip
    if ($action === 'deleteTrip' && isset($_POST['id_trip'])) {
        $stmt = $conn->prepare("DELETE FROM paket_trips WHERE id_trip=?");
        $stmt->bind_param("i", $_POST['id_trip']);
        $success = $stmt->execute();
        $stmt->close();
        echo json_encode(['success' => $success]);
        exit;
    }

    // Untuk addTrip dan updateTrip
    $nama_gunung = $_POST['nama_gunung'];
    $tanggal = $_POST['tanggal'];
    $slot = $_POST['slot'];
    $durasi = $_POST['durasi'];
    $jenis_trip = $_POST['jenis_trip'];
    $harga = $_POST['harga'];
    $via_gunung = $_POST['via_gunung'];
    $status = $_POST['status'];
    $gambarPath = '';

    // Handle upload gambar jika ada
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
        $targetDir = "../img/";
        $fileName = date('YmdHis') . '_' . basename($_FILES['gambar']['name']);
        $targetFilePath = $targetDir . $fileName;
        if (move_uploaded_file($_FILES['gambar']['tmp_name'], $targetFilePath)) {
            $gambarPath = 'img/' . $fileName;
        }
    }


    // AddTrip
    if ($action === 'addTrip') {
        $stmt = $conn->prepare(
            "INSERT INTO paket_trips (nama_gunung, tanggal, slot, durasi, jenis_trip, harga, via_gunung, status, gambar)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param(
            "ssissssss",
            $nama_gunung, $tanggal, $slot, $durasi, $jenis_trip, $harga, $via_gunung, $status, $gambarPath
        );
        $success = $stmt->execute();
        if (!$success) {
            echo json_encode(['success' => false, 'msg' => $stmt->error]);
        } else {
            echo json_encode(['success' => true]);
        }
        $stmt->close();
        exit;
    }

    // UpdateTrip
    if ($action === 'updateTrip' && isset($_POST['id_trip'])) {
        // jika tidak upload gambar baru, ambil gambar lama
        if ($gambarPath === '') {
            $q = $conn->prepare("SELECT gambar FROM paket_trips WHERE id_trip=?");
            $q->bind_param("i", $_POST['id_trip']);
            $q->execute();
            $q->bind_result($gambar_old);
            $q->fetch();
            $q->close();
            $gambarPath = $gambar_old;
        }
        $stmt = $conn->prepare(
            "UPDATE paket_trips SET nama_gunung=?, tanggal=?, slot=?, durasi=?, jenis_trip=?, harga=?, via_gunung=?, status=?, gambar=?
             WHERE id_trip=?"
        );
        $stmt->bind_param(
            "ssissssssi",
            $nama_gunung, $tanggal, $slot, $durasi, $jenis_trip, $harga, $via_gunung, $status, $gambarPath, $_POST['id_trip']
        );
        $success = $stmt->execute();
        $stmt->close();
        echo json_encode(['success' => $success]);
        exit;
    }

    // fallback
    http_response_code(400);
    echo json_encode(['success' => false, 'msg' => 'Aksi tidak dikenal']);
    exit;
}

// GET trip list
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'getTrips') {
    $result = $conn->query("SELECT * FROM paket_trips ORDER BY id_trip DESC");
    $trips = [];
    while ($row = $result->fetch_assoc()) {
        $trips[] = $row;
    }
    echo json_encode($trips);
    exit;
}
?>
