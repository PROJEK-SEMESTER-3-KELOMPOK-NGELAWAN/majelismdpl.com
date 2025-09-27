<?php
header('Content-Type: application/json');
require_once 'koneksi.php';

$action = $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action) {
    // DeleteTrip
    if ($action === 'deleteTrip' && isset($_POST['id_trip'])) {
        session_start(); // Pastikan session dibuka jika file ini belum otomatis memulai session
        $id_user = $_SESSION['id_user']; // Ambil user pelaku dari session

        // Dapatkan nama gunung untuk keterangan aktivitas
        $q = $conn->prepare("SELECT nama_gunung FROM paket_trips WHERE id_trip=?");
        $q->bind_param("i", $_POST['id_trip']);
        $q->execute();
        $q->bind_result($nama_gunung_del);
        $q->fetch();
        $q->close();

        $stmt = $conn->prepare("DELETE FROM paket_trips WHERE id_trip=?");
        $stmt->bind_param("i", $_POST['id_trip']);
        $success = $stmt->execute();
        $stmt->close();

        // Tambahkan ke activity_logs jika proses delete trip berhasil
        if ($success) {
            $aktivitas = "Trip \"{$nama_gunung_del}\" dihapus";
            $statusLog = "Delete";
            $logStmt = $conn->prepare(
                "INSERT INTO activity_logs (aktivitas, waktu, status, id_user) VALUES (?, NOW(), ?, ?)"
            );
            $logStmt->bind_param("ssi", $aktivitas, $statusLog, $id_user);
            $logStmt->execute();
            $logStmt->close();
        }

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
        session_start(); // aktifkan session (jika belum di awal file)
        $id_user = $_SESSION['id_user']; // ambil dari session login admin

        $stmt = $conn->prepare(
            "INSERT INTO paket_trips (nama_gunung, tanggal, slot, durasi, jenis_trip, harga, via_gunung, status, gambar)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param(
            "ssissssss",
            $nama_gunung,
            $tanggal,
            $slot,
            $durasi,
            $jenis_trip,
            $harga,
            $via_gunung,
            $status,
            $gambarPath
        );
        $success = $stmt->execute();
        if (!$success) {
            echo json_encode(['success' => false, 'msg' => $stmt->error]);
            $stmt->close();
            exit;
        }
        $id = $stmt->insert_id;
        $stmt->close();

        // Tambahkan ke activity_logs jika proses insert trip berhasil
        if ($success) {
            $aktivitas = "Trip \"{$nama_gunung}\" ditambahkan";
            $statusLog = "Publish";
            $logStmt = $conn->prepare(
                "INSERT INTO activity_logs (aktivitas, waktu, status, id_user) VALUES (?, NOW(), ?, ?)"
            );
            $logStmt->bind_param("ssi", $aktivitas, $statusLog, $id_user);
            $logStmt->execute();
            $logStmt->close();
        }

        // Ambil data trip terbaru
        $q = $conn->prepare("SELECT * FROM paket_trips WHERE id_trip = ?");
        $q->bind_param("i", $id);
        $q->execute();
        $result = $q->get_result();
        $newTrip = $result->fetch_assoc();
        $q->close();

        echo json_encode(['success' => true, 'data' => $newTrip]);
        exit;
    }


    // UpdateTrip
    if ($action === 'updateTrip' && isset($_POST['id_trip'])) {
        session_start(); // pastikan session dibuka
        $id_user = $_SESSION['id_user']; // ambil dari session admin login

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
            $nama_gunung,
            $tanggal,
            $slot,
            $durasi,
            $jenis_trip,
            $harga,
            $via_gunung,
            $status,
            $gambarPath,
            $_POST['id_trip']
        );
        $success = $stmt->execute();
        if (!$success) {
            echo json_encode(['success' => false, 'msg' => $stmt->error]);
            $stmt->close();
            exit;
        }
        $stmt->close();

        // Ambil data nama trip yg baru diupdate untuk log aktivitas
        $q = $conn->prepare("SELECT nama_gunung FROM paket_trips WHERE id_trip = ?");
        $q->bind_param("i", $_POST['id_trip']);
        $q->execute();
        $q->bind_result($nama_gunung_log);
        $q->fetch();
        $q->close();

        // Tambah log aktivitas jika update berhasil
        if ($success) {
            $aktivitas = "Trip \"{$nama_gunung_log}\" diupdate";
            $statusLog = "Update";
            $logStmt = $conn->prepare("INSERT INTO activity_logs (aktivitas, waktu, status, id_user) VALUES (?, NOW(), ?, ?)");
            $logStmt->bind_param("ssi", $aktivitas, $statusLog, $id_user);
            $logStmt->execute();
            $logStmt->close();
        }

        // Ambil data trip terbaru yang diupdate
        $q = $conn->prepare("SELECT * FROM paket_trips WHERE id_trip = ?");
        $q->bind_param("i", $_POST['id_trip']);
        $q->execute();
        $result = $q->get_result();
        $updatedTrip = $result->fetch_assoc();
        $q->close();

        echo json_encode(['success' => true, 'data' => $updatedTrip]);
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

if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'getTrip' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $q = $conn->prepare("SELECT * FROM paket_trips WHERE id_trip = ?");
    $q->bind_param("i", $id);
    $q->execute();
    $result = $q->get_result();
    $trip = $result->fetch_assoc();
    $q->close();

    // Kirim data, jika tidak ditemukan akan bernilai null
    echo json_encode(['success' => (bool)$trip, 'data' => $trip]);
    exit;
}
