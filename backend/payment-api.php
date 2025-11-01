<?php
require_once dirname(__FILE__, 2) . '/vendor/autoload.php';
require_once 'koneksi.php';

ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);

session_start();
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

set_error_handler(function ($errno, $errstr, $errfile, $errline) use ($respond) {
    $respond(500, ['error' => 'PHP Error', 'message' => $errstr, 'file' => basename($errfile), 'line' => $errline]);
});
set_exception_handler(function ($e) use ($respond) {
    $respond(500, ['error' => 'Exception', 'message' => $e->getMessage()]);
});

\Midtrans\Config::$serverKey = 'Mid-server-4bU4xow9Vq2yH-1WicaeTMiq';
\Midtrans\Config::$isProduction = false;
\Midtrans\Config::$isSanitized = true;
\Midtrans\Config::$is3ds = true;

$mapStatus = function ($transaction_status, $fraud_status) {
    $fraud_status = $fraud_status ?? 'accept';
    if ($transaction_status === 'capture') return ($fraud_status === 'accept') ? 'paid' : 'pending';
    if ($transaction_status === 'settlement') return 'paid';
    if ($transaction_status === 'pending') return 'pending';
    if (in_array($transaction_status, ['deny', 'expire', 'cancel'])) return 'failed';
    return 'pending';
};

// Endpoint: status di DB by booking id
if (isset($_GET['status_id'])) {
    $id_booking = intval($_GET['status_id']);
    $status = 'unknown';
    if ($id_booking > 0) {
        $stmt = $conn->prepare("SELECT status_pembayaran FROM payments WHERE id_booking=?");
        if (!$stmt) $respond(500, ['error' => 'Database prepare error: ' . $conn->error]);
        $stmt->bind_param("i", $id_booking);
        $stmt->execute();
        $stmt->bind_result($status);
        $stmt->fetch();
        $stmt->close();
    }
    $respond(200, ['status' => $status ?: 'no_payment']);
}

// Endpoint: check status ke Midtrans by order_id
if (isset($_GET['check_status'])) {
    $order_id = trim($_GET['check_status']);
    if ($order_id === '') $respond(400, ['error' => 'Order ID required']);

    try {
        $status = \Midtrans\Transaction::status($order_id); // [web:126][web:139]
        $transaction_status = $status->transaction_status ?? 'pending';
        $fraud_status = $status->fraud_status ?? 'accept';
        $status_pembayaran = $mapStatus($transaction_status, $fraud_status);

        $conn->begin_transaction();
        $stmt = $conn->prepare("UPDATE payments SET status_pembayaran=? WHERE order_id=?");
        if (!$stmt) {
            $conn->rollback();
            $respond(500, ['error' => 'Database error: ' . $conn->error]);
        }
        $stmt->bind_param("ss", $status_pembayaran, $order_id);
        $stmt->execute();
        $stmt->close();

        if ($status_pembayaran === 'paid') {
            $stmtBooking = $conn->prepare("UPDATE bookings SET status='confirmed' WHERE id_booking=(SELECT id_booking FROM payments WHERE order_id=?)");
            if ($stmtBooking) {
                $stmtBooking->bind_param("s", $order_id);
                $stmtBooking->execute();
                $stmtBooking->close();
            }
        }
        $conn->commit();

        $respond(200, ['success' => true, 'status' => $status_pembayaran, 'transaction_status' => $transaction_status]);
    } catch (Exception $e) {
        if ($conn && $conn->errno) $conn->rollback();
        $respond(500, ['error' => 'Failed to check status', 'message' => $e->getMessage()]);
    }
}

