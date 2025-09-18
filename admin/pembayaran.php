<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Pembayaran | Majelis MDPL</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet" />
<style>
  /* Sidebar dan styling konsisten dengan peserta.php */
  body {
    background: #f6f0e8;
    color: #232323;
    font-family: "Poppins", Arial, sans-serif;
    min-height: 100vh;
    letter-spacing: 0.3px;
    margin: 0;
  }
  .sidebar {
    background: #a97c50;
    min-height: 100vh;
    width: 240px;
    position: fixed;
    left: 0; top: 0; bottom: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    padding-top: 34px;
    box-shadow: 2px 0 18px rgba(79,56,34,0.06);
    z-index: 100;
    transition: width 0.25s;
  }
  .sidebar img {
    width: 43px; height: 43px;
    border-radius: 11px;
    background: #fff7eb;
    border: 2px solid #d9b680;
    margin-bottom: 13px;
  }
  .logo-text {
    font-size: 1.13em;
    font-weight: 700;
    color: #fffbe4;
    margin-bottom: 30px;
    letter-spacing: 1.5px;
  }
  .sidebar-nav {
    flex: 1 1 auto;
    width: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
  }
  .nav-link {
    width: 90%;
    color: #fff;
    font-weight: 500;
    border-radius: 0.7rem;
    margin-bottom: 5px;
    padding: 13px 22px;
    display: flex;
    align-items: center;
    font-size: 16px;
    gap: 11px;
    letter-spacing: 0.7px;
    text-decoration: none;
    transition: background 0.22s, color 0.22s;
  }
  .nav-link.active,
  .nav-link:hover {
    background: #432f17;
    color: #ffd49c;
  }
  .logout {
    color: #fff;
    background: #c19c72;
    font-weight: 600;
    margin-bottom: 15px;
  }
  .logout:hover {
    background: #432f17;
    color: #fffbe4;
  }
  @media (max-width: 800px) {
    .sidebar {
      width: 100vw;
      height: 70px;
      flex-direction: row;
      padding-top: 0;
      padding-bottom: 0;
      bottom: unset;
      top: 0;
      justify-content: center;
      align-items: center;
      position: fixed;
      z-index: 100;
    }
    .sidebar img,
    .logo-text {
      display: none;
    }
    .sidebar-nav {
      flex-direction: row;
      align-items: center;
      justify-content: center;
      width: 100vw;
      height: 70px;
      margin: 0; padding: 0;
    }
    .nav-link,
    .logout {
      width: auto;
      min-width: 70px;
      font-size: 15px;
      margin: 0 3px;
      border-radius: 14px;
      padding: 8px 10px;
      justify-content: center;
    }
    .logout {
      order: 99;
      margin-left: 8px;
      margin-bottom: 0;
    }
  }
    .main {
      margin-left: 240px;
      min-height: 100vh;
      padding: 0 10px 18px 10px;
      background: #f6f0e8;
      transition: margin-left 0.25s;
    }
  @media (max-width: 800px) {
    .main {
      margin-left: 0;
      padding-top: 90px;
      padding-left: 15px;
      padding-right: 15px;
    }
  }
    .daftar-heading {
      font-size: 1.4rem;
      font-weight: bold;
      color: #a97c50;
      margin: 32px 0 18px 0;
      letter-spacing: 1px;
    }
  .payment-summary {
    max-width: 900px;
    margin: 0 auto 30px auto;
    display: flex;
    gap: 30px;
    justify-content: center;
  }
  .summary-item {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    padding: 20px 45px;
    text-align: center;
    color: #3a3a3a;
    font-weight: 700;
    font-size: 1.2em;
  }
  .summary-item i {
    font-size: 2em;
    margin-bottom: 12px;
    color: #a97c50;
  }
  /* Table style konsisten dengan peserta.php */
  table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 1px 7px rgba(120,77,37,0.1);
  }
  thead {
    background-color: #a97c50;
  }
  thead th {
    color: white;
    padding: 12px 10px;
    font-weight: 700;
    letter-spacing: 0.7px;
    text-align: left;
  }
  tbody tr {
    border-bottom: 1px solid #f2dbc1;
  }
  tbody tr:last-child {
    border-bottom: none;
  }
  tbody tr:hover {
    background-color: #f9e8d0;
    color: #a97c50;
  }
  tbody td {
    padding: 11px 10px;
    vertical-align: middle;
    font-weight: 500;
    color: #432f17;
  }
  .btn-action-group {
    display: flex;
    gap: 10px;
  }
  .btn-edit, .btn-delete {
    padding: 5px 12px;
    font-size: 0.85em;
    font-weight: 600;
    border-radius: 6px;
    cursor: pointer;
    border: none;
    color: white;
    transition: background-color 0.3s ease;
  }
  .btn-edit {
    background-color: #007bff;
  }
  .btn-edit:hover {
    background-color: #0056b3;
  }
  .btn-delete {
    background-color: #dc3545;
  }
  .btn-delete:hover {
    background-color: #a71d2a;
  }
