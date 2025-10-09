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
            
            // Update global variables dari auth_check.php
            $username = $user['username'];
            $user_role = $user['role'];
            $is_super_admin = RoleHelper::isSuperAdmin($user_role);
            $is_admin = RoleHelper::isAdmin($user_role);
        }
        $stmt->close();
    }
}

// Ambil username dari session dengan fallback yang lebih baik
$display_username = $username ?? 'Guest';
$display_role = $user_role ?? 'user';

// Jika masih "root" atau kosong, redirect ke login
if ($display_username === 'root' || $display_username === 'Guest' || empty($display_username)) {
    session_destroy();
    header('Location: ../login.php?error=session_invalid');
    exit;
}

// Handle error messages dari parameter URL
$error_message = '';
$error_type = '';
if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'access_denied':
            $error_message = $_GET['message'] ?? 'Akses ditolak. Anda tidak memiliki permission yang diperlukan untuk mengakses halaman tersebut.';
            $error_type = 'warning';
            break;
        case 'unauthorized':
            $error_message = 'Anda tidak memiliki akses ke halaman tersebut. Silakan hubungi administrator.';
            $error_type = 'error';
            break;
        case 'session_expired':
            $error_message = 'Session Anda telah berakhir. Silakan login kembali.';
            $error_type = 'info';
            break;
        case 'session_invalid':
            $error_message = 'Session tidak valid. Silakan login kembali.';
            $error_type = 'error';
            break;
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Admin Dashboard | Majelis MDPL</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet" />
  <!-- SweetAlert2 untuk error handling -->
  <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.0/dist/sweetalert2.min.css" rel="stylesheet">

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

    .main {
      margin-left: 280px; /* Disesuaikan dengan sidebar baru */
      min-height: 100vh;
      padding: 20px 25px;
      background: #f6f0e8;
      transition: margin-left 0.3s ease;
    }

    /* Responsive untuk sidebar collapsed */
    body.sidebar-collapsed .main {
      margin-left: 70px;
    }

    @media (max-width: 768px) {
      .main {
        margin-left: 0 !important;
        padding-top: 20px;
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
      flex-wrap: wrap;
      gap: 15px;
    }

    .main-header h2 {
      font-size: 1.4rem;
      font-weight: 700;
      color: #a97c50;
      margin-bottom: 0;
      letter-spacing: 1px;
    }

    .admin-info {
      background: linear-gradient(135deg, #a97c50 0%, #8b6332 100%);
      color: #ffe8c8;
      border-radius: 15px;
      padding: 12px 20px;
      font-weight: 600;
      font-size: 14px;
      box-shadow: 0 4px 15px rgba(169, 124, 80, 0.2);
      display: flex;
      align-items: center;
      gap: 12px;
      position: relative;
      transition: all 0.3s ease;
      min-width: 200px;
    }

    .admin-info:hover {
      background: linear-gradient(135deg, #8b6332 0%, #a97c50 100%);
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(169, 124, 80, 0.3);
    }

    .admin-info .user-icon {
      font-size: 24px;
      color: #fff;
      background: rgba(255, 255, 255, 0.2);
      padding: 8px;
      border-radius: 50%;
      width: 40px;
      height: 40px;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .admin-info .user-details {
      display: flex;
      flex-direction: column;
      align-items: flex-start;
      flex: 1;
    }

    .admin-info .username {
      font-weight: 700;
      font-size: 15px;
      letter-spacing: 0.5px;
      color: #fff;
      margin-bottom: 2px;
    }

    .admin-info .role-badge {
      font-size: 11px;
      font-weight: 600;
      background: rgba(255, 255, 255, 0.2);
      color: #fff;
      padding: 2px 8px;
      border-radius: 12px;
      text-transform: capitalize;
      letter-spacing: 0.3px;
      border: 1px solid rgba(255, 255, 255, 0.3);
    }

    /* Role-specific badge colors */
    .role-admin {
      background: rgba(255, 193, 7, 0.2) !important;
      border-color: rgba(255, 193, 7, 0.4) !important;
    }

    .role-super_admin {
      background: rgba(220, 53, 69, 0.2) !important;
      border-color: rgba(220, 53, 69, 0.4) !important;
    }

    /* Responsive admin info */
    @media (max-width: 800px) {
      .admin-info {
        padding: 8px 15px;
        font-size: 12px;
        min-width: 150px;
      }
      
      .admin-info .user-icon {
        width: 32px;
        height: 32px;
        font-size: 18px;
      }
      
      .admin-info .username {
        font-size: 13px;
      }
      
      .main-header {
        flex-direction: column;
        align-items: flex-start;
      }
      
      .main-header h2 {
        font-size: 1.2rem;
      }
    }

    @media (max-width: 600px) {
      .admin-info {
        padding: 6px 12px;
        min-width: 120px;
      }
      
      .admin-info .user-details {
        display: none;
      }
      
      .admin-info::after {
        content: attr(data-username);
        font-size: 12px;
        font-weight: 600;
        color: #fff;
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
      padding: 20px;
      box-shadow: 0 4px 15px rgba(120, 77, 37, 0.08);
      min-width: 130px;
      flex: 1 1 130px;
      display: flex;
      align-items: center;
      gap: 16px;
      transition: all 0.3s ease;
    }

    .card-stat:hover {
      transform: translateY(-3px);
      box-shadow: 0 8px 25px rgba(120, 77, 37, 0.15);
    }

    .card-stat i {
      font-size: 2rem;
      color: #a97c50;
      background: rgba(169, 124, 80, 0.1);
      padding: 12px;
      border-radius: 12px;
      width: 50px;
      height: 50px;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .stat-info {
      flex: 1;
    }

    .stat-label {
      font-size: 13px;
      font-weight: 600;
      color: #a97c50;
      opacity: 0.9;
      margin-bottom: 4px;
    }

    .stat-value {
      font-size: 24px;
      font-weight: 700;
      color: #432f17;
      line-height: 1;
    }

    .chart-section {
      max-width: 900px;
      margin: 0 auto 30px auto;
      background: #fff;
      padding: 25px;
      border-radius: 1rem;
      box-shadow: 0 4px 15px rgba(120, 77, 37, 0.08);
    }

    .chart-section h3 {
      font-size: 1.2em;
      font-weight: 700;
      color: #a97c50;
      margin-bottom: 20px;
      text-align: center;
      letter-spacing: 0.5px;
    }

    .data-table-section {
      background: #fff;
      border-radius: 1rem;
      box-shadow: 0 4px 15px rgba(120, 77, 37, 0.08);
      padding: 25px;
      margin-bottom: 18px;
    }

    .data-table-section h3 {
      font-size: 1.2em;
      font-weight: 700;
      color: #a97c50;
      margin: 0;
      letter-spacing: 0.5px;
    }

    table {
      width: 100%;
      border-radius: 1rem;
      overflow: hidden;
      border-collapse: collapse;
      margin-top: 15px;
      font-size: 13px;
    }

    th,
    td {
      padding: 12px 15px;
      text-align: left;
      font-weight: 500;
      color: #432f17;
      border-bottom: 1px solid #f2dbc1;
      vertical-align: middle;
    }

    th {
      background: linear-gradient(135deg, #a97c50 0%, #8b6332 100%);
      color: #fff;
      font-weight: 700;
      letter-spacing: 0.7px;
      font-size: 14px;
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
      font-size: 12px;
      padding: 6px 12px;
      border-radius: 20px;
      display: inline-block;
      min-width: 70px;
      text-align: center;
      letter-spacing: 0.3px;
    }

    .badge-delete {
      background-color: #dc3545;
      color: white;
    }

    .badge-pending {
      background-color: #ffc107;
      color: #432f17;
    }

    .badge-success {
      background-color: #28a745;
      color: white;
    }

    .badge-info {
      background-color: #17a2b8;
      color: white;
    }

    .badge-update {
      background-color: #007bff;
      color: white;
    }

    /* Welcome message enhancement */
    .welcome-section {
      background: linear-gradient(135deg, #a97c50 0%, #8b6332 100%);
      color: white;
      padding: 25px;
      border-radius: 15px;
      margin-bottom: 30px;
      box-shadow: 0 6px 20px rgba(169, 124, 80, 0.2);
      position: relative;
      overflow: hidden;
    }

    .welcome-section::before {
      content: '';
      position: absolute;
      top: 0;
      right: 0;
      width: 100px;
      height: 100px;
      background: rgba(255, 255, 255, 0.05);
      border-radius: 50%;
      transform: translate(30px, -30px);
    }

    .welcome-section h3 {
      margin: 0;
      font-size: 1.4rem;
      font-weight: 600;
      letter-spacing: 0.5px;
      position: relative;
      z-index: 1;
    }

    .welcome-section p {
      margin: 8px 0 0 0;
      opacity: 0.9;
      font-size: 15px;
      position: relative;
      z-index: 1;
    }

    /* SweetAlert2 custom styling */
    .swal2-popup {
      border-radius: 15px !important;
    }

    .swal2-title {
      color: #a97c50 !important;
      font-family: "Poppins", Arial, sans-serif !important;
    }

    .swal2-confirm {
      background-color: #a97c50 !important;
      border-radius: 8px !important;
    }

    .swal2-confirm:hover {
      background-color: #8b6332 !important;
    }
  </style>
</head>

<body>

  <!-- Include Sidebar -->
  <?php include 'sidebar.php'; ?>

  <main class="main">
    <div class="main-header">
      <h2>Dashboard Admin</h2>
      <div class="admin-info" 
           title="Pengguna yang sedang login: <?= htmlspecialchars($display_username) ?>"
           data-username="<?= htmlspecialchars($display_username) ?>">
        <i class="bi bi-person-circle user-icon"></i>
        <div class="user-details">
          <span class="username"><?= htmlspecialchars($display_username) ?></span>
          <span class="role-badge role-<?= $display_role ?>">
            <?= RoleHelper::getRoleDisplayName($display_role) ?>
          </span>
        </div>
      </div>
    </div>

    <!-- Welcome Section -->
    <section class="welcome-section">
      <h3 id="welcomeMessage">
        <?php 
        // Pastikan tidak menampilkan "root"
        $displayName = ($display_username && $display_username !== 'root' && $display_username !== 'Guest') ? $display_username : 'Pengguna';
        echo "Selamat Datang, " . htmlspecialchars($displayName) . "!"; 
        ?>
      </h3>
      <p>Selamat beraktivitas di sistem Majelis MDPL - <?= RoleHelper::getRoleDisplayName($display_role) ?></p>
    </section>

    <!-- SECTION HEADERS INFORMATION -->
    <section class="cards">
      <div class="card-stat">
        <i class="bi bi-signpost-split"></i>
        <div class="stat-info">
          <div class="stat-label">Trip Aktif</div>
          <div class="stat-value" data-stat="trip-aktif">0</div>
        </div>
      </div>
      <div class="card-stat">
        <i class="bi bi-people"></i>
        <div class="stat-info">
          <div class="stat-label">Peserta</div>
          <div class="stat-value" data-stat="total-peserta">0</div>
        </div>
      </div>
      <div class="card-stat">
        <i class="bi bi-credit-card"></i>
        <div class="stat-info">
          <div class="stat-label">Pembayaran Pending</div>
          <div class="stat-value" data-stat="pembayaran-pending">0</div>
        </div>
      </div>
      <div class="card-stat">
        <i class="bi bi-check2-circle"></i>
        <div class="stat-info">
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
        <h3>Riwayat Aktivitas Terbaru</h3>
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
  
  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="../frontend/dashboard.js"></script>
  
  <!-- Error handling dan dynamic greeting script -->
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      // Error handling dengan SweetAlert2
      <?php if (!empty($error_message)): ?>
      Swal.fire({
        title: '<?= $error_type === "warning" ? "Peringatan!" : ($error_type === "error" ? "Error!" : "Informasi") ?>',
        text: '<?= addslashes($error_message) ?>',
        icon: '<?= $error_type === "warning" ? "warning" : ($error_type === "error" ? "error" : "info") ?>',
        confirmButtonText: 'Mengerti',
        confirmButtonColor: '#a97c50',
        timer: <?= $error_type === "info" ? 5000 : 0 ?>,
        timerProgressBar: <?= $error_type === "info" ? "true" : "false" ?>,
        showCloseButton: true
      });
      <?php endif; ?>

      // Search functionality
      const searchInput = document.getElementById('activitySearchInput');
      const tableBody = document.querySelector('.data-table-section tbody');

      if (searchInput && tableBody) {
        searchInput.addEventListener('input', () => {
          const filter = searchInput.value.toLowerCase();
          const rows = tableBody.querySelectorAll('tr');
          rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(filter) ? '' : 'none';
          });
        });
      }

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
      const currentUsername = "<?= addslashes($displayName) ?>";
      
      if (welcomeTitle && currentUsername && currentUsername !== 'root' && currentUsername !== 'Pengguna') {
        welcomeTitle.innerHTML = `${greeting}, ${currentUsername}!`;
      } else if (welcomeTitle) {
        welcomeTitle.innerHTML = `${greeting}, <?= RoleHelper::getRoleDisplayName($display_role) ?>!`;
      }

      // Animation untuk cards
      const cards = document.querySelectorAll('.card-stat');
      cards.forEach((card, index) => {
        setTimeout(() => {
          card.style.opacity = '0';
          card.style.transform = 'translateY(20px)';
          card.style.transition = 'all 0.5s ease';
          
          setTimeout(() => {
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
          }, 100);
        }, index * 100);
      });
    });

    // Function untuk menampilkan toast notification
    function showToast(type, message) {
      Swal.fire({
        toast: true,
        position: 'top-end',
        icon: type,
        title: message,
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
          toast.addEventListener('mouseenter', Swal.stopTimer)
          toast.addEventListener('mouseleave', Swal.resumeTimer)
        }
      });
    }
  </script>

</body>

</html>
