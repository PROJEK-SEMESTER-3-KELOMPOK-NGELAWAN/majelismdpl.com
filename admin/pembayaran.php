<?php
require_once 'auth_check.php';
require_once '../config.php';


if (!class_exists('RoleHelper')) {
  class RoleHelper
  {
    public static function getRoleDisplayName($role)
    {
      return ucwords(str_replace('_', ' ', $role));
    }
  }
}
$user_role = $user_role ?? 'user';
?>
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
    body {
      background: #f6f0e8;
      color: #232323;
      font-family: "Poppins", Arial, sans-serif;
      min-height: 100vh;
      letter-spacing: 0.3px;
      margin: 0;
    }


    .main {
      margin-left: 240px;
      min-height: 100vh;
      padding: 20px 25px;
      background: #f6f0e8;
      transition: margin-left 0.3s ease;
    }


    @media (max-width: 800px) {
      .main {
        margin-left: 0;
        padding-top: 90px;
        padding-left: 15px;
        padding-right: 15px;
      }
    }


    .text-brown {
      color: #a97c50 !important;
    }


    .bg-brown {
      background-color: #a97c50 !important;
      color: white;
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


    .permission-badge {
      background-color: #28a745;
      color: white;
      font-size: 0.7em;
      padding: 2px 6px;
      border-radius: 4px;
      margin-left: 8px;
    }


    .card {
      border: none;
      border-radius: 15px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
      transition: all 0.3s ease;
    }


    .card:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
    }


    .card-header {
      background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
      border-bottom: 2px solid #a97c50;
      border-radius: 15px 15px 0 0 !important;
      padding: 20px;
    }


    .btn-primary {
      background: linear-gradient(135deg, #a97c50 0%, #8b6332 100%);
      border: none;
      border-radius: 8px;
      padding: 10px 20px;
      font-weight: 500;
      transition: all 0.3s ease;
    }


    .btn-primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(169, 124, 80, 0.4);
      background: linear-gradient(135deg, #8b6332 0%, #a97c50 100%);
    }


    .btn-secondary {
      background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
      color: white;
      border: none;
      border-radius: 8px;
      font-weight: 500;
    }


    .btn-secondary:hover {
      background: linear-gradient(135deg, #5a6268 0%, #495057 100%);
    }


    .btn-close-black {
      filter: none;
      opacity: 0.8;
      transition: all 0.3s ease;
    }


    .btn-close-black:hover {
      opacity: 1;
      transform: scale(1.1);
    }


    .modal-header {
      background: linear-gradient(135deg, #a97c50 0%, #8b6332 100%);
      color: white;
      border: none;
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
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
      padding: 20px 40px;
      text-align: center;
      display: flex;
      flex-direction: column;
      align-items: center;
      flex: 1 1 250px;
      transition: all 0.3s ease;
    }


    .summary-item:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
    }


    .summary-item i {
      font-size: 2.5rem;
      margin-bottom: 10px;
      color: #a97c50;
      background: none;
      padding: 0;
      border-radius: 0;
    }


    .summary-label {
      font-size: 1.0rem;
      font-weight: 600;
      color: #495057;
      margin-bottom: 5px;
    }


    .summary-value {
      font-size: 1.5rem;
      font-weight: 700;
      color: #432f17;
    }


    .summary-item.lunas i {
      color: #28a745 !important;
    }


    .summary-item.pending i {
      color: #ffc107 !important;
    }


    .chart-container {
      background: #fff;
      padding: 25px;
      border-radius: 15px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
      margin-bottom: 30px;
      max-width: 100%;
    }


    .chart-title {
      font-size: 1.25rem;
      font-weight: 700;
      color: #a97c50;
      margin-bottom: 15px;
    }


    #paymentsChart {
      width: auto;
      height: auto;
    }


    .table-responsive {
      margin-top: 20px;
      border-radius: 15px;
      overflow: auto;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    }


    table {
      border-collapse: collapse;
      font-size: 14px;
      width: 100%;
    }


    thead th {
      background: linear-gradient(135deg, #a97c50 0%, #8b6332 100%);
      color: white;
      padding: 15px;
      font-weight: 600;
      border: none;
      text-align: left;
      white-space: nowrap;
    }


    thead th.text-center {
      text-align: center;
    }


    tbody td {
      padding: 12px 15px;
      font-weight: 500;
      color: #432f17;
      border-bottom: 1px solid #f2dbc1;
      vertical-align: middle;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }


    tbody td.text-center {
      text-align: center;
    }


    tbody tr:last-child td {
      border-bottom: none;
    }


    tbody tr:hover {
      background-color: #f9e8d0;
      color: #432f17;
    }


    tbody tr:hover td {
      color: #a97c50;
    }


    /* Style untuk kolom nomor di header */
    thead th.col-number {
      width: 60px;
      text-align: center;
      font-weight: 700;
      color: #fff;
    }

    /* Style untuk kolom nomor di body - WARNA SAMA DENGAN KOLOM LAIN */
    tbody td.col-number {
      width: 60px;
      text-align: center;
      font-weight: 600;
      color: #432f17;
    }

    /* Saat hover, warna nomor ikut berubah seperti kolom lain */
    tbody tr:hover td.col-number {
      color: #a97c50;
    }


    .table-controls {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
      flex-wrap: wrap;
      gap: 15px;
    }


    .search-container {
      position: relative;
      max-width: 350px;
      min-width: 250px;
      flex-grow: 1;
    }


    .filter-select {
      border-radius: 8px;
      height: 42px;
      border: 2px solid #e9ecef;
      padding: 0.375rem 2.25rem 0.375rem 0.75rem;
      font-size: 0.95rem;
      color: #495057;
      transition: all 0.3s ease;
      cursor: pointer;
      width: 100%;
      max-width: 200px;
    }


    .filter-select:focus {
      border-color: #a97c50;
      box-shadow: 0 0 0 0.2rem rgba(169, 124, 80, 0.15);
      outline: none;
    }


    .search-input {
      padding-left: 15px;
      padding-right: 40px;
      border-radius: 8px;
      border: 2px solid #e9ecef;
      height: 42px;
      width: 100%;
      font-size: 0.95rem;
      color: #495057;
      transition: all 0.3s ease;
    }


    .search-input:focus {
      outline: none;
      border-color: #a97c50;
      box-shadow: 0 0 0 0.2rem rgba(169, 124, 80, 0.15);
      transform: translateY(-1px);
    }


    .search-icon {
      position: absolute;
      right: 12px;
      top: 50%;
      transform: translateY(-50%);
      color: #6c757d;
      pointer-events: none;
      font-size: 1.1rem;
    }


    #detailPaymentModal .modal-content {
      border-radius: 15px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
    }


    #detailPaymentModal .modal-header {
      background: linear-gradient(135deg, #a97c50 0%, #8b6332 100%);
      color: white;
      border-radius: 15px 15px 0 0 !important;
      padding: 20px 25px;
    }


    #detailPaymentModal .modal-title {
      color: white !important;
      font-weight: 600;
    }


    .btn-detail {
      width: 40px;
      height: 40px;
      display: flex;
      justify-content: center;
      align-items: center;
      border-radius: 8px;
      border: none;
      padding: 0;
      font-size: 1.2rem;
      background: linear-gradient(135deg, #a97c50 0%, #8b6332 100%);
      color: white;
      transition: all 0.2s ease-in-out;
    }


    .btn-detail:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 8px rgba(169, 124, 80, 0.4);
    }


    .user-info-box {
      background: #f8f9fa;
      border-left: 4px solid #a97c50;
      padding: 12px 15px;
      border-radius: 8px;
      margin-bottom: 15px;
    }


    .user-info-label {
      font-size: 0.85rem;
      color: #6c757d;
      font-weight: 500;
      margin-bottom: 3px;
    }


    .user-info-value {
      font-size: 1rem;
      color: #432f17;
      font-weight: 600;
    }
  </style>
