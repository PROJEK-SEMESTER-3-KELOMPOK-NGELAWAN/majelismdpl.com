<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Majelis MDPL | Admin Dashboard</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet" />

  <style>
    body {
      background: #f6f0e8;
      color: #432f17;
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
      left: 0;
      top: 0;
      bottom: 0;
      display: flex;
      flex-direction: column;
      align-items: center;
      padding-top: 34px;
      box-shadow: 2px 0 18px rgba(79, 56, 34, 0.06);
      z-index: 100;
      transition: width 0.25s;
    }

    .sidebar img {
      width: 43px;
      height: 43px;
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
        margin: 0;
        padding: 0;
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
      padding: 20px 25px;
      background: #f6f0e8;
    }

    @media (max-width: 800px) {
      .main {
        margin-left: 0;
        padding-top: 90px;
        padding-left: 15px;
        padding-right: 15px;
      }
    }

    .main-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding-top: 32px;
      padding-bottom: 28px;
    }

    .main-header h2 {
      font-size: 1.4rem;
      font-weight: 700;
      color: #a97c50;
      margin-bottom: 0;
      letter-spacing: 1px;
    }

    .admin-info {
      background: #432f17;
      color: #ffe8c8;
      border-radius: 19px;
      padding: 6px 18px;
      font-weight: 600;
      font-size: 14px;
      box-shadow: 0 2px 8px rgba(169, 124, 80, 0.1);
    }

    .cards {
      display: flex;
      gap: 19px;
      margin-bottom: 32px;
      flex-wrap: wrap;
    }

    .card-stat {
      background: #fff;
      border-radius: 1rem;
      padding: 16px 18px;
      box-shadow: 0 1px 7px rgba(120, 77, 37, 0.09);
      min-width: 130px;
      flex: 1 1 130px;
      display: flex;
      align-items: center;
      gap: 14px;
    }

    .card-stat i {
      font-size: 1.65rem;
      color: #a97c50;
    }

    .stat-label {
      font-size: 13.5px;
      font-weight: 600;
      color: #a97c50;
      opacity: 0.94;
    }

    .stat-value {
      font-size: 19px;
      font-weight: 700;
      color: #432f17;
    }

    .chart-section {
      max-width: 900px;
      margin: 0 auto 30px auto;
      background: #fff;
      padding: 22px 20px 16px 20px;
      border-radius: 1rem;
      box-shadow: 0 1px 7px rgba(120, 77, 37, 0.1);
    }

    .chart-section h3 {
      font-size: 1.1em;
      font-weight: 700;
      color: #a97c50;
      margin-bottom: 18px;
      text-align: center;
    }

    .data-table-section {
      background: #fff;
      border-radius: 1rem;
      box-shadow: 0 1px 7px rgba(120, 77, 37, 0.08);
      padding: 12px;
      margin-bottom: 18px;
    }

    .data-table-section h3 {
      font-size: 1.06em;
      font-weight: 700;
      color: #a97c50;
    }

    table {
      width: 100%;
      border-radius: 1rem;
      overflow: hidden;
      border-collapse: collapse;
      margin-top: 7px;
      font-size: 13px;
    }

    th,
    td {
      padding: 10px 10px;
      text-align: left;
      font-weight: 500;
      color: #432f17;
      border-bottom: 1px solid #f2dbc1;
      vertical-align: middle;
    }

    th {
      background: #a97c50;
      color: #fff;
      font-weight: 700;
      letter-spacing: 0.7px;
      font-size: 13.5px;
    }

    tr:last-child td {
      border-bottom: none;
    }

    tbody tr:hover td {
      background-color: #f9e8d0;
      color: #a97c50;
    }

    .badge {
      font-weight: 600;
      font-size: 11px;
      padding: 5px 11px;
      border-radius: 10px;
      letter-spacing: 0.6px;
    }

    .badge-success {
      background: #13a362;
      color: #fff;
    }

    .badge-warning {
      background: #ffd49c;
      color: #432f17;
    }

    .badge-danger {
      background: #c94f44;
      color: #fff;
    }

    .badge-info {
      background: #67caff;
      color: #fff;
    }

    .stat-value {
    transition: all 0.3s ease;
}

