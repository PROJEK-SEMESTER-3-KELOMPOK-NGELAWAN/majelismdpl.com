<?php
require_once 'koneksi.php';

// Ambil method dan parameter
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? null;

// GET: Detail trip
if ($method === 'GET' && $action === 'getDetail') {
    $id_trip = $_GET['id_trip'] ?? null;
    if (!$id_trip) {
        echo json_encode([
            'success' => false,
            'message' => 'Parameter id_trip wajib diisi'
        ]);
        exit;
    }

    $stmt = $conn->prepare("SELECT * FROM detail_trip WHERE id_trip = ?");
    $stmt->bind_param("i", $id_trip);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    echo json_encode([
        'success' => ($data ? true : false),
        'data' => $data
    ]);
    exit;
}

// POST: Simpan/ubah detail trip
if ($method === 'POST') {
    $id_trip = $_POST['id_trip'];
    $nama_lokasi = $_POST['nama_lokasi_meeting_point'];
    $alamat = $_POST['alamat_meeting_point'];
    $waktu_kumpul = $_POST['waktu_kumpul'];
    $include = $_POST['include'];
    $exclude = $_POST['exclude'];
    $syaratKetentuan = $_POST['syarat_ketentuan'];
    $link_map = $_POST['link_gmap_meeting_point'];

    // Cek, kalau detail sudah ada update, kalau belum insert
    $cek = $conn->prepare("SELECT id_trip FROM detail_trip WHERE id_trip = ?");
    $cek->bind_param("i", $id_trip);
    $cek->execute();
    $cek->store_result();

    if ($cek->num_rows > 0) {
        // Update
        $stmt = $conn->prepare("UPDATE detail_trip SET nama_lokasi=?, alamat=?, waktu_kumpul=?, include=?, exclude=?, syaratKetentuan=?, link_map=? WHERE id_trip=?");
        $stmt->bind_param("sssssssi", $nama_lokasi, $alamat, $waktu_kumpul, $include, $exclude, $syaratKetentuan, $link_map, $id_trip);
    } else {
        // Insert
        $stmt = $conn->prepare("INSERT INTO detail_trip (id_trip, nama_lokasi, alamat, waktu_kumpul, include, exclude, syaratKetentuan, link_map) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssssss", $id_trip, $nama_lokasi, $alamat, $waktu_kumpul, $include, $exclude, $syaratKetentuan, $link_map);
    }
    $result = $stmt->execute();

    echo json_encode([
        'success' => $result,
        'message' => $result ? 'Detail trip berhasil disimpan!' : 'Gagal menyimpan detail trip.'
    ]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid Request']);
