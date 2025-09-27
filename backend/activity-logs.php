<?php
require_once 'koneksi.php';

// Cari apakah ada log lebih dari 30 hari sebelum dihapus
$delCheck = $conn->query("SELECT COUNT(*) as cnt FROM activity_logs WHERE waktu < NOW() - INTERVAL 30 DAY");
$delRow = $delCheck->fetch_assoc();

// Reset activity log jika ada yang >30 hari
$wasReset = false;
if ($delRow['cnt'] > 0) {
    $conn->query("DELETE FROM activity_logs WHERE waktu < NOW() - INTERVAL 30 DAY");
    $wasReset = true;
}

// Query log terbaru
$sql = "SELECT activity_logs.aktivitas, activity_logs.waktu, activity_logs.status, users.username
        FROM activity_logs
        JOIN users ON activity_logs.id_user = users.id_user
        ORDER BY activity_logs.waktu DESC
        LIMIT 10";
$res = $conn->query($sql);

// Siapkan isi tabel log
$rows = '';
$no = 1;
while ($row = $res->fetch_assoc()) {
    $rows .= "<tr>
        <td>{$no}</td>
        <td>{$row['aktivitas']}</td>
        <td>{$row['username']}</td>
        <td>" . date('d/m/Y H:i', strtotime($row['waktu'])) . "</td>
        <td><span class='badge badge-{$row['status']}'>{$row['status']}</span></td>
      </tr>";
    $no++;
}

// Siapkan flag JS untuk pop up jika dilakukan reset
if ($wasReset) {
    echo "<script>
    setTimeout(function() {
        Swal.fire({
            icon: 'success',
            title: 'Riwayat aktivitas sudah direset',
            text: 'Riwayat aktivitas otomatis direset karena sudah 30 hari.',
            confirmButtonText: 'OK'
        });
    }, 400);
    </script>";
}
