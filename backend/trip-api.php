<?php

/**
 * ============================================
 * FILE: backend/trip-api.php
 * FUNGSI: Handle CRUD Trip + file management + trip_galleries
 * FIXED: Bug pada updateTrip - variabel $gdrive_link diganti $link_drive
 * ============================================
 */

header('Content-Type: application/json');
require_once 'koneksi.php';

session_start();

$action = $_GET['action'] ?? '';
$id_user = $_SESSION['id_user'] ?? null;

// ========== HELPER FUNCTIONS ==========

/**
 * Validasi path untuk prevent path traversal
 */
function validateFilePath($filename)
{
    if (
        strpos($filename, '..') !== false ||
        strpos($filename, '/') !== false ||
        strpos($filename, '\\') !== false
    ) {
        return false;
    }
    return true;
}

/**
 * Delete file gambar trip - FIXED VERSION 100%
 * Handle semua format path: 'img/xxx', './img/xxx', 'img\xxx'
 */
function deleteGameFile($gambar_path)
{
    if (empty($gambar_path)) {
        return [
            'success' => true,
            'message' => 'Tidak ada file yang perlu dihapus',
            'debug' => 'Path kosong'
        ];
    }

    // Normalize path: ubah backslash ke forward slash
    $gambar_path = str_replace('\\', '/', $gambar_path);

    // Remove leading ./ atau /
    $gambar_path = ltrim($gambar_path, './');

    // Extract hanya filename dari path (ambil bagian terakhir)
    $path_parts = explode('/', $gambar_path);
    $filename = end($path_parts); // Ambil filename terakhir

    // Validasi filename
    if (!$filename || strpos($filename, '..') !== false) {
        return [
            'success' => false,
            'message' => 'Filename tidak valid',
            'debug' => "Invalid filename: $filename"
        ];
    }

    // CONSTRUCT ABSOLUTE PATH - FIXED 100%
    $base_path = dirname(__DIR__); // /path/to/project
    $img_folder = $base_path . DIRECTORY_SEPARATOR . 'img';
    $full_path = $img_folder . DIRECTORY_SEPARATOR . $filename;

    // DEBUG INFO
    $debug_info = [
        'base_path' => $base_path,
        'img_folder' => $img_folder,
        'filename' => $filename,
        'full_path' => $full_path,
        'file_exists' => file_exists($full_path)
    ];

    // Verifikasi file ada
    if (!file_exists($full_path)) {
        return [
            'success' => true,
            'message' => 'File tidak ditemukan di folder (sudah terhapus sebelumnya)',
            'debug' => $debug_info
        ];
    }

    // Gunakan realpath untuk absolute path - CRITICAL
    $real_path = realpath($full_path);
    $real_img_folder = realpath($img_folder);

    if ($real_path === false || $real_img_folder === false) {
        return [
            'success' => false,
            'message' => 'Gagal resolve path file',
            'debug' => [
                'realpath_result' => $real_path,
                'real_img_folder' => $real_img_folder
            ]
        ];
    }

    // Pastikan file berada di dalam folder img (SECURITY CHECK)
    if (strpos($real_path, $real_img_folder) !== 0) {
        return [
            'success' => false,
            'message' => 'Path file berada di luar folder yang diizinkan',
            'debug' => [
                'real_path' => $real_path,
                'real_img_folder' => $real_img_folder
            ]
        ];
    }

    // HAPUS FILE dengan realpath - FINAL STEP
    $result = @unlink($real_path);

    if ($result) {
        return [
            'success' => true,
            'message' => 'File berhasil dihapus',
            'debug' => [
                'deleted_file' => $real_path,
                'filename' => $filename
            ]
        ];
    } else {
        $error = error_get_last();
        $error_msg = $error ? $error['message'] : 'Unknown error';

        return [
            'success' => false,
            'message' => 'Gagal menghapus file: ' . $error_msg,
            'debug' => [
                'attempted_path' => $real_path,
                'filename' => $filename,
                'php_error' => $error_msg,
                'file_still_exists' => file_exists($real_path)
            ]
        ];
    }
}

/**
 * Log activity
 */
function logActivity($conn, $id_user, $aktivitas, $statusLog)
{
    if (!$id_user) return;
    $stmt = $conn->prepare("INSERT INTO activity_logs (aktivitas, waktu, status, id_user) VALUES (?, NOW(), ?, ?)");
    if ($stmt) {
        $stmt->bind_param("ssi", $aktivitas, $statusLog, $id_user);
        $stmt->execute();
        $stmt->close();
    }
}

/**
 * Save or Update Gallery to trip_galleries table
 * UPDATED: galery_name otomatis menggunakan nama_gunung
 */
