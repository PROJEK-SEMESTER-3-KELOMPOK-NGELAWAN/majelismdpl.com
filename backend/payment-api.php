<?php
require_once dirname(__FILE__, 2) . '/vendor/autoload.php';
require_once 'koneksi.php';

$helperPath = __DIR__ . '/helpers/Mailer.php';
if (file_exists($helperPath)) require_once $helperPath;

use App\Helpers\Mailer;

ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);

session_start();
header('Content-Type: application/json');

function app_log($msg)
{
    // no-op
}

set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    app_log("PHP[$errno] $errstr @ " . basename($errfile) . ":$errline");
    echo json_encode(['error' => 'PHP Error', 'message' => $errstr, 'where' => basename($errfile) . ':' . $errline]);
    exit;
});

set_exception_handler(function ($e) {
    app_log("EXC " . $e->getMessage());
    echo json_encode(['error' => 'Exception', 'message' => $e->getMessage()]);
    exit;
});

\Midtrans\Config::$serverKey    = 'Mid-server-4bU4xow9Vq2yH-1WicaeTMiq';
\Midtrans\Config::$isProduction = false;
\Midtrans\Config::$isSanitized  = true;
\Midtrans\Config::$is3ds        = true;

$mapStatus = function ($transaction_status, $fraud_status) {
    $fraud_status = $fraud_status ?? 'accept';
    if ($transaction_status === 'capture') return ($fraud_status === 'accept') ? 'paid' : 'pending';
    if ($transaction_status === 'settlement') return 'paid';
    if ($transaction_status === 'pending') return 'pending';
    if ($transaction_status === 'deny') return 'failed';
    if ($transaction_status === 'expire') return 'expire';
    if ($transaction_status === 'cancel') return 'cancel';
    return 'pending';
};

$deleteParticipants = function (mysqli $conn, int $id_booking) {
    if ($st = $conn->prepare("DELETE FROM participants WHERE id_booking=?")) {
        $st->bind_param("i", $id_booking);
        $st->execute();
        $st->close();
    }
};

$setBookingStatus = function (mysqli $conn, int $id_booking, string $status, bool $wipe) use ($deleteParticipants) {
    if ($st = $conn->prepare("UPDATE bookings SET status=? WHERE id_booking=?")) {
        $st->bind_param("si", $status, $id_booking);
        $st->execute();
        $st->close();
    }
    if ($wipe) $deleteParticipants($conn, $id_booking);
};

