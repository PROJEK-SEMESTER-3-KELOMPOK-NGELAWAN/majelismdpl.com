<?php
// ✅ PENTING: Jangan ada spasi atau karakter sebelum <?php
require_once dirname(__FILE__, 2) . '/vendor/autoload.php';
require_once 'koneksi.php';

// ✅ Matikan SEMUA error display - hanya kirim JSON
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);

// ✅ Start session setelah matikan error
session_start();

// ✅ Set header JSON
header('Content-Type: application/json');

// ✅ Tangkap semua error dan kirim sebagai JSON
set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    echo json_encode([
        'error' => 'PHP Error',
        'message' => $errstr,
        'file' => basename($errfile),
        'line' => $errline
    ]);
    exit;
});

set_exception_handler(function ($e) {
    echo json_encode([
        'error' => 'Exception',
        'message' => $e->getMessage(),
        'file' => basename($e->getFile()),
        'line' => $e->getLine()
    ]);
    exit;
});

try {
    // Konfigurasi Midtrans
    \Midtrans\Config::$serverKey = 'Mid-server-4bU4xow9Vq2yH-1WicaeTMiq';
    \Midtrans\Config::$isProduction = false;
    \Midtrans\Config::$isSanitized = true;
    \Midtrans\Config::$is3ds = true;

    // ✅ ENDPOINT 1: Cek Status Pembayaran
    if (isset($_GET['status_id'])) {
        $id_booking = intval($_GET['status_id']);
        $status = 'unknown';

        if ($id_booking > 0) {
            $stmt = $conn->prepare("SELECT status_pembayaran FROM payments WHERE id_booking=?");
            if (!$stmt) {
                echo json_encode(['error' => 'Database prepare error: ' . $conn->error]);
                exit;
            }
            $stmt->bind_param("i", $id_booking);
            $stmt->execute();
            $stmt->bind_result($status);
            $stmt->fetch();
            $stmt->close();
        }

        echo json_encode(['status' => $status ?: 'no_payment']);
        exit;
    }

    // ✅ NEW ENDPOINT: Check Status dari Midtrans API
    if (isset($_GET['check_status'])) {
        $order_id = $_GET['check_status'];

        try {
            // Get status dari Midtrans API
            $status = \Midtrans\Transaction::status($order_id);

            $transaction_status = $status->transaction_status;
            $fraud_status = $status->fraud_status ?? 'accept';

            // Tentukan status pembayaran
            $status_pembayaran = 'pending';

            if ($transaction_status == 'capture') {
                if ($fraud_status == 'accept') {
                    $status_pembayaran = 'paid';
                }
            } else if ($transaction_status == 'settlement') {
                $status_pembayaran = 'paid';
            } else if ($transaction_status == 'pending') {
                $status_pembayaran = 'pending';
            } else if ($transaction_status == 'deny' || $transaction_status == 'expire' || $transaction_status == 'cancel') {
                $status_pembayaran = 'failed';
            }

            // Update database
            $stmt = $conn->prepare("UPDATE payments SET status_pembayaran=? WHERE order_id=?");
            if ($stmt) {
                $stmt->bind_param("ss", $status_pembayaran, $order_id);
                $stmt->execute();
                $stmt->close();
            }

            // Jika paid, update booking
            if ($status_pembayaran == 'paid') {
                $stmtBooking = $conn->prepare("
                    UPDATE bookings 
                    SET status='confirmed' 
                    WHERE id_booking=(SELECT id_booking FROM payments WHERE order_id=?)
                ");
                if ($stmtBooking) {
                    $stmtBooking->bind_param("s", $order_id);
                    $stmtBooking->execute();
                    $stmtBooking->close();
                }
            }

            echo json_encode([
                'success' => true,
                'status' => $status_pembayaran,
                'transaction_status' => $transaction_status
            ]);
            exit;
        } catch (Exception $e) {
            echo json_encode([
                'error' => 'Failed to check status',
                'message' => $e->getMessage()
            ]);
            exit;
        }
    }

    // ✅ ENDPOINT 2: Generate Snap Token untuk Pembayaran
    if (isset($_GET['booking'])) {
        $id_booking = intval($_GET['booking']);

        if ($id_booking <= 0) {
            echo json_encode(['error' => 'ID booking tidak valid']);
            exit;
        }

        // Ambil data booking
        $stmt = $conn->prepare("
            SELECT b.*, t.nama_gunung, t.harga, u.username, u.email
            FROM bookings b
            JOIN paket_trips t ON b.id_trip = t.id_trip
            JOIN users u ON b.id_user = u.id_user
            WHERE b.id_booking = ?
        ");

        if (!$stmt) {
            echo json_encode(['error' => 'Database prepare error: ' . $conn->error]);
            exit;
        }

        $stmt->bind_param("i", $id_booking);
        $stmt->execute();
        $booking = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$booking) {
            echo json_encode(['error' => 'Booking tidak ditemukan']);
            exit;
        }

        // Generate order_id yang unik
        $order_id = 'ORDER-' . $id_booking . '-' . time();

        $params = array(
            'transaction_details' => array(
                'order_id' => $order_id,
                'gross_amount' => intval($booking['total_harga'])
            ),
            'customer_details' => array(
                'first_name' => $booking['username'],
                'email'      => $booking['email']
            ),
            'item_details' => array([
                'id' => $booking['id_trip'],
                'price' => intval($booking['total_harga']),
                'quantity' => 1,
                'name' => $booking['nama_gunung']
            ])
        );

        $snapToken = \Midtrans\Snap::getSnapToken($params);

        // Cek apakah payment sudah ada
        $cek = $conn->prepare("SELECT id_payment FROM payments WHERE id_booking=?");
        if (!$cek) {
            echo json_encode(['error' => 'Database error: ' . $conn->error]);
            exit;
        }

        $cek->bind_param('i', $id_booking);
        $cek->execute();
        $cek->store_result();

        if ($cek->num_rows == 0) {
            // Insert payment baru dengan status pending
            $status = 'pending';
            $jenis = 'trip';
            $metode = 'midtrans';

            $stmtPay = $conn->prepare("INSERT INTO payments
                (id_booking, jumlah_bayar, tanggal, jenis_pembayaran, metode, status_pembayaran, order_id)
                VALUES (?, ?, CURDATE(), ?, ?, ?, ?)");

            if (!$stmtPay) {
                echo json_encode(['error' => 'Insert payment error: ' . $conn->error]);
                exit;
            }

            $stmtPay->bind_param(
                "iissss",
                $id_booking,
                $params['transaction_details']['gross_amount'],
                $jenis,
                $metode,
                $status,
                $order_id
            );
            $stmtPay->execute();
            $stmtPay->close();
        } else {
            // Update order_id untuk transaksi yang sudah ada
            $updateOrder = $conn->prepare("UPDATE payments SET order_id=?, status_pembayaran='pending' WHERE id_booking=?");
            if ($updateOrder) {
                $updateOrder->bind_param("si", $order_id, $id_booking);
                $updateOrder->execute();
                $updateOrder->close();
            }
        }
        $cek->close();

        echo json_encode([
            'success' => true,
            'snap_token' => $snapToken,
            'order_id' => $order_id
        ]);
        exit;
    }

    // ✅ ENDPOINT 3: Webhook Midtrans
    $json = file_get_contents('php://input');

    // ✅ LOG webhook untuk debugging
    if (!empty($json)) {
        $log_file = dirname(__DIR__) . '/logs/webhook-' . date('Y-m-d') . '.log';
        $log_dir = dirname($log_file);
        if (!is_dir($log_dir)) {
            mkdir($log_dir, 0755, true);
        }
        file_put_contents($log_file, date('Y-m-d H:i:s') . " - " . $json . "\n", FILE_APPEND);
    }

    $notification = json_decode($json);

    if (!$notification) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid notification']);
        exit;
    }

    // Verifikasi signature dari Midtrans
    $validSignature = hash(
        'sha512',
        $notification->order_id .
            $notification->status_code .
            $notification->gross_amount .
            \Midtrans\Config::$serverKey
    );

    if ($notification->signature_key !== $validSignature) {
        http_response_code(403);
        echo json_encode(['error' => 'Invalid signature']);
        exit;
    }

    $order_id = $notification->order_id;
    $transaction_status = $notification->transaction_status;
    $fraud_status = $notification->fraud_status ?? 'accept';

    // Tentukan status pembayaran
    $status_pembayaran = 'pending';

    if ($transaction_status == 'capture') {
        if ($fraud_status == 'accept') {
            $status_pembayaran = 'paid';
        }
    } else if ($transaction_status == 'settlement') {
        $status_pembayaran = 'paid';
    } else if ($transaction_status == 'pending') {
        $status_pembayaran = 'pending';
    } else if ($transaction_status == 'deny' || $transaction_status == 'expire' || $transaction_status == 'cancel') {
        $status_pembayaran = 'failed';
    }

    // Update status pembayaran di database
    $stmt = $conn->prepare("UPDATE payments SET status_pembayaran=? WHERE order_id=?");
    if ($stmt) {
        $stmt->bind_param("ss", $status_pembayaran, $order_id);
        $stmt->execute();
        $affected = $stmt->affected_rows;
        $stmt->close();
    } else {
        $affected = 0;
    }

    // Jika status paid, update juga status booking
    if ($status_pembayaran == 'paid') {
        $stmtBooking = $conn->prepare("
            UPDATE bookings 
            SET status='confirmed' 
            WHERE id_booking=(SELECT id_booking FROM payments WHERE order_id=?)
        ");
        if ($stmtBooking) {
            $stmtBooking->bind_param("s", $order_id);
            $stmtBooking->execute();
            $stmtBooking->close();
        }
    }

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Notification processed',
        'status' => $status_pembayaran,
        'affected_rows' => $affected
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Exception caught',
        'message' => $e->getMessage()
    ]);
}

