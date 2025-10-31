<?php
header('Content-Type: application/json');
require_once 'koneksi.php';

$action = $_GET['action'] ?? '';

// Array of field names to track and their human-readable labels
$fieldsToTrack = [
    'nama_gunung' => 'Nama Gunung', 
    'tanggal' => 'Tanggal Trip', 
    'slot' => 'Slot', 
    'durasi' => 'Durasi', 
    'jenis_trip' => 'Jenis Trip', 
    'harga' => 'Harga', 
    'via_gunung' => 'Via', 
    'status' => 'Status'
    // 'gambar' tidak dimasukkan karena log update gambar biasanya terpisah
];


if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action) {
    // Pastikan session dimulai untuk semua POST action yang memerlukan id_user
    if (!isset($_SESSION)) {
        session_start();
    }
    $id_user = $_SESSION['id_user'] ?? null; // Fallback jika session hilang

    // DeleteTrip
    if ($action === 'deleteTrip' && isset($_POST['id_trip'])) {
        // ... (Kode DeleteTrip tidak diubah) ...
        $id_trip_del = $_POST['id_trip'];

        // Dapatkan nama gunung untuk keterangan aktivitas
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

        // Tambahkan ke activity_logs jika proses delete trip berhasil
        if ($success) {
            $aktivitas = "Trip \"{$nama_gunung_del}\" dihapus";
            $statusLog = "Delete";
            if ($id_user) {
                $logStmt = $conn->prepare(
                    "INSERT INTO activity_logs (aktivitas, waktu, status, id_user) VALUES (?, NOW(), ?, ?)"
                );
                $logStmt->bind_param("ssi", $aktivitas, $statusLog, $id_user);
                $logStmt->execute();
                $logStmt->close();
            }
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
        // ... (Kode AddTrip tidak diubah) ...
        
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
            $statusLog = "Create"; // Ganti Publish jadi Create untuk konsistensi filter
            if ($id_user) {
                $logStmt = $conn->prepare(
                    "INSERT INTO activity_logs (aktivitas, waktu, status, id_user) VALUES (?, NOW(), ?, ?)"
                );
                $logStmt->bind_param("ssi", $aktivitas, $statusLog, $id_user);
                $logStmt->execute();
                $logStmt->close();
            }
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
        $id_trip_update = $_POST['id_trip'];

        // ==========================================================
        // LANGKAH 1: AMBIL DATA LAMA SEBELUM UPDATE
        // ==========================================================
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
        // ==========================================================
        
        // Cek gambar lama jika tidak ada upload baru
        if ($gambarPath === '') {
            $gambarPath = $oldData['gambar'];
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
            $id_trip_update
        );
        $success = $stmt->execute();
        
        if (!$success) {
            echo json_encode(['success' => false, 'msg' => $stmt->error]);
            $stmt->close();
            exit;
        }
        $stmt->close();
        
        // ==========================================================
        // LANGKAH 2 & 3: BANDINGKAN DATA DAN BUAT LOG DETAIL
        // ==========================================================
        $changedDetails = [];
        
        // Data baru dari POST untuk perbandingan
        $newDataFromPost = [
            'nama_gunung' => $nama_gunung,
            'tanggal' => $tanggal,
            'slot' => $slot,
            'durasi' => $durasi,
            'jenis_trip' => $jenis_trip,
            'harga' => $harga,
            'via_gunung' => $via_gunung,
            'status' => $status,
            'gambar' => $gambarPath // Termasuk gambar jika diupload/dipertahankan
        ];

        foreach ($fieldsToTrack as $fieldKey => $fieldLabel) {
            $oldValue = (string)($oldData[$fieldKey] ?? '');
            $newValue = (string)($newDataFromPost[$fieldKey] ?? '');
            
            // Perbandingan: Cek apakah nilai lama berbeda dengan nilai baru
            if ($oldValue !== $newValue) {
                // Log perubahan. Ganti label 'status' jika diperlukan.
                $label = ($fieldKey === 'status') ? 'Status Trip' : $fieldLabel;
                
                // Format: Label: NilaiLama -> NilaiBaru
                $changedDetails[] = "{$label}: {$oldValue} -> {$newValue}";
            }
        }

        // Tambah log aktivitas jika ada perubahan (termasuk jika hanya gambar yang berubah)
        if (!empty($changedDetails)) {
            $detailsString = implode(', ', $changedDetails);
            
            // Format Log Akhir: Trip "NamaTrip" diupdate: [Detail Perubahan]
            $aktivitas = "Trip \"{$nama_gunung}\" diupdate: {$detailsString}";
            $statusLog = "Update";

            if ($id_user) {
                $logStmt = $conn->prepare("INSERT INTO activity_logs (aktivitas, waktu, status, id_user) VALUES (?, NOW(), ?, ?)");
                $logStmt->bind_param("ssi", $aktivitas, $statusLog, $id_user);
                $logStmt->execute();
                $logStmt->close();
            }
        }
        // ==========================================================

        // Ambil data trip terbaru yang diupdate
        $q = $conn->prepare("SELECT * FROM paket_trips WHERE id_trip = ?");
        $q->bind_param("i", $id_trip_update);
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

// ... (GET methods di bawahnya tidak diubah) ...
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