$getEmailPack = function (mysqli $conn, int $id_booking, ?string $order_id = null): ?array {
    $q = $conn->prepare("
        SELECT b.id_booking, b.tanggal_booking, b.total_harga, t.nama_gunung, u.username, u.email, p.order_id, p.id_payment
        FROM bookings b
        JOIN paket_trips t ON t.id_trip=b.id_trip
        JOIN users u ON u.id_user=b.id_user
        LEFT JOIN payments p ON p.id_booking=b.id_booking
        WHERE b.id_booking=? LIMIT 1
    ");
    if (!$q) return null;
    $q->bind_param("i", $id_booking);
    $q->execute();
    $res = $q->get_result()->fetch_assoc();
    $q->close();
    if (!$res) return null;
    if ($order_id) $res['order_id'] = $order_id;
    $res['tanggal_booking'] = date('d M Y', strtotime($res['tanggal_booking']));
    return $res;
};

$sendPendingMail = function (mysqli $conn, int $id_booking) use ($getEmailPack) {
    if (!class_exists(Mailer::class)) return;
    $d = $getEmailPack($conn, $id_booking);
    if (!$d) return;
    $html = Mailer::buildPendingTemplate([
        'order_id' => $d['order_id'] ?? '',
        'nama_gunung' => $d['nama_gunung'],
        'tanggal_booking' => $d['tanggal_booking'],
        'total_harga' => $d['total_harga'],
        'nama_user' => $d['username']
    ]);
    Mailer::send($d['email'], $d['username'], 'Menunggu Pembayaran', $html, 'Menunggu pembayaran');
};

$sendPaidMail = function (mysqli $conn, int $id_booking) use ($getEmailPack) {
    if (!class_exists(Mailer::class)) return;
    $d = $getEmailPack($conn, $id_booking);
    if (!$d) return;
    $html = Mailer::buildPaidTemplate([
        'order_id' => $d['order_id'] ?? '',
        'nama_gunung' => $d['nama_gunung'],
        'tanggal_booking' => $d['tanggal_booking'],
        'total_harga' => $d['total_harga'],
        'nama_user' => $d['username'],
        'payment_id' => $d['id_payment'] ?? ''
    ]);
    Mailer::send($d['email'], $d['username'], 'Pembayaran Berhasil', $html, 'Pembayaran berhasil');
};

$sendFailedMail = function (mysqli $conn, int $id_booking, string $variant) use ($getEmailPack) {
    if (!class_exists(Mailer::class)) return;
    $d = $getEmailPack($conn, $id_booking);
    if (!$d) return;
    $html = Mailer::buildFailedTemplate([
        'order_id' => $d['order_id'] ?? '',
        'nama_gunung' => $d['nama_gunung'],
        'tanggal_booking' => $d['tanggal_booking'],
        'total_harga' => $d['total_harga'],
        'nama_user' => $d['username']
    ]);
    $subject = $variant === 'expire' ? 'Pesanan Kedaluwarsa' : ($variant === 'cancel' ? 'Pesanan Dibatalkan' : 'Pembayaran Gagal');
    Mailer::send($d['email'], $d['username'], $subject, $html, $subject);
};

// ===== Cancel manual
if (isset($_POST['cancel_booking'])) {
    $id_booking = (int)($_POST['cancel_booking'] ?? 0);
    if ($id_booking <= 0) {
        echo json_encode(['error' => 'Invalid booking id']);
        exit;
    }
    $conn->begin_transaction();
    if ($up = $conn->prepare("UPDATE payments SET status_pembayaran='cancel' WHERE id_booking=?")) {
        $up->bind_param("i", $id_booking);
        $up->execute();
        $up->close();
    } else {
        $conn->rollback();
        echo json_encode(['error' => 'Database error: ' . $conn->error]);
        exit;
    }
    $setBookingStatus($conn, $id_booking, 'cancelled', true);
    $conn->commit();
    $sendFailedMail($conn, $id_booking, 'cancel');
    echo json_encode(['success' => true, 'status' => 'cancel']);
    exit;
}

// ===== Cek status lokal
if (isset($_GET['status_id'])) {
    $id_booking = (int)($_GET['status_id'] ?? 0);
    $status = 'unknown';
    if ($id_booking > 0 && ($st = $conn->prepare("SELECT status_pembayaran FROM payments WHERE id_booking=?"))) {
        $st->bind_param("i", $id_booking);
        $st->execute();
        $st->bind_result($status);
        $st->fetch();
        $st->close();
    }
    echo json_encode(['status' => $status ?: 'no_payment']);
    exit;
}

// ===== Check Midtrans (poll)
if (isset($_GET['check_status'])) {
    $order_id = trim($_GET['check_status']);
    if ($order_id === '') {
        echo json_encode(['error' => 'Order ID required']);
        exit;
    }

    try {
        $status = \Midtrans\Transaction::status($order_id);
        $transaction_status = $status->transaction_status ?? 'pending';
        $fraud_status = $status->fraud_status ?? 'accept';
        $status_pembayaran = $mapStatus($transaction_status, $fraud_status);

        $conn->begin_transaction();
        if ($st = $conn->prepare("UPDATE payments SET status_pembayaran=? WHERE order_id=?")) {
            $st->bind_param("ss", $status_pembayaran, $order_id);
            $st->execute();
            $st->close();
        } else {
            $conn->rollback();
            echo json_encode(['error' => 'Database error: ' . $conn->error]);
            exit;
        }

        $id_booking = null;
        if ($g = $conn->prepare("SELECT id_booking, sent_paid_email FROM payments WHERE order_id=? LIMIT 1")) {
            $g->bind_param("s", $order_id);
            $g->execute();
            $g->bind_result($id_booking, $sentPaid);
            $g->fetch();
            $g->close();
            $id_booking = (int)$id_booking;
        }

        if ($id_booking) {
            if ($status_pembayaran === 'paid') {
                $setBookingStatus($conn, $id_booking, 'confirmed', false);
                if ((int)($sentPaid ?? 0) === 0) {
                    $sendPaidMail($conn, $id_booking);
                    if ($u = $conn->prepare("UPDATE payments SET sent_paid_email=1 WHERE id_booking=?")) {
                        $u->bind_param("i", $id_booking);
                        $u->execute();
                        $u->close();
                    }
                }
            } elseif (in_array($status_pembayaran, ['failed', 'expire', 'cancel'])) {
                $setBookingStatus($conn, $id_booking, 'cancelled', true);
                $sendFailedMail($conn, $id_booking, $status_pembayaran);
            }
        }
        $conn->commit();
        echo json_encode(['success' => true, 'status' => $status_pembayaran, 'transaction_status' => $transaction_status]);
        exit;
    } catch (\Exception $e) {
        app_log("check_status exception: " . $e->getMessage());
        echo json_encode(['success' => false, 'error' => 'midtrans_exception', 'message' => $e->getMessage()]);
        exit;
    }
}

// ===== Auto-expire >24 jam
if (isset($_GET['expire_stale'])) {
    $conn->begin_transaction();
    $q = $conn->query("
        SELECT p.id_payment, p.id_booking
        FROM payments p
        WHERE p.status_pembayaran='pending'
          AND TIMESTAMPDIFF(HOUR, CONCAT(p.tanggal,' 00:00:00'), NOW()) >= 24
    ");
    $expired = 0;
    if ($q) {
        while ($r = $q->fetch_assoc()) {
            $id_booking = (int)$r['id_booking'];
            if ($up = $conn->prepare("UPDATE payments SET status_pembayaran='expire' WHERE id_payment=?")) {
                $up->bind_param("i", $r['id_payment']);
                $up->execute();
                $up->close();
            }
            $setBookingStatus($conn, $id_booking, 'cancelled', true);
            $sendFailedMail($conn, $id_booking, 'expire');
            $expired++;
        }
    }
    $conn->commit();
    echo json_encode(['success' => true, 'expired' => $expired]);
    exit;
}

// ===== Generate Snap Token
if (isset($_GET['booking'])) {
    $id_booking = (int)($_GET['booking'] ?? 0);
    if ($id_booking <= 0) {
        echo json_encode(['error' => 'ID booking tidak valid']);
        exit;
    }

    $st = $conn->prepare("
        SELECT b.id_booking, b.status, b.total_harga, t.nama_gunung, t.id_trip, u.username, u.email
        FROM bookings b
        JOIN paket_trips t ON b.id_trip=t.id_trip
        JOIN users u ON b.id_user=u.id_user
        WHERE b.id_booking=?
    ");
    if (!$st) {
        echo json_encode(['error' => 'Database prepare error: ' . $conn->error]);
        exit;
    }
    $st->bind_param("i", $id_booking);
    $st->execute();
    $booking = $st->get_result()->fetch_assoc();
    $st->close();

    if (!$booking) {
        echo json_encode(['error' => 'Booking tidak ditemukan']);
        exit;
    }
    if (in_array($booking['status'], ['cancelled', 'confirmed'])) {
        echo json_encode(['error' => 'Booking tidak tersedia untuk pembayaran']);
        exit;
    }

    $grossAmount = (int)($booking['total_harga'] ?? 0);
    if ($grossAmount <= 0) {
        echo json_encode(['error' => 'Total harga tidak valid']);
        exit;
    }

    $customerName  = trim((string)($booking['username'] ?? 'User'));
    $customerEmail = trim((string)($booking['email'] ?? ''));
    if ($customerEmail === '') {
        echo json_encode(['error' => 'Email pengguna kosong']);
        exit;
    }

    $itemName = trim((string)($booking['nama_gunung'] ?? 'Trip'));
    if ($itemName === '') $itemName = 'Trip ' . $booking['id_trip'];

    $order_id = 'ORDER-' . $id_booking . '-' . time();

    $params = [
        'transaction_details' => [
            'order_id' => $order_id,
            'gross_amount' => $grossAmount
        ],
        'customer_details' => [
            'first_name' => $customerName,
            'email' => $customerEmail
        ],
        'item_details' => [[
            'id' => $booking['id_trip'],
            'price' => $grossAmount,
            'quantity' => 1,
            'name' => $itemName
        ]]
    ];

    try {
        $snapToken = \Midtrans\Snap::getSnapToken($params);
    } catch (\Exception $e) {
        app_log('getSnapToken error: ' . $e->getMessage() . ' params=' . json_encode($params));
        echo json_encode(['error' => 'Gagal membuat token pembayaran', 'detail' => $e->getMessage()]);
        exit;
    }

    $cek = $conn->prepare("SELECT id_payment, sent_pending_email FROM payments WHERE id_booking=?");
    if (!$cek) {
        echo json_encode(['error' => 'Database error: ' . $conn->error]);
        exit;
    }
    $cek->bind_param("i", $id_booking);
    $cek->execute();
    $cek->store_result();

    if ($cek->num_rows == 0) {
        $stmtPay = $conn->prepare("INSERT INTO payments
            (id_booking, jumlah_bayar, tanggal, jenis_pembayaran, metode, status_pembayaran, order_id, sent_pending_email, sent_paid_email)
            VALUES (?, ?, CURDATE(), 'trip', 'midtrans', 'pending', ?, 0, 0)");
        if (!$stmtPay) {
            echo json_encode(['error' => 'Insert payment error: ' . $conn->error]);
            exit;
        }
        $stmtPay->bind_param("iis", $id_booking, $grossAmount, $order_id);
        $stmtPay->execute();
        $stmtPay->close();

        $sendPendingMail($conn, $id_booking);
        $conn->query("UPDATE payments SET sent_pending_email=1 WHERE id_booking=" . $id_booking);
    } else {
        $cek->bind_result($id_payment, $sentPending);
        $cek->fetch();
        if ($u = $conn->prepare("UPDATE payments SET order_id=?, status_pembayaran='pending' WHERE id_booking=?")) {
            $u->bind_param("si", $order_id, $id_booking);
            $u->execute();
            $u->close();
        }
        if ((int)$sentPending === 0) {
            $sendPendingMail($conn, $id_booking);
            if ($uu = $conn->prepare("UPDATE payments SET sent_pending_email=1 WHERE id_payment=?")) {
                $uu->bind_param("i", $id_payment);
                $uu->execute();
                $uu->close();
            }
        }
    }
    $cek->close();

    echo json_encode(['success' => true, 'snap_token' => $snapToken, 'order_id' => $order_id]);
    exit;
}

// ===== Webhook
$json = file_get_contents('php://input');
if (empty($json)) {
    echo json_encode(['ok' => true]);
    exit;
}

$notification = json_decode($json);
if (!$notification) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid notification']);
    exit;
}

$validSignature = hash(
    'sha512',
    $notification->order_id .
        $notification->status_code .
        $notification->gross_amount .
        \Midtrans\Config::$serverKey
);
if (($notification->signature_key ?? '') !== $validSignature) {
    http_response_code(403);
    echo json_encode(['error' => 'Invalid signature']);
    exit;
}

$order_id = $notification->order_id;
$transaction_status = $notification->transaction_status ?? 'pending';
$fraud_status = $notification->fraud_status ?? 'accept';
$status_pembayaran = $mapStatus($transaction_status, $fraud_status);

$conn->begin_transaction();
if ($st = $conn->prepare("UPDATE payments SET status_pembayaran=? WHERE order_id=?")) {
    $st->bind_param("ss", $status_pembayaran, $order_id);
    $st->execute();
    $st->close();
} else {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $conn->error]);
    exit;
}

$id_booking = null;
$sentPaid = 0;
if ($gb = $conn->prepare("SELECT id_booking, sent_paid_email FROM payments WHERE order_id=? LIMIT 1")) {
    $gb->bind_param("s", $order_id);
    $gb->execute();
    $gb->bind_result($id_booking, $sentPaid);
    $gb->fetch();
    $gb->close();
    $id_booking = (int)$id_booking;
}
if ($id_booking) {
    if ($status_pembayaran === 'paid') {
        $setBookingStatus($conn, $id_booking, 'confirmed', false);
        if ((int)$sentPaid === 0) {
            $sendPaidMail($conn, $id_booking);
            if ($u = $conn->prepare("UPDATE payments SET sent_paid_email=1 WHERE id_booking=?")) {
                $u->bind_param("i", $id_booking);
                $u->execute();
                $u->close();
            }
        }
    } elseif (in_array($status_pembayaran, ['failed', 'expire', 'cancel'])) {
        $setBookingStatus($conn, $id_booking, 'cancelled', true);
        $sendFailedMail($conn, $id_booking, $status_pembayaran);
    }
}
$conn->commit();
http_response_code(200);
echo json_encode(['success' => true, 'message' => 'Notification processed', 'status' => $status_pembayaran]);
