<?php
require_once 'auth_check.php';

// Debug dan perbaiki session username
if (!isset($_SESSION['username']) || empty($_SESSION['username']) || $_SESSION['username'] === 'root') {
  // Jika session bermasalah, ambil dari database
  if (isset($_SESSION['id_user']) && !empty($_SESSION['id_user'])) {
    require_once '../backend/koneksi.php';
    // Pastikan Anda memilih kolom 'foto_profil' dari tabel 'users'
    $stmt = $conn->prepare("SELECT username, email, role, foto_profil FROM users WHERE id_user = ?");
    $stmt->bind_param("i", $_SESSION['id_user']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
      $user = $result->fetch_assoc();
      $_SESSION['username'] = $user['username'];
      $_SESSION['role'] = $user['role'];
      $_SESSION['email'] = $user['email'] ?? ''; // Ambil email juga
      // SIMPAN JALUR FOTO KE SESSION
      $_SESSION['foto_profil'] = $user['foto_profil'] ?? '';

      // Update global variables dari auth_check.php
      $username = $user['username'];
      $user_role = $user['role'];
      // ... (Kode RoleHelper lainnya) ...
    }
    $stmt->close();
  }
}

// Ambil username, email, dan role dari session dengan fallback yang lebih baik
$display_username = $username ?? 'Guest';
$display_role = $user_role ?? 'user';
$display_email = $_SESSION['email'] ?? 'N/A';
// AMBIL JALUR FOTO DARI SESSION
$user_photo_path_db = $_SESSION['foto_profil'] ?? '';
$default_photo = '../img/profile/default.png'; // Ganti dengan jalur foto default Anda yang benar
$final_photo_path = (!empty($user_photo_path_db) && file_exists('../' . $user_photo_path_db))
  ? '../' . $user_photo_path_db
  : $default_photo;


// Handle error messages dari parameter URL
$error_message = '';
$error_type = '';
$success_message = ''; // TAMBAHKAN VARIABEL SUKSES

if (isset($_GET['error'])) {
  // ... (blok switch untuk error yang sudah ada) ...
}

// NEW: Handle success messages dari parameter URL
if (isset($_GET['success'])) {
  switch ($_GET['success']) {
    case 'photo_updated':
      $success_message = 'Foto profil berhasil diperbarui!';
      break;
    case 'photo_removed':
      $success_message = 'Foto profil berhasil dihapus!';
      break;
  }
}

