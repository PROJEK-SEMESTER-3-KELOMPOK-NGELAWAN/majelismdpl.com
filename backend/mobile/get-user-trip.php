<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

require_once dirname(__FILE__, 3) . '/config.php';
require_once dirname(__FILE__, 2) . '/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$id_user = isset($input['id_user']) ? intval($input['id_user']) : 0;

if ($id_user <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'ID User tidak valid'
    ]);
    exit;
}

try {
    $query = "SELECT 
                b.id_booking,
                b.id_trip,
                b.jumlah_orang,
                b.total_harga,
                b.tanggal_booking,
                b.status AS status_booking,
                t.nama_gunung,
                t.jenis_trip,
                t.tanggal AS tanggal_trip,
                t.durasi,
                t.via_gunung,
                t.gambar,
                t.status AS status_trip,
                d.nama_lokasi,
                d.waktu_kumpul,
                d.link_map,
                p.status_pembayaran
            FROM bookings b
            JOIN paket_trips t ON b.id_trip = t.id_trip
            LEFT JOIN detail_trips d ON t.id_trip = d.id_trip
            LEFT JOIN payments p ON b.id_booking = p.id_booking
            WHERE b.id_user = ?
              AND (b.status = 'paid' OR b.status = 'confirmed')
              AND t.status != 'done'
            ORDER BY t.tanggal ASC";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id_user);
    $stmt->execute();
    $result = $stmt->get_result();

    $trips = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $start_date = new DateTime($row['tanggal_trip']);
            $durasi_angka = intval($row['durasi'] ?? 1);
            $end_date = clone $start_date;
            $end_date->modify('+' . ($durasi_angka - 1) . ' days');
            $tanggal_display = $start_date->format('d') . '–' . $end_date->format('d M Y');

            $gambar_url = '';
            if (!empty($row['gambar'])) {
                if (strpos($row['gambar'], 'http') === 0) {
                    $gambar_url = $row['gambar'];
                } elseif (strpos($row['gambar'], 'img/') === 0) {
                    $gambar_url = getAssetsUrl($row['gambar']);
                } else {
                    $gambar_url = getAssetsUrl('img/' . $row['gambar']);
                }
            } else {
                $gambar_url = getAssetsUrl('img/default-mountain.jpg');
            }

            $status_pembayaran = $row['status_pembayaran'] ?? 'pending';
            $status_pembayaran_display = 'Belum Lunas';

            if (
                strtolower($status_pembayaran) === 'settlement' ||
                strtolower($status_pembayaran) === 'paid' ||
                strtolower($row['status_booking']) === 'paid' ||
                strtolower($row['status_booking']) === 'confirmed'
            ) {
                $status_pembayaran_display = 'Lunas';
            }

            $trips[] = [
                'id_booking' => $row['id_booking'],
                'id_trip' => $row['id_trip'],
                'nama_gunung' => $row['nama_gunung'],
                'jenis_trip' => $row['jenis_trip'],
                'tanggal_trip' => $row['tanggal_trip'],
                'tanggal_display' => $tanggal_display,
                'durasi' => $row['durasi'] ?? '1 hari',
                'via_gunung' => $row['via_gunung'] ?? 'Via Utama',
                'gambar_url' => $gambar_url,
                'jumlah_orang' => $row['jumlah_orang'],
                'total_harga' => $row['total_harga'],
                'status_booking' => $row['status_booking'],
                'status_pembayaran' => $status_pembayaran_display,
                'nama_lokasi' => $row['nama_lokasi'] ?? 'Lokasi akan diinformasikan',
                'waktu_kumpul' => $row['waktu_kumpul'] ?? '00:00',
                'link_map' => $row['link_map'] ?? ''
            ];
        }

        echo json_encode([
            'success' => true,
            'message' => 'Data trip ditemukan',
            'data' => $trips
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Tidak ada trip aktif',
            'data' => []
        ]);
    }

    $stmt->close();
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}

$conn->close();
