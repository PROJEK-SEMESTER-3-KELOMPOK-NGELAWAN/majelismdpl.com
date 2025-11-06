// ========== CONFIG CHECK (CRITICAL!) ==========
if (typeof getApiUrl !== "function") {
  console.error("FATAL ERROR: config.js is not loaded!");
  console.error(
    "Please ensure frontend/config.js is loaded BEFORE pembayaran-admin.js"
  );

  // Fallback untuk debugging
  window.getApiUrl = function (endpoint) {
    console.warn("Using fallback getApiUrl - config.js might not be loaded");
    return "backend/" + endpoint;
  };
}

/**
 * ============================================
 * FILE: frontend/pembayaran-admin.js
 * FUNGSI: Handle UI Pembayaran management
 * UPDATED: Config.js integration + Vanilla JS (NO jQuery)
 * ============================================
 */

let paymentsData = [];
let chartInstance = null;
const PEMBAYARAN_API_URL =
  typeof getApiUrl === "function"
    ? getApiUrl("pembayaran-admin-api.php")
    : "backend/pembayaran-admin-api.php";

// ========== DOM SELECTORS ==========
const dom = {
  paymentList: document.getElementById("paymentList"),
  paymentSearchInput: document.getElementById("paymentSearchInput"),
  gunungFilter: document.getElementById("gunungFilter"),
  totalBayarDisplay: document.getElementById("totalBayarDisplay"),
  lunasCountDisplay: document.getElementById("lunasCountDisplay"),
  prosesCountDisplay: document.getElementById("prosesCountDisplay"),
  paymentsChart: document.getElementById("paymentsChart"),
  detailPaymentModal: document.getElementById("detailPaymentModal"),
};

// ========== DOCUMENT READY ==========
document.addEventListener("DOMContentLoaded", function () {
  // Verify config loaded
  if (typeof getApiUrl !== "function") {
    console.error("getApiUrl function not available");
    Swal.fire({
      title: "Error!",
      text: "Konfigurasi aplikasi tidak lengkap. Silakan refresh halaman.",
      icon: "error",
      confirmButtonColor: "#a97c50",
    });
    return;
  }

  loadPayments();
  setupEventListeners();
});

/**
 * Load payments data from API
 */
function loadPayments() {
  fetch(PEMBAYARAN_API_URL, {
    method: "GET",
    headers: {
      "Content-Type": "application/json",
    },
  })
    .then((res) => {
      if (!res.ok) {
        throw new Error(`HTTP error! status: ${res.status}`);
      }
      return res.json();
    })
    .then((response) => {
      if (response.success) {
        paymentsData = response.data || [];
        renderPaymentsTable(paymentsData);
        updateSummary();
        updateChart();
        populateGunungFilter();
      } else {
        Swal.fire({
          title: "Error!",
          text: response.error || "Gagal memuat data pembayaran",
          icon: "error",
          confirmButtonColor: "#a97c50",
        });
      }
    })
    .catch((error) => {
      console.error("Error loading payments:", error);
      let errorMessage = "Terjadi kesalahan saat memuat data pembayaran";
      if (error.message.includes("timeout")) {
        errorMessage = "Koneksi timeout. Silakan coba lagi.";
      }
      Swal.fire({
        title: "Error Koneksi",
        text: errorMessage,
        icon: "error",
        confirmButtonColor: "#a97c50",
      });
    });
}

/**
 * Render payments table
 */
function renderPaymentsTable(payments) {
  if (!dom.paymentList) return;

  dom.paymentList.innerHTML = "";

  if (!payments || payments.length === 0) {
    dom.paymentList.innerHTML =
      '<tr><td colspan="10" class="text-center text-muted p-4">Tidak ada data pembayaran.</td></tr>';
    return;
  }

  payments.forEach((payment, index) => {
    const statusColor =
      payment.statuspembayaran === "paid"
        ? "success"
        : payment.statuspembayaran === "pending"
        ? "warning"
        : "danger";

    const row = document.createElement("tr");
    row.innerHTML = `
      <td>${payment.idpayment}</td>
      <td>${payment.idbooking}</td>
      <td>${payment.gunung}</td>
      <td>${payment.username}</td>
      <td>Rp ${formatCurrency(payment.jumlahbayar)}</td>
      <td>${payment.tanggal}</td>
      <td>${payment.jenispembayaran}</td>
      <td>${payment.metode}</td>
      <td><span class="badge bg-${statusColor}">${formatStatus(
      payment.statuspembayaran
    )}</span></td>
      <td>
        <button class="btn-detail" onclick="showDetailModal(${index})" title="Lihat Detail">
          <i class="bi bi-eye"></i>
        </button>
      </td>
    `;

    dom.paymentList.appendChild(row);
  });
}

