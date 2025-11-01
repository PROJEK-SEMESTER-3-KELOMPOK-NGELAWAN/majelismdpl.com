<?php
require_once 'koneksi.php';
ini_set('display_errors', 0);
error_reporting(0);
header('Content-Type: application/json; charset=utf-8');

$respond = function (int $code, array $data) {
    if (ob_get_length()) {
        ob_clean();
    }
    header_remove();
    header('Content-Type: application/json; charset=utf-8');
    http_response_code($code);
    echo json_encode($data);
    exit;
};

set_error_handler(function ($errno, $errstr) use ($respond) {
    $respond(500, ['error' => 'PHP Error: ' . $errstr]);
});

try {
    $id_booking = intval($_GET['id'] ?? 0);
    if ($id_booking <= 0) $respond(400, ['error' => 'Invalid booking ID']);

    $stmt = $conn->prepare("SELECT 
      p.status_pembayaran, 
      p.order_id,
      b.status as booking_status,
      b.total_harga,
      b.jumlah_orang
    FROM bookings b
    LEFT JOIN payments p ON p.id_booking = b.id_booking
    WHERE b.id_booking = ?");
    if (!$stmt) $respond(500, ['error' => 'Database error: ' . $conn->error]);

    $stmt->bind_param("i", $id_booking);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    $stmt->close();

    if ($data) {
        $respond(200, [
            'success' => true,
            'status' => $data['status_pembayaran'] ?? 'no_payment',
            'booking_status' => $data['booking_status'],
            'order_id' => $data['order_id'] ?? null,
            'total_harga' => $data['total_harga'],
            'jumlah_orang' => $data['jumlah_orang']
        ]);
    } else {
        $respond(404, ['error' => 'Booking not found', 'status' => 'unknown']);
    }
} catch (Exception $e) {
    $respond(500, ['error' => $e->getMessage()]);
}
