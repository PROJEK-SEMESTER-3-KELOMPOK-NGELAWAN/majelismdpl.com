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
$id_user = 0;
if (isset($input['id_user'])) {
    $id_user = intval($input['id_user']);
} elseif (isset($_POST['id_user'])) {
    $id_user = intval($_POST['id_user']);
} elseif (isset($_REQUEST['id_user'])) {
    $id_user = intval($_REQUEST['id_user']);
}

if ($id_user <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'ID User tidak valid',
        'received_id_user' => $id_user
    ]);
    exit;
}

try {
    $query = "
        SELECT 
            t.id_trip,
            t.nama_gunung,
            t.tanggal AS tanggal_trip,
            d.nama_lokasi,
            d.alamat AS alamat_lokasi,
            d.waktu_kumpul,
            d.link_map_mobile,
            t.durasi,
            b.status AS status_booking
        FROM bookings b
        JOIN paket_trips t ON b.id_trip = t.id_trip
        LEFT JOIN detail_trips d ON t.id_trip = d.id_trip
        WHERE b.id_user = ?
            AND (b.status = 'paid' OR b.status = 'confirmed')
            AND t.status != 'done'
        ORDER BY t.tanggal ASC
    ";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id_user);
    $stmt->execute();
    $result = $stmt->get_result();

    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $link_map_mobile = isset($row['link_map_mobile']) && $row['link_map_mobile'] != null ? $row['link_map_mobile'] : '';

        $informasi_tambahan = "• Waktu kumpul: " . ($row['waktu_kumpul'] ?: '-') . " WIB";
        $informasi_tambahan .= "\n• Lokasi: " . ($row['nama_lokasi'] ?: '-');
        $informasi_tambahan .= "\n• Parkir: Area basecamp";
        $informasi_tambahan .= "\n• Bawa: KTP dan kebutuhan pribadi";
        $informasi_tambahan .= "\n• Sarapan: Disediakan";

        $rows[] = [
            'id_trip' => $row['id_trip'],
            'nama_gunung' => $row['nama_gunung'],
            'tanggal_trip' => $row['tanggal_trip'],
            'waktu_kumpul' => $row['waktu_kumpul'],
            'nama_lokasi' => $row['nama_lokasi'],
            'alamat_lokasi' => $row['alamat_lokasi'],
            'link_map' => $link_map_mobile,
            'informasi_tambahan' => $informasi_tambahan
        ];
    }

    if (count($rows) > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Data meeting point ditemukan',
            'data' => $rows
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Tidak ada trip aktif',
            'data' => null
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
?>
