<?php
require_once 'koneksi.php';
require_once dirname(__FILE__, 2) . '/config.php';

header('Content-Type: application/json; charset=utf-8');

ini_set('display_errors', 0);
ini_set('log_errors', 0);
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);

set_error_handler(function ($errno, $errstr) {
    if ($errno === E_WARNING || $errno === E_NOTICE) {
        return true;
    }
    echo json_encode(['error' => 'PHP Error: ' . $errstr, 'success' => false]);
    exit;
});

try {
    if (!$conn || $conn->connect_error) {
        throw new Exception('Database connection error: ' . ($conn->connect_error ?? 'Unknown'));
    }

    $id_booking = intval($_GET['id'] ?? 0);
    if ($id_booking <= 0) {
        echo json_encode(['error' => 'Invalid booking ID', 'success' => false]);
        exit;
    }

    $stmt = $conn->prepare("
        SELECT p.status_pembayaran, p.order_id, p.tanggal,
               b.status as booking_status, b.total_harga, b.jumlah_orang
        FROM bookings b
        LEFT JOIN payments p ON p.id_booking=b.id_booking
        WHERE b.id_booking=? LIMIT 1
    ");
    if (!$stmt) {
        throw new Exception('Database prepare error: ' . $conn->error);
    }
    $stmt->bind_param("i", $id_booking);
    if (!$stmt->execute()) {
        throw new Exception('Database execute error: ' . $stmt->error);
    }
    $data = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($data) {
        $age_hours = !empty($data['tanggal']) ? (int)floor((time() - strtotime($data['tanggal'] . ' 00:00:00')) / 3600) : null;

        echo json_encode([
            'success' => true,
            'status' => $data['status_pembayaran'] ?? 'no_payment',
            'booking_status' => $data['booking_status'],
            'order_id' => $data['order_id'] ?? null,
            'total_harga' => (int)$data['total_harga'],
            'jumlah_orang' => (int)$data['jumlah_orang'],
            'age_hours' => $age_hours
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode(['error' => 'Booking not found', 'status' => 'unknown', 'success' => false]);
    }
} catch (Exception $e) {
    echo json_encode([
        'error' => $e->getMessage(),
        'success' => false
    ]);
}
