<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once 'koneksi.php';

session_start(); // Pastikan session dimulai untuk ambil id_user
$id_user = $_SESSION['id_user'] ?? null;

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $id_trip = $_GET['id_trip'] ?? null;
    $stmt = $conn->prepare("SELECT * FROM detail_trips WHERE id_trip = ?");
    $stmt->bind_param("i", $id_trip);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    $stmt->close();

    if (!$data) {
        $data = [
            'nama_lokasi' => '',
            'alamat' => '',
            'waktu_kumpul' => '',
            'link_map' => '',
            'include' => '',
            'exclude' => '',
            'syaratKetentuan' => ''
        ];
    }

    echo json_encode(['success' => true, 'data' => $data]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_trip = $_POST['id_trip'] ?? null;
    $nama_lokasi = $_POST['nama_lokasi'] ?? '';
    $alamat = $_POST['alamat'] ?? '';
    $waktu_kumpul = $_POST['waktu_kumpul'] ?? '';
    $link_map = $_POST['link_map'] ?? '';
    $include = $_POST['include'] ?? '';
    $exclude = $_POST['exclude'] ?? '';
    $syaratKetentuan = $_POST['syaratKetentuan'] ?? '';

    if (!$id_trip) {
        echo json_encode(['success' => false, 'message' => 'Trip ID kosong!']);
        exit;
    }

    $cek = $conn->prepare("SELECT id_trip FROM detail_trips WHERE id_trip = ?");
    $cek->bind_param("i", $id_trip);
    $cek->execute();
    $cek->store_result();

    $isUpdate = ($cek->num_rows > 0);

    // UPDATE DETAIL TRIP JIKA SUDAH ADA, INSERT JIKA BELUM ADA
    if ($isUpdate) {
        $stmt = $conn->prepare(
            "UPDATE detail_trips SET nama_lokasi=?, alamat=?, waktu_kumpul=?, link_map=?, `include`=?, `exclude`=?, syaratKetentuan=? WHERE id_trip=?"
        );
        $stmt->bind_param("sssssssi", $nama_lokasi, $alamat, $waktu_kumpul, $link_map, $include, $exclude, $syaratKetentuan, $id_trip);
    } else {
        $stmt = $conn->prepare(
            "INSERT INTO detail_trips (id_trip, nama_lokasi, alamat, waktu_kumpul, link_map, `include`, `exclude`, syaratKetentuan)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("isssssss", $id_trip, $nama_lokasi, $alamat, $waktu_kumpul, $link_map, $include, $exclude, $syaratKetentuan);
    }
    $cek->close();

    if ($stmt->execute()) {
        // Logging activity jika ada id_user
        if ($id_user) {
            if ($isUpdate) {
                $aktivitas = "Mengubah detail trip pada Trip ID #$id_trip ($nama_lokasi)";
                $statusLog = "update";
            } else {
                $aktivitas = "Menambahkan detail trip baru pada Trip ID #$id_trip ($nama_lokasi)";
                $statusLog = "publish";
            }
            $logStmt = $conn->prepare(
                "INSERT INTO activity_logs (aktivitas, waktu, status, id_user) VALUES (?, NOW(), ?, ?)"
            );
            $logStmt->bind_param("ssi", $aktivitas, $statusLog, $id_user);
            $logStmt->execute();
            $logStmt->close();
        }

        echo json_encode(['success' => true, 'message' => 'Detail trip berhasil disimpan']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal menyimpan: ' . $stmt->error]);
    }
    $stmt->close();
    exit;
}
