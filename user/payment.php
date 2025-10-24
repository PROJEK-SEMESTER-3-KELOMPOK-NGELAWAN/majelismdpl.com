<?php
require_once '../backend/koneksi.php';
session_start();

$id_booking = intval($_GET['id'] ?? 0);

$stmt = $conn->prepare("
    SELECT b.*, t.nama_gunung, t.harga, u.username, u.email
    FROM bookings b
    JOIN paket_trips t ON b.id_trip = t.id_trip
    JOIN users u ON b.id_user = u.id_user
    WHERE b.id_booking = ?
");
$stmt->bind_param("i", $id_booking);
$stmt->execute();
$result = $stmt->get_result();
$booking = $result->fetch_assoc();
$stmt->close();

if (!$booking) { echo "Booking tidak ditemukan"; exit(); }
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Pembayaran Trip <?=htmlspecialchars($booking['nama_gunung'])?></title>
    <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="YOUR_CLIENT_KEY"></script>
</head>
<body>
<h2>Pembayaran Trip: <?=htmlspecialchars($booking['nama_gunung'])?></h2>
<p>Pemesan: <?=htmlspecialchars($booking['username'])?> (<?=htmlspecialchars($booking['email'])?>)</p>
<p>Jumlah Peserta: <?=$booking['jumlah_orang']?></p>
<p>Total: Rp <?=number_format($booking['total_harga'],0,',','.')?></p>
<p>Status pembayaran: <b id="payment-status"><?=$booking['status']?></b></p>
<button id="btn-bayar">Bayar Sekarang</button>
<div id="hasil"></div>
<script>
document.getElementById('btn-bayar').onclick = function() {
    fetch('../backend/payment-api.php?booking=<?=$id_booking?>')
        .then(r => r.json())
        .then(resp => {
            if(resp.snap_token){
                window.snap.pay(resp.snap_token, {
                    onSuccess: function(result){
                        document.getElementById('hasil').innerHTML = "Pembayaran sukses!";
                        setTimeout(function(){ location.reload(); }, 2000); // reload status
                    },
                    onPending: function(result){
                        document.getElementById('hasil').innerHTML = "Pembayaran pending.";
                    },
                    onError: function(result){
                        document.getElementById('hasil').innerHTML = "Pembayaran gagal: " + (result && result.status_message ? result.status_message : 'Unknown error');
                    },
                    onClose: function(){
                        document.getElementById('hasil').innerHTML = "Popup pembayaran ditutup.";
                    }
                });
            } else {
                document.getElementById('hasil').innerHTML = 'Error: ' + (resp.error || 'Gagal ambil Snap Token');
            }
        })
        .catch(function(err){
            document.getElementById('hasil').innerHTML = 'Request gagal: ' + err;
        });
};

// Polling status tiap 4 detik
setInterval(function() {
    fetch('../backend/payment-status-api.php?id=<?=$id_booking?>')
    .then(r => r.json())
    .then(res => {
        if(res.status)
            document.getElementById('payment-status').textContent = res.status;
    });
}, 4000);
</script>
</body>
</html>
