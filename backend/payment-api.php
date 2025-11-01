<?php
require_once dirname(__FILE__, 2) . '/vendor/autoload.php';
require_once 'koneksi.php';
require_once __DIR__ . '/helpers/Mailer.php';

use App\Helpers\Mailer;

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

function base_app_url(): string
{
    return 'http://localhost/majelismdpl.com';
}

function getOrderMeta(mysqli $conn, string $order_id): array
{
    $sql = "SELECT u.username, u.email, b.id_booking, b.tanggal_booking, b.total_harga, t.nama_gunung,
                   p.id_payment, p.status_pembayaran, p.sent_pending_email, p.sent_paid_email
            FROM payments p
            JOIN bookings b ON p.id_booking=b.id_booking
            JOIN users u    ON b.id_user=u.id_user
            JOIN paket_trips t ON b.id_trip=t.id_trip
            WHERE p.order_id=?";
    $row = [];
    if ($st = $conn->prepare($sql)) {
        $st->bind_param("s", $order_id);
        $st->execute();
        $res = $st->get_result();
        $row = $res->fetch_assoc() ?: [];
        $st->close();
    }
    return $row;
}
function markEmailFlag(mysqli $conn, string $order_id, string $flagCol): void
{
    if (!in_array($flagCol, ['sent_pending_email', 'sent_paid_email'], true)) return;
    $sql = "UPDATE payments SET {$flagCol}=1 WHERE order_id=? AND {$flagCol}=0";
    if ($st = $conn->prepare($sql)) {
        $st->bind_param("s", $order_id);
        $st->execute();
        $st->close();
    }
}

// ========= endpoint: status_id =========
if (isset($_GET['status_id'])) {
    $id_booking = intval($_GET['status_id']);
    $status = 'unknown';
    if ($id_booking > 0) {
        $st = $conn->prepare("SELECT status_pembayaran FROM payments WHERE id_booking=? ORDER BY id_payment DESC LIMIT 1");
        if (!$st) $respond(500, ['error' => 'Database prepare error: ' . $conn->error]);
        $st->bind_param("i", $id_booking);
        $st->execute();
        $st->bind_result($status);
        $st->fetch();
        $st->close();
    }
    $respond(200, ['status' => $status ?: 'no_payment']);
}

// ========= endpoint: check_status =========
if (isset($_GET['check_status'])) {
    $order_id = trim($_GET['check_status']);
    if ($order_id === '') $respond(400, ['error' => 'Order ID required']);

    try {
        $status = \Midtrans\Transaction::status($order_id);
        $transaction_status = $status->transaction_status ?? 'pending';
        $fraud_status = $status->fraud_status ?? 'accept';
        $status_pembayaran = $mapStatus($transaction_status, $fraud_status);

        $conn->begin_transaction();
        $st = $conn->prepare("UPDATE payments SET status_pembayaran=? WHERE order_id=?");
        if (!$st) {
            $conn->rollback();
            $respond(500, ['error' => 'Database error: ' . $conn->error]);
        }
        $st->bind_param("ss", $status_pembayaran, $order_id);
        $st->execute();
        $st->close();

        if ($status_pembayaran === 'paid') {
            $sb = $conn->prepare("UPDATE bookings SET status='confirmed' WHERE id_booking=(SELECT id_booking FROM payments WHERE order_id=?)");
            if ($sb) {
                $sb->bind_param("s", $order_id);
                $sb->execute();
                $sb->close();
            }
        }
        $conn->commit();

        // email idempoten
        $m = getOrderMeta($conn, $order_id);
        if ($m && !empty($m['email'])) {
            $payload = [
                'order_id'        => $order_id,
                'nama_user'       => $m['username'] ?? '',
                'nama_gunung'     => $m['nama_gunung'] ?? '',
                'tanggal_booking' => isset($m['tanggal_booking']) ? date('d-m-Y', strtotime($m['tanggal_booking'])) : '-',
                'total_harga'     => $m['total_harga'] ?? 0,
                'invoice_url'     => base_app_url() . '/user/view-invoice.php?payment_id=' . ((int)($m['id_payment'] ?? 0)),
                'payment_status_url' => base_app_url() . '/user/payment-status.php'
            ];
            if ($status_pembayaran === 'paid' && (int)($m['sent_paid_email'] ?? 0) === 0) {
                $html = Mailer::buildPaidTemplate($payload);
                $ok = Mailer::send($m['email'], $m['username'] ?? '', 'Pembayaran Berhasil - Majelis MDPL', $html, 'Pembayaran berhasil');
                if (($ok['ok'] ?? false) === true) markEmailFlag($conn, $order_id, 'sent_paid_email');
            }
        }

        $respond(200, ['success' => true, 'status' => $status_pembayaran, 'transaction_status' => $transaction_status]);
    } catch (\Exception $e) {
        if ($conn && $conn->errno) $conn->rollback();
        $respond(500, ['error' => 'Failed to check status', 'message' => $e->getMessage()]);
    }
}

