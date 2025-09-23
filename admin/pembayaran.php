<?php
require_once 'auth_check.php';
?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Pembayaran | Majelis MDPL</title>

  <!-- Styles and Fonts -->
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

    .search-container {
      position: relative;
      margin: 0;
      width: 100%;
      max-width: 450px;
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

    .daftar-heading {
      font-size: 1.4rem;
      font-weight: 700;
      color: #a97c50;
      margin: 32px 0 18px;
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
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
      padding: 20px 40px;
      text-align: center;
      color: #3a3a3a;
      font-weight: 700;
      font-size: 1.2em;
      display: flex;
      flex-direction: column;
      align-items: center;
    }

    .summary-item i {
      font-size: 2.5rem;
      margin-bottom: 10px;
      color: #a97c50;
    }

    .chart-container {
      max-width: 900px;
      margin: 0 auto 30px auto;
      background: #fff;
      padding: 20px 30px;
      border-radius: 16px;
      box-shadow: 0 4px 14px rgba(0, 0, 0, 0.1);
    }

    .chart-title {
      font-weight: 700;
      font-size: 1.25rem;
      color: #a97c50;
      margin-bottom: 15px;
    }

    .cards {
      display: flex;
      gap: 19px;
      margin-bottom: 32px;
      flex-wrap: nowrap;
      justify-content: center;
      max-width: 900px;
      margin-left: auto;
      margin-right: auto;
    }

    .card-stat {
      background: #fff;
      border-radius: 1rem;
      padding: 16px 18px;
      box-shadow: 0 1px 7px rgba(120, 77, 37, 0.09);
      min-width: 180px;
      flex: 1 1 180px;
      display: flex;
      align-items: center;
      gap: 14px;
    }

    .card-stat i {
      font-size: 2.2rem;
      color: #a97c50;
    }

    .stat-label {
      font-size: 1rem;
      font-weight: 600;
      color: #a97c50;
    }

    .stat-value {
      font-size: 1.3rem;
      font-weight: 700;
      color: #432f17;
    }

    table {
      width: 100%;
      border-spacing: 0 8px;
      font-size: 13px;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
      background-color: #fff;
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

    #detailPaymentBody {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 16px 40px;
      font-size: 1em;
      color: #3a3a3a;
    }

    #detailPaymentBody p {
      margin: 0 0 8px 0;
    }

    #detailPaymentBody p strong {
      color: #a97c50;
    }
  </style>
</head>