function saveOrUpdateGallery($conn, $id_trip, $nama_gunung, $gdrive_link)
{
    // Otomatis generate galery_name dari nama_gunung
    $gallery_name = $nama_gunung;

    // Cek apakah sudah ada data gallery untuk id_trip ini
    $checkStmt = $conn->prepare("SELECT id_trip FROM trip_galleries WHERE id_trip = ?");
    $checkStmt->bind_param("i", $id_trip);
    $checkStmt->execute();
    $checkStmt->store_result();

    if ($checkStmt->num_rows > 0) {
        // UPDATE existing
        $checkStmt->close();
        $updateStmt = $conn->prepare("UPDATE trip_galleries SET galery_name = ?, gdrive_link = ? WHERE id_trip = ?");
        $updateStmt->bind_param("ssi", $gallery_name, $gdrive_link, $id_trip);
        $result = $updateStmt->execute();
        $updateStmt->close();
        return $result;
    } else {
        // INSERT new
        $checkStmt->close();
        $insertStmt = $conn->prepare("INSERT INTO trip_galleries (id_trip, galery_name, gdrive_link) VALUES (?, ?, ?)");
        $insertStmt->bind_param("iss", $id_trip, $gallery_name, $gdrive_link);
        $result = $insertStmt->execute();
        $insertStmt->close();
        return $result;
    }
}

/**
 * Delete Gallery from trip_galleries table
 */
function deleteGallery($conn, $id_trip)
{
    $stmt = $conn->prepare("DELETE FROM trip_galleries WHERE id_trip = ?");
    $stmt->bind_param("i", $id_trip);
    $result = $stmt->execute();
    $stmt->close();
    return $result;
}

// ========== FIELDS TO TRACK ==========

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