// ========= endpoint: booking (buat snap token + kirim pending 1x) =========
if (isset($_GET['booking'])) {
    $id_booking = intval($_GET['booking']);
    if ($id_booking <= 0) $respond(400, ['error' => 'ID booking tidak valid']);

    $st = $conn->prepare("SELECT b.*, t.nama_gunung, t.harga, u.username, u.email
                        FROM bookings b
                        JOIN paket_trips t ON b.id_trip=t.id_trip
                        JOIN users u ON b.id_user=u.id_user
                        WHERE b.id_booking=?");
    if (!$st) $respond(500, ['error' => 'Database prepare error: ' . $conn->error]);
    $st->bind_param("i", $id_booking);
    $st->execute();
    $booking = $st->get_result()->fetch_assoc();
    $st->close();
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

    // cek payment terakhir user ini
    $cek = $conn->prepare("SELECT id_payment,status_pembayaran,sent_pending_email FROM payments WHERE id_booking=? ORDER BY id_payment DESC LIMIT 1");
    if (!$cek) $respond(500, ['error' => 'Database error: ' . $conn->error]);
    $cek->bind_param('i', $id_booking);
    $cek->execute();
    $cek->bind_result($last_id, $last_status, $last_sent_pending);
    $has = $cek->fetch();
    $cek->close();

    $id_payment = 0;
    if (!$has) {
        $ins = $conn->prepare("INSERT INTO payments (id_booking,jumlah_bayar,tanggal,jenis_pembayaran,metode,status_pembayaran,order_id,sent_pending_email,sent_paid_email)
                             VALUES (?, ?, CURDATE(),'trip','midtrans','pending',?,0,0)");
        if (!$ins) $respond(500, ['error' => 'Insert payment error: ' . $conn->error]);
        $gross = intval($booking['total_harga']);
        $ins->bind_param("iis", $id_booking, $gross, $order_id);
        $ins->execute();
        $id_payment = $ins->insert_id;
        $ins->close();
        $last_status = 'pending';
        $last_sent_pending = 0;
    } else {
        $upd = $conn->prepare("UPDATE payments SET order_id=?, status_pembayaran='pending' WHERE id_payment=?");
        if ($upd) {
            $upd->bind_param("si", $order_id, $last_id);
            $upd->execute();
            $upd->close();
        }
        $id_payment = $last_id;
    }

    // Kirim email pending hanya jika belum pernah dikirim (flag 0)
    if ($last_status === 'pending' && (int)$last_sent_pending === 0 && !empty($booking['email'])) {
        $payload = [
            'order_id' => $order_id,
            'nama_user' => $booking['username'] ?? '',
            'nama_gunung' => $booking['nama_gunung'] ?? '',
            'total_harga' => $booking['total_harga'] ?? 0,
            'payment_status_url' => base_app_url() . '/user/payment-status.php',
            'invoice_url' => base_app_url() . '/user/view-invoice.php?payment_id=' . $id_payment
        ];
        $html = Mailer::buildPendingTemplate($payload);
        $ok = Mailer::send($booking['email'], $booking['username'] ?? '', 'Segera Selesaikan Pembayaran - Majelis MDPL', $html, 'Segera selesaikan pembayaran Anda');
        if (($ok['ok'] ?? false) === true) {
            $flag = $conn->prepare("UPDATE payments SET sent_pending_email=1 WHERE order_id=?");
            if ($flag) {
                $flag->bind_param("s", $order_id);
                $flag->execute();
                $flag->close();
            }
        }
    }

    $respond(200, ['success' => true, 'snap_token' => $snapToken, 'order_id' => $order_id]);
}

// ========= webhook =========
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
$st = $conn->prepare("UPDATE payments SET status_pembayaran=? WHERE order_id=?");
if ($st) {
    $st->bind_param("ss", $status_pembayaran, $order_id);
    $st->execute();
    $affected = $st->affected_rows;
    $st->close();
} else {
    $conn->rollback();
    http_response_code(500);
    $respond(500, ['error' => 'Database error: ' . $conn->error]);
}
if ($status_pembayaran === 'paid') {
    $sb = $conn->prepare("UPDATE bookings SET status='confirmed' WHERE id_booking=(SELECT id_booking FROM payments WHERE order_id=?)");
    if ($sb) {
        $sb->bind_param("s", $order_id);
        $sb->execute();
        $sb->close();
    }
}
$conn->commit();

// Email paid idempoten
$m = getOrderMeta($conn, $order_id);
if ($m && !empty($m['email'])) {
    $payload = [
        'order_id' => $order_id,
        'nama_user' => $m['username'] ?? '',
        'nama_gunung' => $m['nama_gunung'] ?? '',
        'tanggal_booking' => isset($m['tanggal_booking']) ? date('d-m-Y', strtotime($m['tanggal_booking'])) : '-',
        'total_harga' => $m['total_harga'] ?? 0,
        'invoice_url' => base_app_url() . '/user/view-invoice.php?payment_id=' . ((int)($m['id_payment'] ?? 0)),
        'payment_status_url' => base_app_url() . '/user/payment-status.php'
    ];
    if ($status_pembayaran === 'paid' && (int)($m['sent_paid_email'] ?? 0) === 0) {
        $html = Mailer::buildPaidTemplate($payload);
        $ok = Mailer::send($m['email'], $m['username'] ?? '', 'Pembayaran Berhasil - Majelis MDPL', $html, 'Pembayaran berhasil');
        if (($ok['ok'] ?? false) === true) markEmailFlag($conn, $order_id, 'sent_paid_email');
    }
}

http_response_code(200);
$respond(200, ['success' => true, 'message' => 'Notification processed', 'status' => $status_pembayaran, 'affected_rows' => $affected ?? 0]);