/**
 * Update summary cards
 */
function updateSummary() {
  let totalBayar = 0;
  let lunasCount = 0;
  let prosesCount = 0;

  paymentsData.forEach((payment) => {
    totalBayar += payment.jumlahbayar;
    if (payment.statuspembayaran === "paid") {
      lunasCount++;
    } else if (payment.statuspembayaran === "pending") {
      prosesCount++;
    }
  });

  if (dom.totalBayarDisplay) {
    dom.totalBayarDisplay.textContent = "Rp " + formatCurrency(totalBayar);
  }
  if (dom.lunasCountDisplay) {
    dom.lunasCountDisplay.textContent = lunasCount + " Transaksi";
  }
  if (dom.prosesCountDisplay) {
    dom.prosesCountDisplay.textContent = prosesCount + " Transaksi";
  }
}

/**
 * Format Month Name - Ubah format YYYY-MM menjadi Nama Bulan Tahun
 */
function formatMonthName(monthString) {
  const months = {
    "01": "Januari",
    "02": "Februari",
    "03": "Maret",
    "04": "April",
    "05": "Mei",
    "06": "Juni",
    "07": "Juli",
    "08": "Agustus",
    "09": "September",
    "10": "Oktober",
    "11": "November",
    "12": "Desember",
  };
  const [year, month] = monthString.split("-");
  return months[month] + " " + year;
}

/**
 * Update chart - AREA CHART DENGAN GRADIENT YANG ELEGAN
 */