// Tentukan nama yang akan ditampilkan di welcome section/greeting
$displayName = ($display_username && $display_username !== 'root' && $display_username !== 'Guest')
  ? $display_username
  : RoleHelper::getRoleDisplayName($display_role);
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
      margin-left: 280px;
      /* Disesuaikan dengan sidebar baru */
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
      border: none;
      /* Tambahkan agar konsisten seperti tombol */
      cursor: pointer;
      /* Tambahkan cursor pointer */
    }

    .admin-info:hover {
      background: linear-gradient(135deg, #8b6332 0%, #a97c50 100%);
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(169, 124, 80, 0.3);
    }

    .admin-info .user-icon {
      border-radius: 50%;
      width: 40px;
      height: 40px;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: none;
    }

    .admin-info i.user-icon {
      font-size: 30px;
      color: #fff;
      background: rgba(255, 255, 255, 0.2);
      padding: 8px;
      width: 40px;
      height: 40px;
    }

    .admin-info img.user-icon {
      object-fit: cover;
      border: 2px solid #fff;
      background: none;
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
      /* KONSISTENSI DENGAN MASTER ADMIN: -2px translateY dan shadow */
      transform: translateY(-2px);
      box-shadow: 0 8px 25px rgba(120, 77, 37, 0.12);
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

    .activity-log-table {
      /* KELAS KHUSUS UNTUK KONTROL FONT/PADDING */
      width: 100%;
      border-radius: 1rem;
      overflow: hidden;
      border-collapse: collapse;
      margin-top: 15px;
      font-size: 14px;
      /* Ditingkatkan dari 13px */
    }

    .activity-log-table th {
      /* KONSISTENSI DENGAN MASTER ADMIN */
      background: linear-gradient(135deg, #a97c50 0%, #8b6332 100%);
      color: #fff;
      font-weight: 700;
      letter-spacing: 0.7px;
      font-size: 14px;
      padding: 15px;
      /* Ditingkatkan dari 12px */
      border: none;
    }

    .activity-log-table td {
      padding: 15px;
      /* Ditingkatkan dari 12px untuk jarak antar baris */
      text-align: left;
      font-weight: 500;
      color: #432f17;
      border-bottom: 1px solid #f2dbc1;
      vertical-align: middle;
    }

    .activity-log-table tr:last-child td {
      border-bottom: none;
    }

    .activity-log-table tbody tr:hover td {
      background-color: #f9e8d0;
      color: #a97c50;
    }

    /* ... di dalam blok <style> yang sudah ada ... */

    /* Tata Letak Kontrol Tabel (Filter + Search) */
    .table-controls {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
      flex-wrap: wrap;
      /* Agar responsif */
      gap: 15px;
    }

    .table-controls .control-group {
      display: flex;
      gap: 10px;
    }

    /* Style untuk Filter Dropdown */
    .filter-select {
      border-radius: 50px;
      border: 1.5px solid #a97c50;
      height: 38px;
      font-size: 14px;
      padding: 0 15px;
      color: #432f17;
      background-color: #fff;
      transition: border-color 0.3s ease;
      appearance: none;
      /* Menghilangkan panah default */
      background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23a97c50' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
      background-repeat: no-repeat;
      background-position: right 10px center;
      background-size: 10px;
      cursor: pointer;
    }

    .filter-select:focus {
      outline: none;
      border-color: #432f17;
      box-shadow: 0 0 8px rgba(67, 47, 23, 0.3);
    }

    /* Responsif */
    @media (max-width: 768px) {
      .table-controls {
        flex-direction: column;
        align-items: flex-start;
      }

      .table-controls .control-group {
        width: 100%;
        flex-wrap: wrap;
      }

      .table-controls .control-group>* {
        flex: 1 1 150px;
      }
    }

    #noActivityMessage td {
      background: #fff8f0;
      /* Warna background lembut */
      border: 1px solid #f2dbc1;
      border-radius: 0 0 1rem 1rem;
      font-weight: 500;
    }

    .text-center {
      text-align: center !important;
    }

    .text-brown {
      color: #a97c50 !important;
    }

    .fs-4 {
      font-size: 1.5rem !important;
    }

    .badge {
      font-weight: 600;
      /* KONSISTENSI DENGAN MASTER ADMIN */
      font-size: 0.75rem;
      padding: 0.4em 0.8em;
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

    /* ========== MODAL PROFIL ADMIN ========== */
    .profile-modal-overlay {
      display: none;
      position: fixed;
      z-index: 10001;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      overflow: auto;
      background-color: rgba(0, 0, 0, 0.4);
      backdrop-filter: blur(3px);
      justify-content: center;
      align-items: center;
      animation: fadeIn 0.3s ease;
    }

    .profile-modal-content {
      background-color: #fff;
      margin: 10% auto;
      padding: 25px;
      border-radius: 15px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
      max-width: 400px;
      width: 90%;
      animation: slideIn 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    }

    @keyframes fadeIn {
      from {
        opacity: 0;
      }

      to {
        opacity: 1;
      }
    }

    @keyframes slideIn {
      from {
        transform: translateY(-50px) scale(0.95);
        opacity: 0;
      }

      to {
        transform: translateY(0) scale(1);
        opacity: 1;
      }
    }

    .profile-modal-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      border-bottom: 1px solid #eee;
      padding-bottom: 15px;
      margin-bottom: 20px;
    }

    .profile-modal-header h3 {
      margin: 0;
      color: #a97c50;
      /* Ganti warna agar konsisten */
      font-weight: 700;
      font-size: 1.3rem;
    }

    .close-modal-btn {
      color: #aaa;
      font-size: 28px;
      font-weight: bold;
      border: none;
      background: transparent;
      cursor: pointer;
      transition: color 0.2s;
    }

    .close-modal-btn:hover,
    .close-modal-btn:focus {
      color: #432f17;
      text-decoration: none;
      cursor: pointer;
    }

    .profile-photo-area {
      text-align: center;
      margin-bottom: 25px;
      position: relative;
    }

    .profile-photo-wrapper {
      display: inline-block;
      position: relative;
      width: 120px;
      height: 120px;
    }

    .profile-icon-large {
      /* Menggunakan class dari .user-icon dan penyesuaian untuk ukuran besar */
      font-size: 7rem;
      color: #a97c50;
      border: 5px solid #f6f0e8;
      border-radius: 50%;
      box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
      width: 120px;
      height: 120px;
      display: flex;
      align-items: center;
      justify-content: center;
      object-fit: cover;
      /* Untuk gambar */
    }

    .change-photo-btn {
      position: absolute;
      bottom: 0;
      right: -5px;
      /* Penyesuaian posisi agar di sudut bawah kanan */
      background: #432f17;
      color: white;
      padding: 5px 12px;
      border-radius: 20px;
      font-size: 13px;
      font-weight: 600;
      cursor: pointer;
      border: 2px solid #fff;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
      transition: background 0.2s;
    }

    .change-photo-btn:hover {
      background: #a97c50;
    }

    .remove-photo-btn {
      position: absolute;
      bottom: 70px;
      right: -5px;
      background: #dc3545;
      color: white;
      padding: 5px 12px;
      border-radius: 20px;
      font-size: 13px;
      font-weight: 600;
      cursor: pointer;
      border: 2px solid #fff;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
      transition: background 0.2s;
      border: none;
    }

    .remove-photo-btn:hover {
      background: #c82333;
    }


    .user-info-detail {
      margin-bottom: 15px;
      padding: 10px 0;
      border-bottom: 1px solid #f6f0e8;
    }

    .user-info-detail p {
      font-size: 0.9em;
      color: #a97c50;
      margin: 0;
      font-weight: 600;
    }

    .user-info-detail h4 {
      font-size: 1.1em;
      color: #432f17;
      margin: 5px 0 0 0;
      font-weight: 700;
      word-wrap: break-word;
      /* Tambahkan untuk email/username panjang */
    }

    .role-modal {
      font-size: 14px !important;
      padding: 4px 10px !important;
      margin-top: 5px;
    }

    .profile-modal-footer {
      display: flex;
      justify-content: flex-end;
      gap: 10px;
      padding-top: 15px;
    }

    /* Tombol Logout Konsisten */
    .btn-danger {
      background-color: #dc3545;
      border-color: #dc3545;
      border-radius: 8px;
      /* Konsistensi radius */
      transition: all 0.3s ease;
    }

    .btn-danger:hover {
      background-color: #c82333;
      border-color: #bd2130;
      transform: translateY(-1px);
    }
  </style>
</head>

<body>

  <?php include 'sidebar.php'; ?>

  <main class="main">
    <div class="main-header">
      <div>
        <h2>Dashboard Admin</h2> <small class="text-muted">
          <i class="bi bi-speedometer2"></i> Ringkasan dan Statistik Aktivitas Sistem
          <span class="permission-badge">
            <?= RoleHelper::getRoleDisplayName($display_role) ?>
          </span>
        </small>
      </div>
      <button class="admin-info" id="openProfileModal"
        title="Buka Menu Profil"
        data-username="<?= htmlspecialchars($display_username) ?>">

        <?php if ($final_photo_path && $final_photo_path !== $default_photo): ?>
          <img src="<?= htmlspecialchars($final_photo_path) ?>" alt="Profil"
            class="user-icon"
            style="border-radius: 50%; width: 40px; height: 40px; object-fit: cover; border: 2px solid rgba(255, 255, 255, 1);">
        <?php else: ?>
          <i class="bi bi-person-circle user-icon" id="adminIcon"></i>
        <?php endif; ?>

        <div class="user-details">
          <span class="username"><?= htmlspecialchars($display_username) ?></span>
          <span class="role-badge role-<?= $display_role ?>">
            <?= RoleHelper::getRoleDisplayName($display_role) ?>
          </span>
        </div>
      </button>
    </div>

    <section class="welcome-section">
      <h3 id="welcomeMessage">
        <?php
        // Menggunakan $displayName yang sudah diproses
        echo "Selamat Datang, " . htmlspecialchars($displayName) . "!";
        ?>
      </h3>
      <p>Selamat beraktivitas di sistem Majelis MDPL - <?= RoleHelper::getRoleDisplayName($display_role) ?></p>
    </section>

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

    <section class="chart-section mb-4">
      <h3>Statistik Peserta Bulanan</h3>
      <canvas id="pesertaChart" height="90"></canvas>
    </section>

    <section class="data-table-section">
      <div class="table-controls">
        <h3>Riwayat Aktivitas Terbaru</h3>
        <div class="control-group">
          <select id="activityFilter" class="filter-select">
            <option value="">Semua Aktivitas</option>
            <option value="create">Tambah Data (Create)</option>
            <option value="update">Ubah Data (Update)</option>
            <option value="delete">Hapus Data (Delete)</option>
            <option value="login">Login</option>
            <option value="logout">Logout</option>
          </select>
          <div class="search-container" style="max-width: 280px; width: 100%;">
            <input type="text" id="activitySearchInput" class="search-input" placeholder="Cari detail aktivitas..." />
            <i class="bi bi-search search-icon"></i>
          </div>
        </div>
      </div>

      <div class="table-responsive">
        <table class="activity-log-table">
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
            // Asumsikan file ini mengembalikan variabel $rows yang berisi <tr>
            // PENTING: Pastikan baris-baris ini memiliki data-attribute: data-status="create" atau "delete"
            include '../backend/activity-logs.php';
            echo $rows;
            ?>

            <tr id="noActivityMessage" style="display: none;">
              <td colspan="5" class="text-center p-4">
                <i class="bi bi-filter-circle-fill text-brown fs-4 me-2"></i>
                <span class="text-muted">Tidak ada riwayat aktivitas yang sesuai dengan filter.</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>

    <div class="profile-modal-overlay" id="profileModalOverlay">
      <div class="profile-modal-content">
        <div class="profile-modal-header">
          <h3>Profil Admin</h3>
          <button class="close-modal-btn" id="closeProfileModal">&times;</button>
        </div>

        <div class="profile-modal-body">
          <form id="profilePhotoForm" action="../backend/admin-update-photo.php" method="POST" enctype="multipart/form-data">
            <div class="profile-photo-area">
              <div class="profile-photo-wrapper">
                <?php if ($final_photo_path && $final_photo_path !== $default_photo): ?>
                  <img src="<?= htmlspecialchars($final_photo_path) ?>" alt="Foto Profil Admin"
                    class="profile-icon-large"
                    style="border-radius: 50%;">

                  <button type="button" class="remove-photo-btn" id="removeAdminPhoto">
                    <i class="bi bi-trash-fill"></i> Hapus
                  </button>
                <?php else: ?>
                  <i class="bi bi-person-circle profile-icon-large" id="modalProfileIcon"></i>
                <?php endif; ?>

                <label for="inputAdminPhoto" class="change-photo-btn">
                  <i class="bi bi-camera-fill"></i> Ganti Foto
                </label>
              </div>

              <input type="file" name="admin_foto_profil" id="inputAdminPhoto" accept="image/*" style="display: none;">
              <button type="submit" id="submitAdminPhoto" style="display: none;"></button>
            </div>
          </form>

          <div class="user-info-detail">
            <p>Nama Pengguna:</p>
            <h4><?= htmlspecialchars($display_username) ?></h4>
          </div>

          <div class="user-info-detail">
            <p>Email:</p>
            <h4><?= htmlspecialchars($display_email) ?></h4>
          </div>

          <div class="user-info-detail">
            <p>Role:</p>
            <span class="role-badge role-modal role-<?= $display_role ?>">
              <?= RoleHelper::getRoleDisplayName($display_role) ?>
            </span>
          </div>
        </div>

        <div class="profile-modal-footer">
          <a href="logout.php" class="btn btn-sm btn-danger"><i class="bi bi-box-arrow-right"></i> Logout</a>
        </div>

      </div>
    </div>

  </main>

  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../frontend/dashboard.js"></script>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      // NEW: Penanganan Pesan Sukses
      <?php if (!empty($success_message)): ?>
        Swal.fire({
          title: 'Berhasil!',
          text: '<?= addslashes($success_message) ?>',
          icon: 'success',
          confirmButtonText: 'OK',
          confirmButtonColor: '#a97c50',
          timer: 3000,
          timerProgressBar: true,
          showCloseButton: true
        });
      <?php endif; ?>

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

      /* ... kode JavaScript lainnya ... */

 // ... di dalam document.addEventListener('DOMContentLoaded', () => { ...

// LOGIKA PENCARIAN & FILTER AKTIVITAS
const searchInput = document.getElementById('activitySearchInput');
const filterSelect = document.getElementById('activityFilter');
const tableBody = document.querySelector('.activity-log-table tbody');
const noActivityMessage = document.getElementById('noActivityMessage');

if (searchInput && tableBody && filterSelect && noActivityMessage) {
    
    // Fungsi untuk menghitung ulang kolom No (kolom pertama)
    const renumberVisibleRows = (rows) => {
        let currentNumber = 1;
        rows.forEach(row => {
            if (row.style.display !== 'none' && row.id !== 'noActivityMessage') {
                // Asumsi Kolom No adalah cell index 0
                const noCell = row.cells[0];
                if (noCell) {
                    noCell.textContent = currentNumber;
                    currentNumber++;
                }
            }
        });
    };

    const applyFilterAndSearch = () => {
        const filter = searchInput.value.toLowerCase();
        const activityType = filterSelect.value.toLowerCase(); 
        const rows = tableBody.querySelectorAll('tr');
        let visibleRowCount = 0;

        rows.forEach(row => {
            if (row.id === 'noActivityMessage') {
                return;
            }

            const rowText = row.textContent.toLowerCase();
            
            // Mengambil status dari data-attribute (DIREKOMENDASIKAN)
            const rowStatus = row.getAttribute('data-status') ? row.getAttribute('data-status').toLowerCase() : '';
            
            // Logika Filter Status
            let isVisibleByType;
            if (rowStatus !== '') {
                isVisibleByType = activityType === '' || rowStatus === activityType;
            } else {
                // Fallback (kurang akurat): mencari teks status di seluruh baris
                isVisibleByType = activityType === '' || rowText.includes(activityType);
            }
            
            // Logika Pencarian
            let isVisibleBySearch = rowText.includes(filter);

            // Tampilkan baris jika cocok dengan kedua filter
            const isVisible = isVisibleBySearch && isVisibleByType;
            row.style.display = isVisible ? '' : 'none';
            
            if (isVisible) {
                visibleRowCount++;
            }
        });

        // 1. Hitung ulang nomor urut untuk baris yang terlihat
        renumberVisibleRows(rows);

        // 2. Tampilkan/Sembunyikan pesan No Data
        noActivityMessage.style.display = (visibleRowCount === 0) ? '' : 'none';
    };

    // Panggil fungsi saat page load (default: "Semua Aktivitas")
    applyFilterAndSearch();

    // Tambahkan event listener untuk Input Pencarian dan Dropdown Filter
    searchInput.addEventListener('input', applyFilterAndSearch);
    filterSelect.addEventListener('change', applyFilterAndSearch);
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
      const currentDisplayName = "<?= addslashes($displayName) ?>";

      if (welcomeTitle) {
        welcomeTitle.innerHTML = `${greeting}, ${currentDisplayName}!`;
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

      // LOGIKA MODAL PROFIL DAN GANTI FOTO
      const modalOverlay = document.getElementById('profileModalOverlay');
      const openBtn = document.getElementById('openProfileModal');
      const closeBtn = document.getElementById('closeProfileModal');
      const inputPhoto = document.getElementById('inputAdminPhoto');
      const photoForm = document.getElementById('profilePhotoForm');
      const removePhotoBtn = document.getElementById('removeAdminPhoto');
      const finalPhotoPath = '<?= addslashes($final_photo_path) ?>';
      const defaultPhoto = '<?= addslashes($default_photo) ?>';


      if (openBtn) {
        openBtn.addEventListener('click', () => {
          modalOverlay.style.display = 'flex';
        });
      }

      if (closeBtn) {
        closeBtn.addEventListener('click', () => {
          modalOverlay.style.display = 'none';
        });
      }

      // Tutup modal saat klik di luar area konten
      if (modalOverlay) {
        modalOverlay.addEventListener('click', (e) => {
          if (e.target === modalOverlay) {
            modalOverlay.style.display = 'none';
          }
        });
      }

      // Trigger submit form ketika file dipilih
      if (inputPhoto) {
        inputPhoto.addEventListener('change', function() {
          if (this.files.length > 0) {
            // Cek ukuran file (Misal Max 2MB)
            if (this.files[0].size > 2 * 1024 * 1024) {
              showToast('error', "Ukuran file terlalu besar! Maksimal 2MB.");
              this.value = ''; // Reset input
              return;
            }
            // Kirim form
            photoForm.submit();
          }
        });
      }

      // Tombol Hapus Foto
      if (removePhotoBtn && finalPhotoPath !== defaultPhoto) {
        removePhotoBtn.addEventListener('click', function() {
          Swal.fire({
            title: 'Hapus Foto Profil?',
            text: "Anda yakin ingin menghapus foto profil ini?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#a97c50',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
          }).then((result) => {
            if (result.isConfirmed) {
              // Kirim permintaan hapus ke backend
              window.location.href = '../backend/admin-remove-photo.php';
            }
          });
        });
      }

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

    });
  </script>

</body>

</html>