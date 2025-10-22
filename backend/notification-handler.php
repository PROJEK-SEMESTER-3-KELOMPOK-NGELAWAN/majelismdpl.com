<?php
require_once 'koneksi.php';

// Untuk debugging, tulis log setiap request masuk
file_put_contents('webhook_log.txt', date('c').' '.$_SERVER['REMOTE_ADDR'].' '.file_get_contents("php://input")."\n", FILE_APPEND);

$data = json_decode(file_get_contents("php://input"), true);

// Dapatkan semua data Midtrans untuk validasi signature
$order_id = $data['order_id'] ?? '';
$transaction_status = $data['transaction_status'] ?? '';
$status_code = $data['status_code'] ?? '';
$gross_amount = $data['gross_amount'] ?? '';
$signature_key = $data['signature_key'] ?? '';

// HARUS SAMA dengan serverKey yang didaftarkan Midtrans
$serverKey = 'Mid-server-4bU4xow9Vq2yH-1WicaeTMiq';

// Format signature di dokumentasi Midtrans: order_id+status_code+gross_amount+serverKey
$expected_signature = hash('sha512', $order_id.$status_code.$gross_amount.$serverKey);

if ($signature_key !== $expected_signature) {
    http_response_code(403);
    echo 'Invalid signature key';
    exit;
}

if ($order_id && $transaction_status) {
    // Format order_id: ORDER-[id_booking]-[timestamp]
    $explode = explode('-', $order_id);
    $id_booking = intval($explode[1]);

    // Map status Midtrans ke DB
    $db_status = '';
    if ($transaction_status === 'settlement' || $transaction_status === 'capture') {
        $db_status = 'sukses';
    } elseif ($transaction_status === 'pending') {
        $db_status = 'pending';
    } elseif ($transaction_status === 'expire' || $transaction_status === 'cancel' || $transaction_status === 'deny') {
        $db_status = 'gagal';
    } else {
        $db_status = $transaction_status;
    }

    // Update tabel payments
    $stmtP = $conn->prepare("UPDATE payments SET status_pembayaran=? WHERE id_booking=?");
    $stmtP->bind_param("si", $db_status, $id_booking);
    $stmtP->execute();
    $stmtP->close();

    // Update tabel bookings
    $stmtB = $conn->prepare("UPDATE bookings SET status=? WHERE id_booking=?");
    $stmtB->bind_param("si", $db_status, $id_booking);
    $stmtB->execute();
    $stmtB->close();

    http_response_code(200);
    echo 'OK';
} else {
    http_response_code(400);
    echo 'Parameter kosong';
}
?>