</style>
</head>
<body>

<aside class="sidebar">
  <img src="../img/majelis.png" alt="Majelis MDPL" />
  <div class="logo-text">Majelis MDPL</div>
  <nav class="sidebar-nav">
    <a href="index.php" class="nav-link"><i class="bi bi-bar-chart"></i>Dashboard</a>
    <a href="trip.php" class="nav-link"><i class="bi bi-signpost-split"></i>Trip</a>
    <a href="peserta.php" class="nav-link"><i class="bi bi-people"></i>Peserta</a>
    <a href="pembayaran.php" class="nav-link active"><i class="bi bi-credit-card"></i>Pembayaran</a>
    <a href="galeri.php" class="nav-link"><i class="bi bi-images"></i>Galeri</a>
    <a href="logout.php" class="nav-link logout"><i class="bi bi-box-arrow-right"></i>Logout</a>
  </nav>
</aside>

<main class="main">
  <div class="daftar-heading">Daftar Pembayaran</div>

  <div class="payment-summary">
    <div class="summary-item">
      <i class="bi bi-wallet2"></i>
      Total Bayar<br />Rp 4.200.000
    </div>
    <div class="summary-item">
      <i class="bi bi-check2-circle"></i>
      Lunas<br />15 Pembayaran
    </div>
    <div class="summary-item">
      <i class="bi bi-clock-history"></i>
      Dalam Proses<br />3 Pembayaran
    </div>
  </div>

  <table>
    <thead>
      <tr>
        <th>ID Payment</th>
        <th>ID Booking</th>
        <th>Jumlah Bayar</th>
        <th>Tanggal</th>
        <th>Jenis Pembayaran</th>
        <th>Metode</th>
        <th>Sisa Bayar</th>
        <th>Status Pembayaran</th>
        <th>Aksi</th>
      </tr>
    </thead>
    <tbody id="paymentList">
      <!-- Data pembayaran akan di-load dengan JS -->
    </tbody>
  </table>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
  const samplePayments = [
    {
      idpayment: 1,
      idbooking: 101,
      jumlahbayar: 1500000,
      tanggal: "2025-09-15",
      jenispembayaran: "Lunas",
      metode: "Transfer Bank",
      sisabayar: 0,
      statuspembayaran: "Selesai"
    },
    {
      idpayment: 2,
      idbooking: 102,
      jumlahbayar: 750000,
      tanggal: "2025-09-14",
      jenispembayaran: "DP",
      metode: "Cash",
      sisabayar: 750000,
      statuspembayaran: "Menunggu"
    },
    {
      idpayment: 3,
      idbooking: 103,
      jumlahbayar: 1200000,
      tanggal: "2025-09-13",
      jenispembayaran: "Lunas",
      metode: "Transfer Bank",
      sisabayar: 0,
      statuspembayaran: "Selesai"
    }
  ];

  function loadPayments() {
    const tbody = document.getElementById('paymentList');
    tbody.innerHTML = '';
    samplePayments.forEach(p => {
      tbody.innerHTML += `
        <tr>
          <td>${p.idpayment}</td>
          <td>${p.idbooking}</td>
          <td>Rp ${p.jumlahbayar.toLocaleString('id-ID')}</td>
          <td>${p.tanggal}</td>
          <td>${p.jenispembayaran}</td>
          <td>${p.metode}</td>
          <td>Rp ${p.sisabayar.toLocaleString('id-ID')}</td>
          <td>${p.statuspembayaran}</td>
          <td>
            <button class="btn btn-primary btn-sm">Detail</button>
          </td>
        </tr>
      `;
    });
  }

  window.onload = loadPayments;
</script>

</body>
</html>