// Endpoint: generate Snap token by booking id
if (isset($_GET['booking'])) {
    $id_booking = intval($_GET['booking']);
    if ($id_booking <= 0) $respond(400, ['error' => 'ID booking tidak valid']);

    $stmt = $conn->prepare("SELECT b.*, t.nama_gunung, t.harga, u.username, u.email
                          FROM bookings b
                          JOIN paket_trips t ON b.id_trip = t.id_trip
                          JOIN users u ON b.id_user = u.id_user
                          WHERE b.id_booking = ?");
    if (!$stmt) $respond(500, ['error' => 'Database prepare error: ' . $conn->error]);

    $stmt->bind_param("i", $id_booking);
    $stmt->execute();
    $booking = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$booking) $respond(404, ['error' => 'Booking tidak ditemukan']);

    $order_id = 'ORDER-' . $id_booking . '-' . time();

    $params = [
        'transaction_details' => ['order_id' => $order_id, 'gross_amount' => intval($booking['total_harga'])],
        'customer_details' => ['first_name' => $booking['username'], 'email' => $booking['email']],
        'item_details' => [[
            'id' => $booking['id_trip'],
            'price' => intval($booking['total_harga']),
            'quantity' => 1,
            'name' => $booking['nama_gunung']
        ]]
    ];

    $snapToken = \Midtrans\Snap::getSnapToken($params);

    $cek = $conn->prepare("SELECT id_payment FROM payments WHERE id_booking=?");
    if (!$cek) $respond(500, ['error' => 'Database error: ' . $conn->error]);
    $cek->bind_param('i', $id_booking);
    $cek->execute();
    $cek->store_result();

    if ($cek->num_rows == 0) {
        $stmtPay = $conn->prepare("INSERT INTO payments (id_booking, jumlah_bayar, tanggal, jenis_pembayaran, metode, status_pembayaran, order_id)
                               VALUES (?, ?, CURDATE(), 'trip', 'midtrans', 'pending', ?)");
        if (!$stmtPay) $respond(500, ['error' => 'Insert payment error: ' . $conn->error]);
        $gross_amount = intval($booking['total_harga']);
        $stmtPay->bind_param("iis", $id_booking, $gross_amount, $order_id);
        $stmtPay->execute();
        $stmtPay->close();
    } else {
        $updateOrder = $conn->prepare("UPDATE payments SET order_id=?, status_pembayaran='pending' WHERE id_booking=?");
        if ($updateOrder) {
            $updateOrder->bind_param("si", $order_id, $id_booking);
            $updateOrder->execute();
            $updateOrder->close();
        }
    }
    $cek->close();

    $respond(200, ['success' => true, 'snap_token' => $snapToken, 'order_id' => $order_id]);
}

// Webhook (opsional, tetap ada di file ini)
$json = file_get_contents('php://input');
if (empty($json)) $respond(200, ['ok' => true]);
$notification = json_decode($json);
if (!$notification) {
    http_response_code(400);
    $respond(400, ['error' => 'Invalid notification']);
}

$validSignature = hash('sha512', ($notification->order_id ?? '') . ($notification->status_code ?? '') . ($notification->gross_amount ?? '') . \Midtrans\Config::$serverKey);
if (($notification->signature_key ?? '') !== $validSignature) {
    http_response_code(403);
    $respond(403, ['error' => 'Invalid signature']);
}

$order_id = $notification->order_id;
$transaction_status = $notification->transaction_status ?? 'pending';
$fraud_status = $notification->fraud_status ?? 'accept';
$status_pembayaran = $mapStatus($transaction_status, $fraud_status);

$conn->begin_transaction();
$stmt = $conn->prepare("UPDATE payments SET status_pembayaran=? WHERE order_id=?");
if ($stmt) {
    $stmt->bind_param("ss", $status_pembayaran, $order_id);
    $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();
} else {
    $conn->rollback();
    http_response_code(500);
    $respond(500, ['error' => 'Database error: ' . $conn->error]);
}
if ($status_pembayaran === 'paid') {
    $stmtBooking = $conn->prepare("UPDATE bookings SET status='confirmed' WHERE id_booking=(SELECT id_booking FROM payments WHERE order_id=?)");
    if ($stmtBooking) {
        $stmtBooking->bind_param("s", $order_id);
        $stmtBooking->execute();
        $stmtBooking->close();
    }
}
$conn->commit();

http_response_code(200);
$respond(200, ['success' => true, 'message' => 'Notification processed', 'status' => $status_pembayaran, 'affected_rows' => $affected ?? 0]);