function updateChart() {
  if (!dom.paymentsChart) return;

  const monthlyData = {};
  paymentsData.forEach((payment) => {
    const month = payment.tanggal.substring(0, 7);
    if (!monthlyData[month]) {
      monthlyData[month] = 0;
    }
    monthlyData[month] += payment.jumlahbayar;
  });

  const months = Object.keys(monthlyData).sort();
  const monthLabels = months.map((m) => formatMonthName(m));
  const amounts = months.map((m) => monthlyData[m]);

  if (chartInstance) {
    chartInstance.destroy();
  }

  const ctx = dom.paymentsChart.getContext("2d");
  
  // Create gradient
  const gradient = ctx.createLinearGradient(0, 0, 0, 400);
  gradient.addColorStop(0, "rgba(169, 124, 80, 0.4)");
  gradient.addColorStop(1, "rgba(169, 124, 80, 0.01)");

  chartInstance = new Chart(ctx, {
    type: "line",
    data: {
      labels: monthLabels,
      datasets: [
        {
          label: "Total Pembayaran",
          data: amounts,
          borderColor: "#a97c50",
          backgroundColor: gradient,
          borderWidth: 4,
          fill: true,
          tension: 0.45,
          pointRadius: 7,
          pointBackgroundColor: "#ffffff",
          pointBorderColor: "#a97c50",
          pointBorderWidth: 3,
          pointHoverRadius: 10,
          pointHoverBackgroundColor: "#a97c50",
          pointHoverBorderColor: "#ffffff",
          pointHoverBorderWidth: 3,
          segment: {
            borderDash: [],
          },
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: true,
      interaction: {
        mode: "index",
        intersect: false,
      },
      plugins: {
        legend: {
          display: true,
          labels: {
            font: { size: 13, weight: "600" },
            color: "#495057",
            padding: 20,
            usePointStyle: true,
            pointStyle: "circle",
          },
        },
        tooltip: {
          backgroundColor: "rgba(0, 0, 0, 0.8)",
          titleColor: "#fff",
          bodyColor: "#fff",
          borderColor: "#a97c50",
          borderWidth: 1,
          padding: 12,
          displayColors: true,
          callbacks: {
            label: function (context) {
              let label = context.dataset.label || "";
              if (label) {
                label += ": ";
              }
              label += "Rp " + formatCurrency(context.parsed.y);
              return label;
            },
          },
        },
      },
      scales: {
        y: {
          beginAtZero: true,
          grid: {
            color: "rgba(0, 0, 0, 0.05)",
            drawBorder: true,
          },
          ticks: {
            font: { size: 11 },
            color: "#6c757d",
            callback: function (value) {
              return "Rp " + formatCurrency(value);
            },
          },
        },
        x: {
          grid: {
            display: false,
          },
          ticks: {
            font: { size: 11, weight: "500" },
            color: "#495057",
          },
        },
      },
    },
  });
}

/**
 * Populate gunung filter dropdown
 */
function populateGunungFilter() {
  if (!dom.gunungFilter) return;

  const gunungSet = new Set();
  paymentsData.forEach((payment) => {
    gunungSet.add(payment.gunung);
  });

  const existingOptions = Array.from(dom.gunungFilter.options).map(
    (opt) => opt.value
  );
  gunungSet.forEach((gunung) => {
    if (!existingOptions.includes(gunung)) {
      const option = document.createElement("option");
      option.value = gunung;
      option.textContent = gunung;
      dom.gunungFilter.appendChild(option);
    }
  });
}

/**
 * Show detail modal
 */
function showDetailModal(index) {
  const payment = paymentsData[index];
  if (!payment) return;

  // Update modal content
  document.getElementById("detail_idpayment").textContent = payment.idpayment;
  document.getElementById("detail_idbooking").textContent = payment.idbooking;
  document.getElementById("detail_username").textContent = payment.username;
  document.getElementById("detail_email").textContent = payment.email;
  document.getElementById("detail_gunung").textContent = payment.gunung;
  document.getElementById("detail_jenis_trip").textContent = payment.jenis_trip;
  document.getElementById("detail_tanggal").textContent = payment.tanggal;
  document.getElementById("detail_jenispembayaran").textContent =
    payment.jenispembayaran;
  document.getElementById("detail_metode").textContent = payment.metode;
  document.getElementById("detail_jumlahbayar").textContent =
    "Rp " + formatCurrency(payment.jumlahbayar);
  document.getElementById("subtotal_bayar").textContent =
    "Rp " + formatCurrency(payment.jumlahbayar);
  document.getElementById("jumlah_total").textContent =
    "Rp " + formatCurrency(payment.total_trip);
  document.getElementById("detail_sisabayar").textContent =
    "Rp " + formatCurrency(payment.sisabayar);
  document.getElementById("detail_statuspembayaran").textContent = formatStatus(
    payment.statuspembayaran
  );

  // Show modal
  if (dom.detailPaymentModal) {
    const modal = new bootstrap.Modal(dom.detailPaymentModal);
    modal.show();
  }
}

/**
 * Setup event listeners for search and filter
 */
function setupEventListeners() {
  if (dom.paymentSearchInput) {
    dom.paymentSearchInput.addEventListener("input", function () {
      applyFilters();
    });
  }

  if (dom.gunungFilter) {
    dom.gunungFilter.addEventListener("change", function () {
      applyFilters();
    });
  }
}

/**
 * Apply filters to table
 */
function applyFilters() {
  const searchTerm = dom.paymentSearchInput
    ? dom.paymentSearchInput.value.toLowerCase()
    : "";
  const gunungFilter = dom.gunungFilter ? dom.gunungFilter.value : "";

  const filteredPayments = paymentsData.filter((payment) => {
    const matchSearch =
      payment.idpayment.toString().includes(searchTerm) ||
      payment.idbooking.toString().includes(searchTerm) ||
      payment.username.toLowerCase().includes(searchTerm) ||
      payment.email.toLowerCase().includes(searchTerm) ||
      payment.gunung.toLowerCase().includes(searchTerm) ||
      formatStatus(payment.statuspembayaran).toLowerCase().includes(searchTerm);

    const matchGunung = !gunungFilter || payment.gunung === gunungFilter;

    return matchSearch && matchGunung;
  });

  renderPaymentsTable(filteredPayments);
}

/**
 * Format currency
 */
function formatCurrency(value) {
  return new Intl.NumberFormat("id-ID").format(value);
}

/**
 * Format status
 */
function formatStatus(status) {
  const statusMap = {
    paid: "Lunas",
    pending: "Menunggu",
    failed: "Gagal",
  };
  return statusMap[status] || status;
}