</head>


<body>
  <?php include 'sidebar.php'; ?>


  <main class="main">
    <div class="main-header">
      <div>
        <h2>Kelola Pembayaran</h2>
        <small class="text-muted">
          <i class="bi bi-wallet"></i> Daftar dan status semua transaksi.
          <span class="permission-badge">
            <?= RoleHelper::getRoleDisplayName($user_role) ?>
          </span>
        </small>
      </div>
    </div>


    <section class="payment-summary">
      <div class="summary-item">
        <i class="bi bi-credit-card-2-front"></i>
        <div class="summary-label">Total Bayar Diterima</div>
        <div class="summary-value" id="totalBayarDisplay">Rp 0</div>
      </div>
      <div class="summary-item lunas">
        <i class="bi bi-check-circle"></i>
        <div class="summary-label">Transaksi Lunas</div>
        <div class="summary-value" id="lunasCountDisplay">0 Transaksi</div>
      </div>
      <div class="summary-item pending">
        <i class="bi bi-clock"></i>
        <div class="summary-label">Menunggu Verifikasi</div>
        <div class="summary-value" id="prosesCountDisplay">0 Transaksi</div>
      </div>
    </section>


    <section class="chart-container">
      <h3 class="chart-title">Statistik Pembayaran Bulanan</h3>
      <canvas id="paymentsChart" width="400" height="100"></canvas>
    </section>



    <div class="card">
      <div class="card-header">
        <h5 class="mb-0 text-brown">
          <i class="bi bi-table"></i> Riwayat Transaksi
        </h5>
      </div>
      <div class="card-body p-4">


        <div class="table-controls">
          <div>
            <select id="gunungFilter" class="filter-select">
              <option value="">Semua Gunung (Trip)</option>
            </select>
          </div>


          <div class="search-container">
            <input type="text" id="paymentSearchInput" class="search-input" placeholder="Cari ID Booking, Status, User, atau Nama Gunung..." />
            <i class="bi bi-search search-icon"></i>
          </div>
        </div>


        <div class="table-responsive">
          <table>
            <thead>
              <tr>
                <th class="text-center col-number">No</th>
                <th>ID Payment</th>
                <th>ID Booking</th>
                <th>Gunung (Trip)</th>
                <th>User</th>
                <th>Jumlah Bayar</th>
                <th>Tanggal</th>
                <th>Jenis Pembayaran</th>
                <th>Metode</th>
                <th>Status Pembayaran</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody id="paymentList">
              <tr>
                <td colspan="11" class="text-center p-4">
                  <div class="spinner-border text-brown" role="status">
                    <span class="visually-hidden">Loading...</span>
                  </div>
                  <p class="mt-2 text-muted">Memuat data pembayaran...</p>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>


  </main>


  <!-- Modal Detail Payment -->
  <div class="modal fade" id="detailPaymentModal" tabindex="-1" aria-labelledby="detailPaymentLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content rounded-4 shadow border-0">
        <div class="modal-header">
          <h5 class="modal-title" id="detailPaymentLabel"><i class="bi bi-receipt-cutoff me-2"></i> Detail Pembayaran</h5>
          <button type="button" class="btn-close btn-close-black" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body p-0" style="max-height: 70vh; overflow-y: auto;">
          <div class="d-flex align-items-center gap-3 p-3 border-bottom" style="background:#fff7eb;">
            <div class="rounded-3 bg-white shadow-sm" style="width:60px;height:60px;display:flex;align-items:center;justify-content:center;">
              <i class="bi bi-credit-card-2-front text-brown" style="font-size:2.2rem"></i>
            </div>
            <div>
              <div class="fw-bold mb-1" style="font-size:1.08rem;">ID Payment: <span id="detail_idpayment">-</span></div>
              <div class="text-muted" style="font-size:0.98rem;">ID Booking: <span id="detail_idbooking">-</span></div>
            </div>
          </div>


          <div class="px-4 pt-4">
            <div class="mb-3 fw-semibold" style="font-size:1.06rem;">Informasi User</div>
            <div class="row g-3 mb-3">
              <div class="col-md-6">
                <div class="user-info-box">
                  <div class="user-info-label"><i class="bi bi-person me-1"></i> Username</div>
                  <div class="user-info-value" id="detail_username">-</div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="user-info-box">
                  <div class="user-info-label"><i class="bi bi-envelope me-1"></i> Email</div>
                  <div class="user-info-value" id="detail_email">-</div>
                </div>
              </div>
            </div>
          </div>


          <div class="px-4">
            <div class="mb-3 fw-semibold" style="font-size:1.06rem;">Informasi Trip</div>
            <div class="row g-3 mb-3">
              <div class="col-md-6">
                <div class="user-info-box">
                  <div class="user-info-label"><i class="bi bi-mountain me-1"></i> Gunung</div>
                  <div class="user-info-value" id="detail_gunung">-</div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="user-info-box">
                  <div class="user-info-label"><i class="bi bi-signpost me-1"></i> Jenis Trip</div>
                  <div class="user-info-value" id="detail_jenis_trip">-</div>
                </div>
              </div>
            </div>
          </div>


          <div class="px-4">
            <div class="mb-3 fw-semibold" style="font-size:1.06rem;">Rincian Transaksi</div>
            <div class="rounded-4 p-3 mb-3 border" style="border-color:#f0decdff;">
              <div class="d-flex justify-content-between mb-3">
                <div class="col-7">
                  <div class="fw-medium"><span id="detail_tanggal">-</span></div>
                  <div class="text-muted" style="font-size:0.9rem;"><span id="detail_jenispembayaran">-</span> (<span id="detail_metode">-</span>)</div>
                </div>
                <div class="fw-bold text-brown" id="detail_jumlahbayar">Rp 0</div>
              </div>
              <hr class="my-2" style="border-color:#f2dbc1;">


              <div class="d-flex justify-content-between pt-2">
                <div class="text-brown fw-bold">Total Dibayar</div>
                <div class="fw-bold text-brown" id="subtotal_bayar">Rp 0</div>
              </div>
            </div>
          </div>
          <div class="px-4 pb-3">
            <div class="rounded-4 p-3 mb-3 border" style="background:#fff7eb; border-color:#d9b680;">
              <div class="d-flex justify-content-between align-items-center">
                <div class="fw-bold" style="font-size:1.13rem;">Jumlah Total (Trip)</div>
                <div class="fw-bold text-brown" style="font-size:1.1rem;" id="jumlah_total">Rp 0</div>
              </div>
            </div>


            <div class="d-flex justify-content-between align-items-center mt-2 mb-3">
              <div class="text-muted" style="font-size:0.99rem;"><i class="bi bi-info-circle me-1"></i> Status Pembayaran</div>
              <span id="detail_statuspembayaran"></span>
            </div>
          </div>

          <!-- BAGIAN PESERTA DENGAN HORIZONTAL SCROLL -->
          <div class="border-top pt-3" style="background: #f9f9f9;">
            <div class="px-4 pb-3">
              <div class="mb-2 fw-semibold d-flex justify-content-between align-items-center" style="font-size:1.06rem;">
                <span>Daftar Peserta (<span id="participants_count">0</span> orang)</span>
              </div>

              <div id="participants_loading" class="text-center text-muted py-3">
                <div class="spinner-border spinner-border-sm text-brown" role="status">
                  <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2" style="font-size: 0.9rem;">Memuat data peserta...</p>
              </div>

              <!-- Container dengan horizontal scroll -->
              <div id="participants_container" style="display: none; overflow-x: auto; max-height: 300px;">
                <table class="table table-sm table-hover mb-0" style="border-collapse: collapse; min-width: 600px;">
                  <thead style="background: #ffffff; color: #432f17; border-bottom: 3px solid #a97c50;">
                    <tr>
                      <th class="text-center" style="width: 50px; padding: 10px; border: none; white-space: nowrap; color: #ffffffff; font-weight: 700;">No</th>
                      <th style="padding: 10px; border: none; white-space: nowrap; color: #ffffffff; font-weight: 700;">Nama Peserta</th>
                      <th style="padding: 10px; border: none; white-space: nowrap; color: #ffffffff; font-weight: 700;">Email</th>
                      <th style="padding: 10px; border: none; white-space: nowrap; color: #ffffffff; font-weight: 700;">No WA</th>
                      <th style="padding: 10px; border: none; white-space: nowrap; color: #ffffffff; font-weight: 700;">NIK</th>
                    </tr>
                  </thead>
                  <tbody id="participants_list">
                  </tbody>
                </table>
              </div>

              <div id="participants_empty" style="display: none;" class="text-center text-muted py-3" style="font-size: 0.9rem;">
                <i class="bi bi-info-circle me-1"></i> Tidak ada data peserta
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Tutup</button>
        </div>
      </div>
    </div>
  </div>






  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>



  <!-- LOAD CONFIG.JS TERLEBIH DAHULU (CRITICAL!) -->
  <script src="<?php echo getAssetsUrl('frontend/config.js'); ?>"></script>



  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>



  <!-- BARU LOAD PEMBAYARAN-ADMIN.JS -->
  <script src="<?php echo getAssetsUrl('frontend/pembayaran-admin.js'); ?>"></script>


</body>


</html>