// ========== POST REQUESTS ==========

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action) {

    // ========== DELETE TRIP + FILE GAMBAR + GALLERY ==========
    if ($action === 'deleteTrip' && isset($_POST['id_trip'])) {
        $id_trip_del = (int)$_POST['id_trip'];

        // Ambil data trip terlebih dahulu
        $q = $conn->prepare("SELECT nama_gunung, gambar FROM paket_trips WHERE id_trip=?");
        if (!$q) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'msg' => 'Database error: ' . $conn->error
            ]);
            exit;
        }

        $q->bind_param("i", $id_trip_del);
        $q->execute();
        $q->bind_result($nama_gunung_del, $gambar_path);
        $fetch_result = $q->fetch();
        $q->close();

        if (!$fetch_result) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'msg' => 'Trip tidak ditemukan'
            ]);
            exit;
        }

        // HAPUS FILE GAMBAR TERLEBIH DAHULU
        $file_delete_result = null;
        if (!empty($gambar_path)) {
            $file_delete_result = deleteGameFile($gambar_path);
        }

        // HAPUS DARI trip_galleries (CASCADE)
        deleteGallery($conn, $id_trip_del);

        // HAPUS DARI DATABASE paket_trips
        $stmt = $conn->prepare("DELETE FROM paket_trips WHERE id_trip=?");
        if (!$stmt) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'msg' => 'Database error: ' . $conn->error,
                'fileDeleteInfo' => $file_delete_result
            ]);
            exit;
        }

        $stmt->bind_param("i", $id_trip_del);
        $success = $stmt->execute();
        $stmt->close();

        if ($success && $id_user) {
            $aktivitas = "Trip \"{$nama_gunung_del}\" dihapus beserta gallery";
            $statusLog = "Delete";
            logActivity($conn, $id_user, $aktivitas, $statusLog);
        }

        echo json_encode([
            'success' => $success,
            'msg' => $success ? 'Trip, file gambar, dan gallery berhasil dihapus' : 'Gagal menghapus trip',
            'fileDeleteInfo' => $file_delete_result
        ]);
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
    $status = isset($_POST['status']) ? strtolower(trim($_POST['status'])) : 'available';

    // TAMBAHAN: Ambil input gallery (link_drive saja, galery_name otomatis)
    $link_drive = $_POST['link_drive'] ?? '';

    // Validasi status
    $allowedStatuses = ['available', 'sold', 'done'];
    if (!in_array($status, $allowedStatuses, true)) {
        http_response_code(422);
        echo json_encode(['success' => false, 'msg' => 'Status tidak valid (allowed: available, sold, done)']);
        exit;
    }

    $gambarPath = '';

    // ========== UPLOAD GAMBAR ==========
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
        $base_path = dirname(__DIR__);
        $targetDir = $base_path . DIRECTORY_SEPARATOR . 'img';

        if (!is_dir($targetDir)) {
            @mkdir($targetDir, 0755, true);
        }

        $fileName = date('YmdHis') . '_' . basename($_FILES['gambar']['name']);
        $targetFilePath = $targetDir . DIRECTORY_SEPARATOR . $fileName;

        if (move_uploaded_file($_FILES['gambar']['tmp_name'], $targetFilePath)) {
            // Simpan dengan format: img/filename (forward slash)
            $gambarPath = 'img/' . $fileName;
        }
    }

    // ========== ADD TRIP ==========
    if ($action === 'addTrip') {
        $stmt = $conn->prepare(
            "INSERT INTO paket_trips (nama_gunung, tanggal, slot, durasi, jenis_trip, harga, via_gunung, status, gambar)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );

        if (!$stmt) {
            http_response_code(500);
            echo json_encode(['success' => false, 'msg' => $conn->error]);
            exit;
        }

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
            http_response_code(500);
            echo json_encode(['success' => false, 'msg' => $stmt->error]);
            $stmt->close();
            exit;
        }

        $id = $stmt->insert_id;
        $stmt->close();

        // SAVE GALLERY DATA (JIKA STATUS = DONE DAN ADA LINK)
        // galery_name otomatis menggunakan nama_gunung
        if ($status === 'done' && !empty($link_drive)) {
            saveOrUpdateGallery($conn, $id, $nama_gunung, $link_drive);
        }

        // Log activity
        if ($id_user) {
            $aktivitas = "Trip \"{$nama_gunung}\" ditambahkan";
            $statusLog = "Create";
            logActivity($conn, $id_user, $aktivitas, $statusLog);
        }

        // Fetch inserted data
        $q = $conn->prepare("SELECT * FROM paket_trips WHERE id_trip = ?");
        $q->bind_param("i", $id);
        $q->execute();
        $result = $q->get_result();
        $newTrip = $result->fetch_assoc();
        $q->close();

        echo json_encode(['success' => true, 'data' => $newTrip]);
        exit;
    }

    // ========== UPDATE TRIP ==========
    if ($action === 'updateTrip' && isset($_POST['id_trip'])) {
        $id_trip_update = (int)$_POST['id_trip'];

        // Ambil data lama
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

        // Jika ada upload gambar baru, hapus gambar lama terlebih dahulu
        if ($gambarPath !== '' && !empty($oldData['gambar'])) {
            deleteGameFile($oldData['gambar']);
        }

        // Jika tidak ada upload gambar baru, gunakan gambar lama
        if ($gambarPath === '') {
            $gambarPath = $oldData['gambar'];
        }

        // Update ke database
        $stmt = $conn->prepare(
            "UPDATE paket_trips SET nama_gunung=?, tanggal=?, slot=?, durasi=?, jenis_trip=?, harga=?, via_gunung=?, status=?, gambar=? WHERE id_trip=?"
        );

        if (!$stmt) {
            http_response_code(500);
            echo json_encode(['success' => false, 'msg' => $conn->error]);
            exit;
        }

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
            http_response_code(500);
            echo json_encode(['success' => false, 'msg' => $stmt->error]);
            $stmt->close();
            exit;
        }

        $stmt->close();

        // UPDATE/INSERT GALLERY DATA (JIKA STATUS = DONE)
        // galery_name otomatis menggunakan nama_gunung
        // FIXED: Menggunakan $link_drive bukan $gdrive_link
        if ($status === 'done') {
            if (!empty($link_drive)) {
                saveOrUpdateGallery($conn, $id_trip_update, $nama_gunung, $link_drive);
            }
        } else {
            // Jika status bukan done, hapus gallery data (opsional)
            // Uncomment baris berikut jika ingin menghapus gallery saat status berubah dari done
            // deleteGallery($conn, $id_trip_update);
        }

        // Track changes
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

        // Log activity
        if (!empty($changedDetails) && $id_user) {
            $detailsString = implode(', ', $changedDetails);
            $aktivitas = "Trip \"{$nama_gunung}\" diupdate: {$detailsString}";
            $statusLog = "Update";
            logActivity($conn, $id_user, $aktivitas, $statusLog);
        }

        // Fetch updated data
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

// ========== GET REQUESTS ==========

// GET: List semua trips
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'getTrips') {
    $result = $conn->query("SELECT * FROM paket_trips ORDER BY id_trip DESC");
    $trips = [];
    while ($row = $result->fetch_assoc()) {
        $trips[] = $row;
    }
    echo json_encode($trips);
    exit;
}

// GET: Single trip
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

// GET: Gallery data for specific trip
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'getGallery' && isset($_GET['id_trip'])) {
    $id_trip = intval($_GET['id_trip']);
    $q = $conn->prepare("SELECT * FROM trip_galleries WHERE id_trip = ?");
    $q->bind_param("i", $id_trip);
    $q->execute();
    $result = $q->get_result();
    $gallery = $result->fetch_assoc();
    $q->close();
    echo json_encode(['success' => (bool)$gallery, 'data' => $gallery]);
    exit;
}

// Fallback
http_response_code(400);
echo json_encode(['success' => false, 'msg' => 'Request tidak valid']);
