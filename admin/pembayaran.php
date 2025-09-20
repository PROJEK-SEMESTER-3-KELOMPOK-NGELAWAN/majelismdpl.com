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
    transition: width 0.25s ease;
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
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
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
    box-shadow: 0 4px 14px rgba(0,0,0,0.1);
  }
  .chart-title {
    font-weight: 700;
    font-size: 1.25rem;
    color: #a97c50;
    margin-bottom: 15px;
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
 <section class="chart-container">
  <h3 class="chart-title">Distribusi Pembayaran per Bulan</h3>
  <canvas id="paymentsChart" width="400" height="100"></canvas>
 </section>
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
<div class="modal fade" id="detailPaymentModal" tabindex="-1" aria-labelledby="detailPaymentLabel" aria-hidden="true">
 <div class="modal-dialog modal-dialog-scrollable modal-lg">
  <div class="modal-content">
   <div class="modal-header">
    <h5 class="modal-title" id="detailPaymentLabel">Detail Pembayaran</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
   </div>
   <div class="modal-body" id="detailPaymentBody"></div>
   <div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
   </div>
  </div>
 </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
async function loadPayments() {
 const res = await fetch('payments.json');
 const payments = await res.json();
 renderPayments(payments);
 updateSummary(payments);
 updateChart(payments);
}
function renderPayments(payments) {
 const tbody = document.getElementById('paymentList');
 tbody.innerHTML = '';
 payments.forEach((p,index) => {
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
   const idx=btn.getAttribute('data-index');
   showPaymentDetail(JSON.parse(JSON.stringify(payments[idx])));
  });
 });
}
function updateSummary(payments){
 const totalBayar=payments.reduce((acc,p)=>acc+p.jumlahbayar,0);
 const lunasCount=payments.filter(p=>p.statuspembayaran.toLowerCase()==='selesai').length;
 const prosesCount=payments.filter(p=>p.statuspembayaran.toLowerCase()==='menunggu').length;
 document.querySelector('.summary-item:nth-child(1)').innerHTML=`<i class="bi bi-wallet2"></i> Total Bayar<br />Rp ${totalBayar.toLocaleString('id-ID')}`;
 document.querySelector('.summary-item:nth-child(2)').innerHTML=`<i class="bi bi-check2-circle"></i> Lunas<br />${lunasCount} Pembayaran`;
 document.querySelector('.summary-item:nth-child(3)').innerHTML=`<i class="bi bi-clock-history"></i> Dalam Proses<br />${prosesCount} Pembayaran`;
}
function updateChart(payments){
 const ctx=document.getElementById('paymentsChart').getContext('2d');
 const months=['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
 const monthlyTotals=new Array(12).fill(0);
 payments.forEach(p=>{
  const monthIndex=new Date(p.tanggal).getMonth();
  monthlyTotals[monthIndex]+=p.jumlahbayar;
 });
 if(window.paymentsChartInstance)window.paymentsChartInstance.destroy();
 window.paymentsChartInstance=new Chart(ctx,{
  type:'line',
  data:{
   labels:months,
   datasets:[{
    label:'Jumlah Pembayaran per Bulan',
    data:monthlyTotals,
    fill:true,
    borderColor:'#a97c50',
    backgroundColor:'rgba(169,124,80,0.3)',
    pointBackgroundColor:'#a97c50',
    tension:0.3
   }]
  },
  options:{
   responsive:true,
   plugins:{
    legend:{labels:{color:'#432f17',font:{family:'Poppins',weight:'bold'}}}
   },
   scales:{
    y:{beginAtZero:true,ticks:{color:'#432f17'},grid:{color:'#f5ede0'}},
    x:{ticks:{color:'#a97c50'},grid:{color:'#f5ede0'}}
   }
  }
 });
}
function showPaymentDetail(payment){
 const modalBody=document.getElementById('detailPaymentBody');
 modalBody.innerHTML=`
  <div style="display:flex;flex-wrap:wrap;gap:20px;font-size:1em;color:#3a3a3a;">
   <div style="flex:1 1 45%;"><strong>ID Payment:</strong><br/>${payment.idpayment}</div>
   <div style="flex:1 1 45%;"><strong>ID Booking:</strong><br/>${payment.idbooking}</div>
   <div style="flex:1 1 45%;"><strong>Jumlah Bayar:</strong><br/>Rp ${payment.jumlahbayar.toLocaleString('id-ID')}</div>
   <div style="flex:1 1 45%;"><strong>Tanggal:</strong><br/>${payment.tanggal}</div>
   <div style="flex:1 1 45%;"><strong>Jenis Pembayaran:</strong><br/>${payment.jenispembayaran}</div>
   <div style="flex:1 1 45%;"><strong>Metode:</strong><br/>${payment.metode}</div>
   <div style="flex:1 1 45%;"><strong>Sisa Bayar:</strong><br/>Rp ${payment.sisabayar.toLocaleString('id-ID')}</div>
   <div style="flex:1 1 45%;"><strong>Status Pembayaran:</strong><br/>${payment.statuspembayaran}</div>
  </div>
 `;
 const myModal=new bootstrap.Modal(document.getElementById('detailPaymentModal'));
 myModal.show();
}
loadPayments();
</script>
</body>
</html>