<body>

  <!-- Include Sidebar -->
  <?php include 'sidebar.php'; ?>

  <main class="main">
    <div class="daftar-heading">Daftar Pembayaran</div>
    <section class="cards payment-summary">
      <div class="card-stat">
        <i class="bi bi-wallet2"></i>
        <div>
          <div class="stat-label">Total Bayar</div>
          <div class="stat-value" id="totalBayarDisplay">Rp 4.200.000</div>
        </div>
      </div>
      <div class="card-stat">
        <i class="bi bi-check2-circle"></i>
        <div>
          <div class="stat-label">Lunas</div>
          <div class="stat-value" id="lunasCountDisplay">15 Pembayaran</div>
        </div>
      </div>
      <div class="card-stat">
        <i class="bi bi-clock-history"></i>
        <div>
          <div class="stat-label">Dalam Proses</div>
          <div class="stat-value" id="prosesCountDisplay">3 Pembayaran</div>
        </div>
      </div>
    </section>
    <section class="chart-container">
      <h3 class="chart-title">Distribusi Pembayaran per Bulan</h3>
      <canvas id="paymentsChart" width="400" height="100"></canvas>
    </section>
    <div class="search-container" style="max-width: 450px; margin-bottom: 15px;">
      <input type="text" id="paymentSearchInput" class="search-input" placeholder="Cari pembayaran..." />
      <i class="bi bi-search search-icon"></i>
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
      <tbody id="paymentList"></tbody>
    </table>
  </main>

  <!-- Modal Detail Pembayaran -->
  <div class="modal fade" id="detailPaymentModal" tabindex="-1" aria-labelledby="detailPaymentLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content rounded-4 shadow border-0">
        <div class="modal-header">
          <h5 class="modal-title text-black" id="detailPaymentLabel"><i class="bi bi-receipt-cutoff me-2"></i> Detail Pembayaran</h5>
          <button type="button" class="btn-close btn-close-black" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body p-0" style="background:#fdfaf7;">
          <div class="d-flex align-items-center gap-3 p-3 border-bottom" style="background:#fff5e6;">
            <div class="rounded-3 bg-white shadow-sm" style="width:60px;height:60px;display:flex;align-items:center;justify-content:center;">
              <i class="bi bi-credit-card-2-front text-brown" style="font-size:2.2rem"></i>
            </div>
            <div>
              <div class="fw-bold mb-1" style="font-size:1.08rem;">ID Payment: <span id="detail_idpayment">-</span></div>
              <div class="text-muted" style="font-size:0.98rem;">ID Booking: <span id="detail_idbooking">-</span></div>
            </div>
          </div>
          <div class="px-3 pt-3">
            <div class="mb-2 fw-semibold" style="font-size:1.06rem;">Rincian Harga</div>
            <div class="rounded-4 p-3 mb-2">
              <div class="d-flex justify-content-between">
                <div>
                  <div><span id="detail_tanggal">-</span></div>
                  <div class="text-muted" style="font-size:0.96rem;"><span id="detail_jenispembayaran">-</span> (<span id="detail_metode">-</span>)</div>
                </div>
                <div class="fw text-brown" id="detail_jumlahbayar">Rp 0</div>
              </div>
              <hr class="my-2" style="border-color:#d7b577;">
              <div class="d-flex justify-content-between">
                <div class="text-brown fw-semibold">Subtotal</div>
                <div class="fw text-brown" id="subtotal_bayar">Rp 0</div>
              </div>
            </div>
          </div>
          <div class="px-3 pb-3">
            <div class="rounded-4 p-3 mb-3 " style="background:#fff7eb; border:1.5px solid #f0decdff;">
              <div class="d-flex justify-content-between align-items-center">
                <div class="fw-bold" style="font-size:1.13rem; ">Jumlah Total</div>
                <div class="fw-bold text-brown" style="font-size:1.1rem;" id="jumlah_total">Rp 0</div>
              </div>
            </div>
            <div class="d-flex justify-content-between">
              <div class="text-muted" style="font-size:0.99rem;"><i class="bi bi-coin"></i> Sisa Bayar</div>
              <div class="fw" id="detail_sisabayar">Rp 0</div>
            </div>
            <div class="d-flex justify-content-between mt-1">
              <div class="text-muted" style="font-size:0.99rem;"><i class="bi bi-info-circle"></i> Status</div>
              <span class="fw" id="detail_statuspembayaran">-</span>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-brown px-4" data-bs-dismiss="modal" style="background:#a97c50; color: white; border:none;">Tutup</button>
        </div>
      </div>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
    const paymentSearchInput = document.getElementById('paymentSearchInput');
    paymentSearchInput.addEventListener('input', function() {
      const filter = this.value.toLowerCase();
      const rows = document.querySelectorAll('#paymentList tr');
      rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
      });
    });

    async function loadPayments() {
      const res = await fetch('payment.json');
      const payments = await res.json();
      renderPayments(payments);
      updateSummary(payments);
      updateChart(payments);
    }

    function renderPayments(payments) {
      const tbody = document.getElementById('paymentList');
      tbody.innerHTML = '';
      payments.forEach((p, index) => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
   <td>${p.idpayment}</td>
   <td>${p.idbooking}</td>
   <td>Rp ${p.jumlahbayar.toLocaleString('id-ID')}</td>
   <td>${p.tanggal}</td>
   <td>${p.jenispembayaran}</td>
   <td>${p.metode}</td>
   <td>Rp ${p.sisabayar.toLocaleString('id-ID')}</td>
   <td>${p.statuspembayaran}</td>
   <td><button class="btn btn-primary btn-sm detail-btn" data-index="${index}">Detail</button></td>
  `;
        tbody.appendChild(tr);
      });
      document.querySelectorAll('.detail-btn').forEach(btn => {
        btn.addEventListener('click', () => {
          const idx = btn.getAttribute('data-index');
          showPaymentDetail(JSON.parse(JSON.stringify(payments[idx])));
        });
      });
    }

    function updateSummary(payments) {
      const totalBayar = payments.reduce((acc, p) => acc + p.jumlahbayar, 0);
      const lunasCount = payments.filter(p => p.statuspembayaran.toLowerCase() === 'selesai').length;
      const prosesCount = payments.filter(p => p.statuspembayaran.toLowerCase() === 'menunggu').length;

      document.getElementById('totalBayarDisplay').textContent = `Rp ${totalBayar.toLocaleString('id-ID')}`;
      document.getElementById('lunasCountDisplay').textContent = `${lunasCount} Pembayaran`;
      document.getElementById('prosesCountDisplay').textContent = `${prosesCount} Pembayaran`;
    }

    function updateChart(payments) {
      const ctx = document.getElementById('paymentsChart').getContext('2d');
      const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
      const monthlyTotals = new Array(12).fill(0);
      payments.forEach(p => {
        const monthIndex = new Date(p.tanggal).getMonth();
        monthlyTotals[monthIndex] += p.jumlahbayar;
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
      document.getElementById('jumlah_total').textContent = 'Rp ' + payment.jumlahbayar.toLocaleString('id-ID');
      document.getElementById('detail_sisabayar').textContent = 'Rp ' + payment.sisabayar.toLocaleString('id-ID');
      document.getElementById('detail_statuspembayaran').textContent = payment.statuspembayaran;

      const myModal = new bootstrap.Modal(document.getElementById('detailPaymentModal'));
      myModal.show();
    }
    loadPayments();
  </script>
</body>
</html>