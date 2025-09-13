<?php
session_start();

// Load data dari JSON 
$jsonFile = __DIR__ . "/payments.json";
if (file_exists($jsonFile)) {
    $payments = json_decode(file_get_contents($jsonFile), true);
} else {
    $payments = [];
}

// Hitung statistik
$totalLunas = count(array_filter($payments, fn($p) => $p['status_pembayaran'] === 'Paid'));
$totalUnpaid = count(array_filter($payments, fn($p) => $p['status_pembayaran'] === 'Unpaid'));
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Pembayaran - Admin | Majelis MDPL</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../css/admin.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    table { font-size: 14px; }
  </style>
</head>
<body>
<div class="d-flex">
  <!-- Sidebar -->
  <div class="sidebar p-3 d-flex flex-column" id="sidebar">
    <div class="d-flex align-items-center justify-content-between mb-4">
      <h2 class="mb-0">Menu</h2>
      <button class="btn-toggle" id="toggleBtn"><i class="bi bi-list"></i></button>
    </div>
    <ul class="nav flex-column">
      <li class="nav-item"><a href="index.php" class="nav-link"><i class="bi bi-speedometer2"></i><span> Dashboard</span></a></li>
      <li class="nav-item"><a href="trip.php" class="nav-link"><i class="bi bi-map-fill"></i><span> Trip</span></a></li>
      <li class="nav-item"><a href="peserta.php" class="nav-link active"><i class="bi bi-people-fill"></i><span> Peserta</span></a></li>
      <li class="nav-item"><a href="pembayaran.php" class="nav-link"><i class="bi bi-wallet2"></i><span> Pembayaran</span></a></li>
      <li class="nav-item"><a href="galeri.php" class="nav-link"><i class="bi bi-images"></i><span> Galeri</span></a></li>
      <li class="nav-item"><a href="logout.php" class="nav-link"><i class="bi bi-box-arrow-left"></i><span> Logout</span></a></li>
    </ul>
  </div>

  <!-- Content -->
  <div class="content flex-grow-1 p-4">
    <h1 class="mb-4">Laporan Pembayaran</h1>

    <!-- Grafik -->
    <div class="mb-5">
      <canvas id="paymentChart" height="70"></canvas>
    </div>

    <!-- Tabel -->
    <div class="table-responsive">
      <table class="table table-bordered table-striped align-middle">
        <thead class="table-dark text-center">
          <tr>
            <th>ID</th>
            <th>Nama</th>
            <th>Jumlah Bayar</th>
            <th>Tanggal</th>
            <th>Jenis Pembayaran</th>
            <th>Metode</th>
            <th>Sisa Bayar</th>
            <th>Status</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($payments)): ?>
            <tr>
              <td colspan="9" class="text-center text-muted">Belum ada pembayaran</td>
            </tr>
          <?php else: ?>
            <?php foreach ($payments as $p): ?>
              <tr>
                <td><?= $p['id_payment'] ?></td>
                <td><?= $p['id_booking'] ?></td>
                <td>Rp <?= number_format($p['jumlah_bayar'], 0, ',', '.') ?></td>
                <td><?= $p['tanggal'] ?></td>
                <td><?= $p['jenis_pembayaran'] ?></td>
                <td><?= $p['metode'] ?></td>
                <td><?= $p['sisa_bayar'] ?></td>
                <td>
                  <?php if ($p['status_pembayaran'] === "Paid"): ?>
                    <span class="badge bg-success">Paid</span>
                  <?php else: ?>
                    <span class="badge bg-danger">Unpaid</span>
                  <?php endif; ?>
                </td>
                <td class="text-center">
                  <button class="btn btn-warning btn-sm editBtn" 
                          data-id="<?= $p['id_payment'] ?>" 
                          data-status="<?= $p['status_pembayaran'] ?>">
                    <i class="bi bi-pencil-square"></i> Edit
                  </button>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Modal Edit -->
<div class="modal fade" id="editModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="editForm">
        <div class="modal-header">
          <h5 class="modal-title">Edit Status Pembayaran</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id_payment" id="id_payment">
          <div class="mb-3">
            <label>Status</label>
            <select name="status_pembayaran" id="status_pembayaran" class="form-select">
              <option value="Paid">Paid</option>
              <option value="Unpaid">Unpaid</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Chart
const ctx = document.getElementById('paymentChart');
new Chart(ctx, {
  type: 'bar',
  data: {
    labels: ['Paid', 'Unpaid'],
    datasets: [{
      label: 'Jumlah Pembayaran',
      data: [<?= $totalLunas ?>, <?= $totalUnpaid ?>],
      backgroundColor: ['#28a745', '#dc3545']
    }]
  }
});

// Modal Edit
const editBtns = document.querySelectorAll(".editBtn");
const editModal = new bootstrap.Modal(document.getElementById("editModal"));
editBtns.forEach(btn => {
  btn.addEventListener("click", () => {
    document.getElementById("id_payment").value = btn.dataset.id;
    document.getElementById("status_pembayaran").value = btn.dataset.status;
    editModal.show();
  });
});

// Simpan ke JSON
document.getElementById("editForm").addEventListener("submit", async function(e) {
  e.preventDefault();
  const formData = new FormData(this);
  const response = await fetch("save_payment.php", {
    method: "POST",
    body: formData
  });
  if (response.ok) {
    location.reload();
  } else {
    alert("Gagal menyimpan data");
  }
});
</script>
</body>
</html>
