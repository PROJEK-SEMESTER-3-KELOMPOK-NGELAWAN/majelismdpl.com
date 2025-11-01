<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once 'koneksi.php';

$action = $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => 'Method not allowed. Only GET requests are supported.'
    ]);
    exit();
}

try {
    switch ($action) {
        case 'getDashboardStats':
            getDashboardStats($conn);
            break;
        case 'getTripCountByStatus':
            getTripCountByStatus($conn);
            break;
        case 'getTripsOverview':
            getTripsOverview($conn);
            break;
        case 'getParticipantsStats':
            getParticipantsStats($conn);
            break;
        case 'getBookingsStats':
            getBookingsStats($conn);
            break;
        case 'getParticipantsMonthly':
            getParticipantsMonthly($conn);
            break;
        default:
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Invalid action. Available: getDashboardStats, getTripCountByStatus, getTripsOverview, getParticipantsStats, getBookingsStats, getParticipantsMonthly'
            ]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Server error: ' . $e->getMessage()
    ]);
}

/**
 * Dashboard stats ringkas.
 * trip_aktif  = paket_trips.status = 'available'
 * trip_selesai= paket_trips.status = 'done'
 * total_peserta = jumlah baris di participants
 */
function getDashboardStats($conn)
{
    try {
        $stats = [];

        // Trip aktif (available)
        $row = $conn->query("SELECT COUNT(*) AS count FROM paket_trips WHERE status='available'")->fetch_assoc();
        $stats['trip_aktif'] = (int)$row['count'];

        // Trip selesai (DONE)
        $row = $conn->query("SELECT COUNT(*) AS count FROM paket_trips WHERE status='done'")->fetch_assoc();
        $stats['trip_selesai'] = (int)$row['count'];

        // Total trips
        $row = $conn->query("SELECT COUNT(*) AS count FROM paket_trips")->fetch_assoc();
        $stats['total_trips'] = (int)$row['count'];

        // Total peserta dari tabel participants
        $checkParticipants = $conn->query("SHOW TABLES LIKE 'participants'");
        if ($checkParticipants->num_rows > 0) {
            $row = $conn->query("SELECT COUNT(*) AS count FROM participants")->fetch_assoc();
            $stats['total_peserta'] = (int)$row['count'];
        } else {
            $stats['total_peserta'] = 0;
        }

        // Pembayaran pending (bookings.status='pending')
        $checkBookings = $conn->query("SHOW TABLES LIKE 'bookings'");
        if ($checkBookings->num_rows > 0) {
            $row = $conn->query("SELECT COUNT(*) AS count FROM bookings WHERE status='pending'")->fetch_assoc();
            $stats['pembayaran_pending'] = (int)$row['count'];

            $row = $conn->query("SELECT COUNT(*) AS count FROM bookings")->fetch_assoc();
            $stats['total_bookings'] = (int)$row['count'];
        } else {
            $stats['pembayaran_pending'] = 0;
            $stats['total_bookings'] = 0;
        }

        $stats['last_updated'] = date('Y-m-d H:i:s');

        echo json_encode(['success' => true, 'data' => $stats]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'data' => [
                'trip_aktif' => 0,
                'trip_selesai' => 0,
                'total_trips' => 0,
                'total_peserta' => 0,
                'pembayaran_pending' => 0,
                'total_bookings' => 0,
                'last_updated' => date('Y-m-d H:i:s')
            ],
            'error' => $e->getMessage()
        ]);
    }
}

function getTripCountByStatus($conn)
{
    $status = $_GET['status'] ?? '';
    if ($status === '') {
        echo json_encode(['success' => false, 'count' => 0, 'error' => 'Status parameter is required']);
        return;
    }
    try {
        $stmt = $conn->prepare("SELECT COUNT(*) AS count FROM paket_trips WHERE status=?");
        $stmt->bind_param("s", $status);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        echo json_encode(['success' => true, 'count' => (int)$row['count'], 'status' => $status, 'timestamp' => date('Y-m-d H:i:s')]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'count' => 0, 'status' => $status, 'error' => $e->getMessage()]);
    }
}

function getTripsOverview($conn)
{
    try {
        $overview = [];
        $res = $conn->query("SELECT status, COUNT(*) AS count, AVG(harga) AS avg_price, SUM(slot) AS total_slots FROM paket_trips GROUP BY status");
        while ($row = $res->fetch_assoc()) {
            $overview[$row['status']] = [
                'count' => (int)$row['count'],
                'avg_price' => round((float)$row['avg_price'], 2),
                'total_slots' => (int)$row['total_slots']
            ];
        }
        $recent = [];
        $res2 = $conn->query("SELECT id_trip, nama_gunung, status, tanggal, harga FROM paket_trips ORDER BY id_trip DESC LIMIT 5");
        while ($row = $res2->fetch_assoc()) {
            $recent[] = $row;
        }

        echo json_encode(['success' => true, 'data' => ['overview' => $overview, 'recent_trips' => $recent, 'timestamp' => date('Y-m-d H:i:s')]]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'data' => [], 'error' => $e->getMessage()]);
    }
}

