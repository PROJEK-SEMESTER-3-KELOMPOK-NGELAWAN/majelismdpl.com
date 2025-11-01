<?php
header('Content-Type: application/json');
require_once 'koneksi.php';

$action = $_GET['action'] ?? '';

$fieldsToTrack = [
    'nama_gunung' => 'Nama Gunung',
    'tanggal'     => 'Tanggal Trip',
    'slot'        => 'Slot',
    'durasi'      => 'Durasi',
    'jenis_trip'  => 'Jenis Trip',
    'harga'       => 'Harga',
    'via_gunung'  => 'Via',
    'status'      => 'Status'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action) {
    if (!isset($_SESSION)) {
        session_start();
    }
    $id_user = $_SESSION['id_user'] ?? null;

    // Hapus trip
    if ($action === 'deleteTrip' && isset($_POST['id_trip'])) {
        $id_trip_del = (int)$_POST['id_trip'];

        $q = $conn->prepare("SELECT nama_gunung FROM paket_trips WHERE id_trip=?");
        $q->bind_param("i", $id_trip_del);
        $q->execute();
        $q->bind_result($nama_gunung_del);
        $q->fetch();
        $q->close();

        $stmt = $conn->prepare("DELETE FROM paket_trips WHERE id_trip=?");
        $stmt->bind_param("i", $id_trip_del);
        $success = $stmt->execute();
        $stmt->close();

        if ($success && $id_user) {
            $aktivitas = "Trip \"{$nama_gunung_del}\" dihapus";
            $statusLog = "Delete";
            $logStmt = $conn->prepare("INSERT INTO activity_logs (aktivitas, waktu, status, id_user) VALUES (?, NOW(), ?, ?)");
            $logStmt->bind_param("ssi", $aktivitas, $statusLog, $id_user);
            $logStmt->execute();
            $logStmt->close();
        }

        echo json_encode(['success' => $success]);
        exit;
    }

    // Ambil input POST
    $nama_gunung = $_POST['nama_gunung'] ?? '';
    $tanggal     = $_POST['tanggal'] ?? '';
    $slot        = (int)($_POST['slot'] ?? 0);
    $durasi      = $_POST['durasi'] ?? '';
    $jenis_trip  = $_POST['jenis_trip'] ?? '';
    $harga       = (int)($_POST['harga'] ?? 0);
    $via_gunung  = $_POST['via_gunung'] ?? '';
    // Validasi status: tambahkan 'done'
    $status = isset($_POST['status']) ? strtolower(trim($_POST['status'])) : 'available';
    $allowedStatuses = ['available', 'sold', 'done'];
    if (!in_array($status, $allowedStatuses, true)) {
        http_response_code(422);
        echo json_encode(['success' => false, 'msg' => 'Status tidak valid (allowed: available, sold, done)']);
        exit;
    }

    $gambarPath = '';

    // Upload gambar
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
        $targetDir = "../img/";
        if (!is_dir($targetDir)) {
            @mkdir($targetDir, 0775, true);
        }
        $fileName = date('YmdHis') . '_' . basename($_FILES['gambar']['name']);
        $targetFilePath = $targetDir . $fileName;
        if (move_uploaded_file($_FILES['gambar']['tmp_name'], $targetFilePath)) {
            $gambarPath = 'img/' . $fileName;
        }
    }

    if ($action === 'addTrip') {
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

        if ($id_user) {
            $aktivitas = "Trip \"{$nama_gunung}\" ditambahkan";
            $statusLog = "Create";
            $logStmt = $conn->prepare("INSERT INTO activity_logs (aktivitas, waktu, status, id_user) VALUES (?, NOW(), ?, ?)");
            $logStmt->bind_param("ssi", $aktivitas, $statusLog, $id_user);
            $logStmt->execute();
            $logStmt->close();
        }

        $q = $conn->prepare("SELECT * FROM paket_trips WHERE id_trip = ?");
        $q->bind_param("i", $id);
        $q->execute();
        $result = $q->get_result();
        $newTrip = $result->fetch_assoc();
        $q->close();

        echo json_encode(['success' => true, 'data' => $newTrip]);
        exit;
    }

    if ($action === 'updateTrip' && isset($_POST['id_trip'])) {
        $id_trip_update = (int)$_POST['id_trip'];

        $qOld = $conn->prepare("SELECT * FROM paket_trips WHERE id_trip = ?");
        $qOld->bind_param("i", $id_trip_update);
        $qOld->execute();
        $resultOld = $qOld->get_result();
        $oldData = $resultOld->fetch_assoc();
        $qOld->close();

        if (!$oldData) {
            http_response_code(404);
            echo json_encode(['success' => false, 'msg' => 'Trip tidak ditemukan.']);
            exit;
        }

        if ($gambarPath === '') {
            $gambarPath = $oldData['gambar'];
        }

        $stmt = $conn->prepare(
            "UPDATE paket_trips SET nama_gunung=?, tanggal=?, slot=?, durasi=?, jenis_trip=?, harga=?, via_gunung=?, status=?, gambar=? WHERE id_trip=?"
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
            $id_trip_update
        );
        $success = $stmt->execute();
        if (!$success) {
            echo json_encode(['success' => false, 'msg' => $stmt->error]);
            $stmt->close();
            exit;
        }
        $stmt->close();

        $changedDetails = [];
        $newDataFromPost = [
            'nama_gunung' => $nama_gunung,
            'tanggal'     => $tanggal,
            'slot'        => $slot,
            'durasi'      => $durasi,
            'jenis_trip'  => $jenis_trip,
            'harga'       => $harga,
            'via_gunung'  => $via_gunung,
            'status'      => $status,
            'gambar'      => $gambarPath
        ];

        foreach ($fieldsToTrack as $fieldKey => $fieldLabel) {
            $oldValue = (string)($oldData[$fieldKey] ?? '');
            $newValue = (string)($newDataFromPost[$fieldKey] ?? '');
            if ($oldValue !== $newValue) {
                $label = ($fieldKey === 'status') ? 'Status Trip' : $fieldLabel;
                $changedDetails[] = "{$label}: {$oldValue} -> {$newValue}";
            }
        }

        if (!empty($changedDetails) && $id_user) {
            $detailsString = implode(', ', $changedDetails);
            $aktivitas = "Trip \"{$nama_gunung}\" diupdate: {$detailsString}";
            $statusLog = "Update";
            $logStmt = $conn->prepare("INSERT INTO activity_logs (aktivitas, waktu, status, id_user) VALUES (?, NOW(), ?, ?)");
            $logStmt->bind_param("ssi", $aktivitas, $statusLog, $id_user);
            $logStmt->execute();
            $logStmt->close();
        }

        $q = $conn->prepare("SELECT * FROM paket_trips WHERE id_trip = ?");
        $q->bind_param("i", $id_trip_update);
        $q->execute();
        $result = $q->get_result();
        $updatedTrip = $result->fetch_assoc();
        $q->close();

        echo json_encode(['success' => true, 'data' => $updatedTrip]);
        exit;
    }

    http_response_code(400);
    echo json_encode(['success' => false, 'msg' => 'Aksi tidak dikenal']);
    exit;
}

// GET list
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'getTrips') {
    $result = $conn->query("SELECT * FROM paket_trips ORDER BY id_trip DESC");
    $trips = [];
    while ($row = $result->fetch_assoc()) {
        $trips[] = $row;
    }
    echo json_encode($trips);
    exit;
}

// GET single
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'getTrip' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $q = $conn->prepare("SELECT * FROM paket_trips WHERE id_trip = ?");
    $q->bind_param("i", $id);
    $q->execute();
    $result = $q->get_result();
    $trip = $result->fetch_assoc();
    $q->close();
    echo json_encode(['success' => (bool)$trip, 'data' => $trip]);
    exit;
}
