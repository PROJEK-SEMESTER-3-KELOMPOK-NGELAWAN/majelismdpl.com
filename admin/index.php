<?php
require_once 'auth_check.php';

// Debug dan perbaiki session username
if (!isset($_SESSION['username']) || empty($_SESSION['username']) || $_SESSION['username'] === 'root') {
    // Jika session bermasalah, ambil dari database
    if (isset($_SESSION['id_user']) && !empty($_SESSION['id_user'])) {
        require_once '../backend/koneksi.php';
        $stmt = $conn->prepare("SELECT username, role FROM users WHERE id_user = ?");
        $stmt->bind_param("i", $_SESSION['id_user']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
        }
        $stmt->close();
    }
}

// Ambil username dari session dengan fallback yang lebih baik
$username = $_SESSION['username'] ?? 'Guest';
$role = $_SESSION['role'] ?? 'user';

// Jika masih "root" atau kosong, redirect ke login
if ($username === 'root' || $username === 'Guest' || empty($username)) {
    session_destroy();
    header('Location: ../login.php?error=session_invalid');
    exit;
}
?>

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

    .search-container {
      position: relative;
      margin: 0;
      width: 100%;
    }

    .search-input {
      width: 100%;
      padding-left: 15px;
      padding-right: 45px;
      border-radius: 50px;
      border: 1.5px solid #a97c50;
      height: 38px;
      font-size: 14px;
      color: #432f17;
      transition: border-color 0.3s ease;
    }

    .search-input:focus {
      outline: none;
      border-color: #432f17;
      box-shadow: 0 0 8px rgba(67, 47, 23, 0.3);
    }

    .search-icon {
      position: absolute;
      right: 15px;
      top: 50%;
      transform: translateY(-50%);
      color: #a97c50;
      pointer-events: none;
      font-size: 18px;
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
      padding: 8px 20px;
      font-weight: 600;
      font-size: 14px;
      box-shadow: 0 2px 8px rgba(169, 124, 80, 0.1);
      display: flex;
      align-items: center;
      gap: 8px;
      position: relative;
      transition: all 0.3s ease;
    }

    .admin-info:hover {
      background: #a97c50;
      transform: translateY(-1px);
      box-shadow: 0 4px 12px rgba(169, 124, 80, 0.2);
    }

    .admin-info .user-icon {
      font-size: 16px;
      color: #ffd49c;
    }

    .admin-info .user-details {
      display: flex;
      flex-direction: column;
      align-items: flex-start;
    }

    .admin-info .username {
      font-weight: 700;
      font-size: 14px;
      letter-spacing: 0.5px;
      color: #fff;
    }

    .admin-info .role-badge {
      font-size: 11px;
      font-weight: 500;
      color: #ffd49c;
      opacity: 0.9;
      text-transform: capitalize;
      letter-spacing: 0.3px;
    }

    /* Responsive admin info */
    @media (max-width: 800px) {
      .admin-info {
        padding: 6px 12px;
        font-size: 12px;
      }
      
      .admin-info .user-details {
        display: none;
      }
      
      .admin-info .username {
        font-size: 12px;
      }
      
      .main-header {
        flex-direction: column;
        gap: 15px;
        align-items: flex-start;
      }
      
      .main-header h2 {
        font-size: 1.2rem;
      }
    }

    @media (max-width: 600px) {
      .admin-info {
        padding: 5px 10px;
      }
      
      .admin-info .username {
        display: none;
      }
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
      font-size: 13px;
      padding: 5px 12px;
      border-radius: 14px;
      display: inline-block;
      min-width: 70px;
      text-align: center;
    }

    .badge-delete {
      background-color: #c94f44;
      color: white;
    }

    .badge-pending {
      background-color: #ffd49c;
      color: #432f17;
    }

    .badge-success {
      background-color: #13a362;
      color: white;
    }

    .badge-info {
      background-color: #13a362;
      color: white;
    }

    .badge-update {
      background-color: #67caff;
      color: white;
    }

    /* Welcome message enhancement */
    .welcome-section {
      background: linear-gradient(135deg, #a97c50 0%, #8b6332 100%);
      color: white;
      padding: 20px 25px;
      border-radius: 12px;
      margin-bottom: 25px;
      box-shadow: 0 4px 15px rgba(169, 124, 80, 0.2);
    }

    .welcome-section h3 {
      margin: 0;
      font-size: 1.3rem;
      font-weight: 600;
      letter-spacing: 0.5px;
    }

    .welcome-section p {
      margin: 5px 0 0 0;
      opacity: 0.9;
      font-size: 14px;
    }
  </style>
</head>

<body>

  <!-- Include Sidebar -->
  <?php include 'sidebar.php'; ?>

  <main class="main">
    <div class="main-header">
      <h2>Dashboard Admin</h2>
      <div class="admin-info" title="Pengguna yang sedang login">
        <i class="bi bi-person-circle user-icon"></i>
        <div class="user-details">
          <span class="username"><?php echo htmlspecialchars($username); ?></span>
          <span class="role-badge"><?php echo htmlspecialchars(ucfirst($role)); ?></span>
        </div>
        <span class="username d-lg-none"><?php echo htmlspecialchars($username); ?></span>
      </div>
    </div>

    <!-- Welcome Section - Fixed untuk tidak menampilkan "root" -->
    <section class="welcome-section">
      <h3 id="welcomeMessage">
        <?php 
        // Pastikan tidak menampilkan "root"
        $displayName = ($username && $username !== 'root' && $username !== 'Guest') ? $username : 'Pengguna';
        echo "Selamat Datang, " . htmlspecialchars($displayName) . "!"; 
        ?>
      </h3>
      <p>Selamat beraktivitas di sistem Majelis MDPL</p>
    </section>

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
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 style="margin: 0; color: #a97c50; font-weight: 700; font-size: 1.2rem; letter-spacing:1px;">Riwayat Aktivitas Terbaru</h3>
        <div class="search-container" style="max-width: 280px; width: 100%;">
          <input type="text" id="activitySearchInput" class="search-input" placeholder="Cari aktivitas..." />
          <i class="bi bi-search search-icon"></i>
        </div>
      </div>

      <table>
        <thead>
          <tr>
            <th>No</th>
            <th>Aktivitas</th>
            <th>Pelaku</th>
            <th>Waktu</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php
          include '../backend/activity-logs.php';
          echo $rows;
          ?>
        </tbody>
      </table>
    </section>

  </main>
  
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="../frontend/dashboard.js"></script>
  <script>
    // JavaScript untuk dynamic greeting
    document.addEventListener('DOMContentLoaded', () => {
      const searchInput = document.getElementById('activitySearchInput');
      const tableBody = document.querySelector('.data-table-section tbody');

      searchInput.addEventListener('input', () => {
        const filter = searchInput.value.toLowerCase();
        const rows = tableBody.querySelectorAll('tr');
        rows.forEach(row => {
          const text = row.textContent.toLowerCase();
          row.style.display = text.includes(filter) ? '' : 'none';
        });
      });

      // Dynamic greeting berdasarkan waktu
      const currentHour = new Date().getHours();
      let greeting = '';
      
      if (currentHour < 10) {
        greeting = 'Selamat Pagi';
      } else if (currentHour < 15) {
        greeting = 'Selamat Siang';
      } else if (currentHour < 18) {
        greeting = 'Selamat Sore';
      } else {
        greeting = 'Selamat Malam';
      }

      // Update welcome message dengan greeting yang sesuai waktu
      const welcomeTitle = document.getElementById('welcomeMessage');
      const currentUsername = "<?php echo addslashes($displayName); ?>";
      
      if (welcomeTitle && currentUsername && currentUsername !== 'root' && currentUsername !== 'Pengguna') {
        welcomeTitle.innerHTML = `${greeting}, ${currentUsername}!`;
      } else if (welcomeTitle) {
        welcomeTitle.innerHTML = `${greeting}!`;
      }
    });
  </script>

</body>

</html>
