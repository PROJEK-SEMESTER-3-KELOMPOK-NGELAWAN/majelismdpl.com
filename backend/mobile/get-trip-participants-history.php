<?php
/**
 * ============================================
 * GET TRIP PARTICIPANTS HISTORY API
 * Ambil data peserta trip untuk trip BERSTATUS 'done' (riwayat/selesai)
 * ============================================
 */
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
        'message' => 'Method not allowed. Use POST'
    ]);
    exit;
}

// Baca id_trip dari JSON body/POST
$input = json_decode(file_get_contents('php://input'), true);
$id_trip = 0;

if (isset($input['id_trip'])) {
    $id_trip = intval($input['id_trip']);
} elseif (isset($_POST['id_trip'])) {
    $id_trip = intval($_POST['id_trip']);
}

if ($id_trip <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'ID Trip tidak valid',
        'received_id_trip' => $id_trip
    ]);
    exit;
}

try {
    // Pastikan trip benar-benar selesai (done)
    $queryCheck = "SELECT status FROM paket_trips WHERE id_trip = ? LIMIT 1";
    $stmtCheck = $conn->prepare($queryCheck);
    if (!$stmtCheck) throw new Exception("Gagal menyiapkan statement: " . $conn->error);
    $stmtCheck->bind_param("i", $id_trip);
    $stmtCheck->execute();
    $resultCheck = $stmtCheck->get_result();
    if ($resultCheck->num_rows === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Trip tidak ditemukan',
            'received_id_trip' => $id_trip
        ]);
        $stmtCheck->close();
        $conn->close();
        exit;
    }
    $tripData = $resultCheck->fetch_assoc();
    $stmtCheck->close();
    $tripStatus = $tripData['status'];
    if ($tripStatus !== 'done') {
        echo json_encode([
            'success' => false,
            'message' => 'Trip belum selesai, status: ' . $tripStatus,
            'trip_status' => $tripStatus,
            'received_id_trip' => $id_trip
        ]);
        $conn->close();
        exit;
    }

    // Ambil peserta trip yang sudah selesai
    $query = "
        SELECT 
            p.id_participant,
            p.nama,
            p.email,
            p.nik,
            p.no_wa,
            p.tempat_lahir,
            p.tanggal_lahir,
            p.alamat,
            u.id_user,
            u.username,
            u.foto_profil,
            b.id_booking,
            b.jumlah_orang,
            b.total_harga,
            b.status AS booking_status,
            b.tanggal_booking,
            pt.nama_gunung,
            pt.jenis_trip,
            pt.status AS trip_status
        FROM participants p
        INNER JOIN bookings b ON p.id_booking = b.id_booking
        INNER JOIN paket_trips pt ON b.id_trip = pt.id_trip
        LEFT JOIN users u ON b.id_user = u.id_user
        WHERE pt.id_trip = ?
        ORDER BY b.tanggal_booking DESC, p.id_participant ASC
    ";

    $stmt = $conn->prepare($query);
    if (!$stmt) throw new Exception("Gagal menyiapkan statement: " . $conn->error);
    $stmt->bind_param("i", $id_trip);
    if (!$stmt->execute()) throw new Exception("Gagal mengeksekusi query: " . $stmt->error);

    $result = $stmt->get_result();
    $participants = [];
    while ($row = $result->fetch_assoc()) {
        $fotoProfil = '';
        if (!empty($row['foto_profil'])) {
            $fotoProfil = BASE_URL . '/' . ltrim($row['foto_profil'], '/');
        }
        $participants[] = [
            'id_participant' => intval($row['id_participant']),
            'nama' => $row['nama'] ?? '',
            'email' => $row['email'] ?? '',
            'nik' => $row['nik'] ?? '',
            'no_wa' => $row['no_wa'] ?? '',
            'tempat_lahir' => $row['tempat_lahir'] ?? '',
            'tanggal_lahir' => $row['tanggal_lahir'] ?? '',
            'alamat' => $row['alamat'] ?? '',
            'username' => $row['username'] ?? '',
            'foto_profil' => $fotoProfil,
            'id_booking' => intval($row['id_booking']),
            'jumlah_orang' => intval($row['jumlah_orang']),
            'total_harga' => intval($row['total_harga']),
            'booking_status' => $row['booking_status'] ?? '',
            'tanggal_booking' => $row['tanggal_booking'] ?? '',
            'nama_gunung' => $row['nama_gunung'] ?? '',
            'jenis_trip' => $row['jenis_trip'] ?? '',
            'trip_status' => $row['trip_status'] ?? ''
        ];
    }
    $stmt->close();

    echo json_encode([
        'success' => true,
        'message' => 'Peserta trip riwayat berhasil diambil',
        'data' => $participants,
        'total_participants' => count($participants),
        'trip_status' => $tripStatus
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
$conn->close();
?>