.stat-value.updating {
    color: #a97c50;
    transform: scale(1.1);
}

.card-stat {
    transition: transform 0.2s ease;
}

.card-stat:hover {
    transform: translateY(-2px);
}

  </style>
</head>

<body>
  <aside class="sidebar">
    <img src="../img/majelis.png" alt="Majelis MDPL" />
    <div class="logo-text">Majelis MDPL</div>
    <nav class="sidebar-nav">
      <a href="index.php" class="nav-link active"><i class="bi bi-bar-chart"></i>Dashboard</a>
      <a href="trip.php" class="nav-link"><i class="bi bi-signpost-split"></i>Trip</a>
      <a href="peserta.php" class="nav-link"><i class="bi bi-people"></i>Peserta</a>
      <a href="pembayaran.php" class="nav-link"><i class="bi bi-credit-card"></i>Pembayaran</a>
      <a href="galeri.php" class="nav-link"><i class="bi bi-images"></i>Galeri</a>
      <a href="logout.php" class="nav-link logout"><i class="bi bi-box-arrow-right"></i>Logout</a>
    </nav>
  </aside>
  <main class="main">
    <div class="main-header">
      <h2>Dashboard Admin</h2>
      <div class="admin-info"><i class="bi bi-person-circle"></i> Admin</div>
    </div>


    <!-- SECTION HEADERS INFORMATION -->
    <section class="cards">
      <div class="card-stat">
        <i class="bi bi-signpost-split"></i>
        <div>
          <div class="stat-label">Trip Aktif</div>
          <div class="stat-value" data-stat="trip-aktif">0</div>
        </div>
      </div>
      <div class="card-stat">
        <i class="bi bi-people"></i>
        <div>
          <div class="stat-label">Peserta</div>
          <div class="stat-value" data-stat="total-peserta">0</div>
        </div>
      </div>
      <div class="card-stat">
        <i class="bi bi-credit-card"></i>
        <div>
          <div class="stat-label">Pembayaran Pending</div>
          <div class="stat-value" data-stat="pembayaran-pending">0</div>
        </div>
      </div>
      <div class="card-stat">
        <i class="bi bi-check2-circle"></i>
        <div>
          <div class="stat-label">Trip Selesai</div>
          <div class="stat-value" data-stat="trip-selesai">0</div>
        </div>
      </div>
    </section>



    <!-- DATA TABLE GRAFIK SECTION -->
    <section class="chart-section mb-4">
      <h3>Statistik Peserta Bulanan</h3>
      <canvas id="pesertaChart" height="90"></canvas>
    </section>
    
    <section class="data-table-section">
      <h3>Riwayat Aktivitas Terbaru</h3>
      <table>
        <thead>
          <tr>
            <th>No</th>
            <th>Aktivitas</th>
            <th>Waktu</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>1</td>
            <td>Trip “Rinjani” ditambahkan</td>
            <td>11/09/2025 13:30</td>
            <td><span class="badge badge-success">Publish</span></td>
          </tr>
          <tr>
            <td>2</td>
            <td>Pembayaran peserta #067 diverifikasi</td>
            <td>09/09/2025 19:42</td>
            <td><span class="badge badge-warning">Proses</span></td>
          </tr>
          <tr>
            <td>3</td>
            <td>Peserta baru daftar – Bromo</td>
            <td>09/09/2025 08:10</td>
            <td><span class="badge badge-info">Baru</span></td>
          </tr>
          <tr>
            <td>4</td>
            <td>Pembatalan peserta #024 trip Lawu</td>
            <td>08/09/2025 20:14</td>
            <td><span class="badge badge-danger">Batal</span></td>
          </tr>
        </tbody>
      </table>
    </section>


  </main>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="../frontend/dashboard.js"></script>
</body>

</html>