function getParticipantsStats($conn)
{
    try {
        $check = $conn->query("SHOW TABLES LIKE 'participants'");
        if ($check->num_rows === 0) {
            echo json_encode(['success' => false, 'data' => [], 'error' => 'Participants table not found']);
            return;
        }
        $stats = [];
        $row = $conn->query("SELECT COUNT(*) AS count FROM participants")->fetch_assoc();
        $stats['total'] = (int)$row['count'];

        // Jika ada relasi id_participant ke bookings, hitung yang punya bookings
        $checkCol = $conn->query("SHOW COLUMNS FROM participants LIKE 'id_booking'");
        if ($checkCol->num_rows > 0) {
            $row = $conn->query("SELECT COUNT(*) AS count FROM participants WHERE id_booking IS NOT NULL")->fetch_assoc();
            $stats['with_bookings'] = (int)$row['count'];
        } else {
            $stats['with_bookings'] = 0;
        }

        echo json_encode(['success' => true, 'data' => $stats, 'timestamp' => date('Y-m-d H:i:s')]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'data' => [], 'error' => $e->getMessage()]);
    }
}

function getBookingsStats($conn)
{
    try {
        $stats = [];
        $check = $conn->query("SHOW TABLES LIKE 'bookings'");
        if ($check->num_rows === 0) {
            echo json_encode(['success' => false, 'data' => [], 'error' => 'Bookings table not found']);
            return;
        }
        $res = $conn->query("SELECT status, COUNT(*) AS count, SUM(total_harga) AS total_revenue FROM bookings GROUP BY status");
        while ($row = $res->fetch_assoc()) {
            $stats[$row['status']] = [
                'count' => (int)$row['count'],
                'revenue' => (float)$row['total_revenue']
            ];
        }
        $row = $conn->query("SELECT SUM(total_harga) AS total FROM bookings")->fetch_assoc();
        $stats['total_revenue'] = (float)$row['total'];

        echo json_encode(['success' => true, 'data' => $stats, 'timestamp' => date('Y-m-d H:i:s')]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'data' => [], 'error' => $e->getMessage()]);
    }
}

/**
 * Participants Monthly Chart Data (12 bulan terakhir)
 * Menggunakan tabel participants (relasi ke bookings via id_booking), kelompokkan berdasarkan bookings.tanggal_booking.
 */
function getParticipantsMonthly($conn)
{
    try {
        // Pastikan tabel participants dan bookings ada
        $hasParticipants = $conn->query("SHOW TABLES LIKE 'participants'")->num_rows > 0;
        $hasBookings = $conn->query("SHOW TABLES LIKE 'bookings'")->num_rows > 0;

        if (!$hasParticipants || !$hasBookings) {
            echo json_encode(['success' => false, 'labels' => [], 'data' => [], 'error' => 'Required tables not found']);
            return;
        }

        // Ambil jumlah peserta per bulan dari tanggal_booking di bookings
        $sql = "
            SELECT 
                DATE_FORMAT(b.tanggal_booking, '%Y-%m') AS ym,
                YEAR(b.tanggal_booking) AS y,
                MONTH(b.tanggal_booking) AS m,
                COUNT(p.id_participant) AS total_peserta
            FROM participants p
            INNER JOIN bookings b ON p.id_booking = b.id_booking
            WHERE b.tanggal_booking >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
            GROUP BY DATE_FORMAT(b.tanggal_booking, '%Y-%m'), YEAR(b.tanggal_booking), MONTH(b.tanggal_booking)
            ORDER BY y ASC, m ASC
        ";
        $res = $conn->query($sql);
        $map = [];
        while ($r = $res->fetch_assoc()) {
            $map[$r['ym']] = (int)$r['total_peserta'];
        }

        // Build 12 months series (0 jika tidak ada data)
        $labels = [];
        $data = [];
        $cursor = new DateTime(date('Y-m-01'));
        $cursor->modify('-11 months');

        for ($i = 0; $i < 12; $i++) {
            $key = $cursor->format('Y-m');
            $labels[] = bulanIndo((int)$cursor->format('n'));
            $data[] = $map[$key] ?? 0;
            $cursor->modify('+1 month');
        }

        echo json_encode(['success' => true, 'labels' => $labels, 'data' => $data, 'timestamp' => date('Y-m-d H:i:s')]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'labels' => [], 'data' => [], 'error' => $e->getMessage()]);
    }
}

function bulanIndo($n)
{
    $arr = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    return $arr[max(1, min(12, $n)) - 1];
}
