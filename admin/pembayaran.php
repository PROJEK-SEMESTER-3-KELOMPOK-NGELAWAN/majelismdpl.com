<?php
require_once 'auth_check.php';
// Asumsi RoleHelper sudah dimuat di auth_check.php atau file lain yang diperlukan.
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
    /* --- SIDEBAR & GLOBAL (KONSISTENSI FONT & BG) --- */
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
      left: 0;
      top: 0;
      bottom: 0;
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


    /* --- MAIN CONTENT & LAYOUT KONSISTENSI (Dari Master Admin) --- */
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

    /* Header Halaman KONSISTEN */
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


    /* --- CARD & SHADOW KONSISTENSI --- */
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

    /* Tombol Primary KONSISTEN */
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

    /* Tombol Secondary/Tutup Modal KONSISTEN */
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

    /* Close button modal (dari Master Admin) */
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
      /* KONSISTENSI MODAL HEADER */
      background: linear-gradient(135deg, #a97c50 0%, #8b6332 100%);
      color: white;
      border: none;
    }


    /* ----------------------------------------------------------- */
    /* --- SUMMARY CARDS (KEMBALI KE GAYA VERTIKAL ASLI) --- */
    /* ----------------------------------------------------------- */
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
      /* Radius Asli */
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
      padding: 20px 40px;
      /* Padding Asli */
      text-align: center;
      display: flex;
      flex-direction: column;
      /* Vertikal */
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
      /* Icon besar */
      margin-bottom: 10px;
      color: #a97c50;
      /* Default brown */
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

    /* KOREKSI: SUMMARY VALUE ASLI */
    .summary-value {
      font-size: 1.5rem;
      font-weight: 700;
      color: #432f17;
    }

    /* Tambahkan warna spesifik untuk ikon status di summary */
    .summary-item.lunas i {
      color: #28a745 !important;
    }

    .summary-item.pending i {
      color: #ffc107 !important;
    }


    /* --- CHART & TABLE CONTAINER (KONSISTENSI DENGAN CARD) --- */
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


    /* --- TABLE KONSISTENSI (BORDER RADIUS FIX) --- */
    .table-responsive {
      margin-top: 20px;
      border-radius: 15px;
      /* PENTING: Untuk melengkungkan sudut tabel */
      overflow: hidden;
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
    }

    tbody td {
      padding: 12px 15px;
      font-weight: 500;
      color: #432f17;
      border-bottom: 1px solid #f2dbc1;
    }

    tbody tr:hover {
      background-color: #f9e8d0;
      color: #432f17;
    }

    /* --- SEARCH INPUT KONSISTENSI --- */
    .search-container {
      max-width: 450px;
      margin-bottom: 20px;
      position: relative;
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

    /* --- MODAL DETAIL (KONSISTENSI) --- */
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

    /* --- Styling Tombol Detail Khusus --- */
    .btn-detail {
      /* Mengikuti gaya tombol aksi (Edit/Hapus) di menu Peserta */
      width: 40px;
      height: 40px;
      display: flex;
      justify-content: center;
      align-items: center;
      border-radius: 8px;
      /* Sudut melengkung */
      border: none;
      padding: 0;
      font-size: 1.2rem;
      /* Ukuran ikon */

      /* Menggunakan warna utama (Cokelat) */
      background: linear-gradient(135deg, #a97c50 0%, #8b6332 100%);
      color: white;
      transition: all 0.2s ease-in-out;
    }

    .btn-detail:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 8px rgba(169, 124, 80, 0.4);
      /* Ganti warna saat hover jika diperlukan */
    }

    /* --- End Tombol Detail Khusus --- */
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
        <div class="search-container">
          <input type="text" id="paymentSearchInput" class="search-input" placeholder="Cari ID Booking, Nama Peserta, atau Status..." />
          <i class="bi bi-search search-icon"></i>
        </div>

        <div class="table-responsive">
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
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </main>

  <div class="modal fade" id="detailPaymentModal" tabindex="-1" aria-labelledby="detailPaymentLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content rounded-4 shadow border-0">
        <div class="modal-header">
          <h5 class="modal-title" id="detailPaymentLabel"><i class="bi bi-receipt-cutoff me-2"></i> Detail Pembayaran</h5>
          <button type="button" class="btn-close btn-close-black" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body p-0">
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
          <div class="px-4 pb-4">
            <div class="rounded-4 p-3 mb-3 border" style="background:#fff7eb; border-color:#d9b680;">
              <div class="d-flex justify-content-between align-items-center">
                <div class="fw-bold" style="font-size:1.13rem; ">Jumlah Total (Trip)</div>
                <div class="fw-bold text-brown" style="font-size:1.1rem;" id="jumlah_total">Rp 0</div>
              </div>
            </div>
            <div class="d-flex justify-content-between">
              <div class="text-muted" style="font-size:0.99rem;"><i class="bi bi-coin me-1"></i> Sisa Pembayaran</div>
              <div class="fw-bold text-danger" id="detail_sisabayar">Rp 0</div>
            </div>
            <div class="d-flex justify-content-between mt-2">
              <div class="text-muted" style="font-size:0.99rem;"><i class="bi bi-info-circle me-1"></i> Status Verifikasi</div>
              <span class="fw-bold" id="detail_statuspembayaran">-</span>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Tutup</button>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
    async function loadPayments() {
      // Menggunakan data dummy untuk demonstrasi (Hapus ini jika API sudah siap)
      const payments = [{
          idpayment: 'P001',
          idbooking: 'B001',
          jumlahbayar: 500000,
          tanggal: '2025-10-25',
          jenispembayaran: 'DP',
          metode: 'Transfer BCA',
          sisabayar: 1500000,
          statuspembayaran: 'Menunggu'
        },
        {
          idpayment: 'P002',
          idbooking: 'B002',
          jumlahbayar: 2000000,
          tanggal: '2025-10-20',
          jenispembayaran: 'Full',
          metode: 'Transfer Mandiri',
          sisabayar: 0,
          statuspembayaran: 'Selesai'
        },
        {
          idpayment: 'P003',
          idbooking: 'B001',
          jumlahbayar: 1000000,
          tanggal: '2025-10-27',
          jenispembayaran: 'Cicilan',
          metode: 'Cash',
          sisabayar: 500000,
          statuspembayaran: 'Selesai'
        },
        {
          idpayment: 'P004',
          idbooking: 'B003',
          jumlahbayar: 1500000,
          tanggal: '2025-09-10',
          jenispembayaran: 'Full',
          metode: 'Transfer BCA',
          sisabayar: 0,
          statuspembayaran: 'Selesai'
        },
      ];

      renderPayments(payments);
      updateSummary(payments);
      updateChart(payments);
    }

    function renderPayments(payments) {
      const tbody = document.getElementById('paymentList');
      tbody.innerHTML = '';
      payments.forEach((p, index) => {
        const tr = document.createElement('tr');

        let statusClass = 'bg-secondary';
        let statusText = p.statuspembayaran;

        if (p.statuspembayaran.toLowerCase() === 'selesai' || p.statuspembayaran.toLowerCase() === 'lunas') {
          statusClass = 'bg-success';
        } else if (p.statuspembayaran.toLowerCase() === 'menunggu' || p.statuspembayaran.toLowerCase() === 'proses') {
          statusClass = 'bg-warning text-dark';
        } else if (p.statuspembayaran.toLowerCase() === 'batal') {
          statusClass = 'bg-danger';
        }

        const statusBadge = `<span class="badge ${statusClass}">${statusText}</span>`;

        tr.innerHTML = `
                    <td>${p.idpayment}</td>
                    <td>${p.idbooking}</td>
                    <td>Rp ${p.jumlahbayar.toLocaleString('id-ID')}</td>
                    <td>${p.tanggal}</td>
                    <td>${p.jenispembayaran}</td>
                    <td>${p.metode}</td>
                    <td>Rp ${p.sisabayar.toLocaleString('id-ID')}</td>
                    <td>${statusBadge}</td>
                    <td>
                        <button class="btn-detail detail-btn" data-index="${index}">
                            <i class="bi bi-eye"></i>
                        </button>
                    </td>
                `;
        tbody.appendChild(tr);
      });

      document.querySelectorAll('.detail-btn').forEach(btn => {
        btn.addEventListener('click', () => {
          const idx = btn.getAttribute('data-index');
          showPaymentDetail(payments[idx]);
        });
      });
    }

    function updateSummary(payments) {
      const totalBayar = payments.filter(p => p.statuspembayaran.toLowerCase() !== 'batal').reduce((acc, p) => acc + p.jumlahbayar, 0);
      const lunasCount = payments.filter(p => p.statuspembayaran.toLowerCase() === 'selesai' || p.statuspembayaran.toLowerCase() === 'lunas').length;
      const prosesCount = payments.filter(p => p.statuspembayaran.toLowerCase() === 'menunggu' || p.statuspembayaran.toLowerCase() === 'proses').length;

      document.getElementById('totalBayarDisplay').textContent = `Rp ${totalBayar.toLocaleString('id-ID')}`;
      document.getElementById('lunasCountDisplay').textContent = `${lunasCount} Transaksi`;
      document.getElementById('prosesCountDisplay').textContent = `${prosesCount} Transaksi`;
    }

    function updateChart(payments) {
      const ctx = document.getElementById('paymentsChart').getContext('2d');
      const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
      const monthlyTotals = new Array(12).fill(0);
      payments.forEach(p => {
        if (p.statuspembayaran.toLowerCase() !== 'batal') {
          const date = new Date(p.tanggal);
          if (!isNaN(date)) {
            const monthIndex = date.getMonth();
            monthlyTotals[monthIndex] += p.jumlahbayar;
          }
        }
      });

      if (window.paymentsChartInstance) window.paymentsChartInstance.destroy();
      window.paymentsChartInstance = new Chart(ctx, {
        type: 'line',
        data: {
          labels: months,
          datasets: [{
            label: 'Jumlah Pembayaran per Bulan',
            data: monthlyTotals,
            fill: true,
            borderColor: '#a97c50',
            backgroundColor: 'rgba(169,124,80,0.3)',
            pointBackgroundColor: '#a97c50',
            tension: 0.3
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: true,
          plugins: {
            legend: {
              labels: {
                color: '#432f17',
                font: {
                  family: 'Poppins',
                  weight: 'bold'
                }
              }
            }
          },
          scales: {
            y: {
              beginAtZero: true,
              ticks: {
                color: '#432f17'
              },
              grid: {
                color: '#f5ede0'
              }
            },
            x: {
              ticks: {
                color: '#a97c50'
              },
              grid: {
                color: '#f5ede0'
              }
            }
          }
        }
      });
    }

    function showPaymentDetail(payment) {
      document.getElementById('detail_idpayment').textContent = payment.idpayment;
      document.getElementById('detail_idbooking').textContent = payment.idbooking;
      document.getElementById('detail_tanggal').textContent = payment.tanggal;
      document.getElementById('detail_jenispembayaran').textContent = payment.jenispembayaran;
      document.getElementById('detail_metode').textContent = payment.metode;
      document.getElementById('detail_jumlahbayar').textContent = 'Rp ' + payment.jumlahbayar.toLocaleString('id-ID');
      document.getElementById('subtotal_bayar').textContent = 'Rp ' + payment.jumlahbayar.toLocaleString('id-ID');
      document.getElementById('jumlah_total').textContent = 'Rp ' + (payment.jumlahbayar + payment.sisabayar).toLocaleString('id-ID');
      document.getElementById('detail_sisabayar').textContent = 'Rp ' + payment.sisabayar.toLocaleString('id-ID');

      let statusClass = 'text-secondary';
      if (payment.statuspembayaran.toLowerCase() === 'selesai' || payment.statuspembayaran.toLowerCase() === 'lunas') {
        statusClass = 'text-success';
      } else if (payment.statuspembayaran.toLowerCase() === 'menunggu' || payment.statuspembayaran.toLowerCase() === 'proses') {
        statusClass = 'text-warning';
      } else if (payment.statuspembayaran.toLowerCase() === 'batal') {
        statusClass = 'text-danger';
      }

      document.getElementById('detail_statuspembayaran').className = `fw-bold ${statusClass}`;
      document.getElementById('detail_statuspembayaran').textContent = payment.statuspembayaran;

      const myModal = new bootstrap.Modal(document.getElementById('detailPaymentModal'));
      myModal.show();
    }

    // Panggil fungsi saat halaman dimuat
    loadPayments();
  </script>
</body>

</html>