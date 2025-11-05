class DashboardStats {
  constructor() {
    // CEK apakah getApiUrl tersedia
    if (typeof getApiUrl !== "function") {
      console.error(
        "ERROR: getApiUrl function not found. Ensure config.js is loaded before dashboard.js"
      );
      this.baseURL = "backend/dashboard-api.php"; // Fallback
    } else {
      this.baseURL = getApiUrl("dashboard-api.php");
    }

    this.chart = null;
    this.init();
  }

  async init() {
    await Promise.all([this.loadDashboardStats(), this.loadMonthlyChart()]);
    // Auto refresh setiap 30 detik
    setInterval(() => {
      this.loadDashboardStats();
      this.loadMonthlyChart(true);
    }, 30000);
  }

  async loadDashboardStats() {
    try {
      const response = await fetch(`${this.baseURL}?action=getDashboardStats`);
      if (!response.ok)
        throw new Error(`HTTP error! status: ${response.status}`);
      const result = await response.json();
      if (result.success) {
        this.updateAllStats(result.data);
      } else {
        this.showError("Gagal memuat statistik dashboard: " + result.error);
      }
    } catch (error) {
      this.showError(
        "Terjadi kesalahan saat memuat statistik: " + error.message
      );
    }
  }

  updateAllStats(stats) {
    this.updateStat("trip-aktif", stats.trip_aktif || 0);
    this.updateStat("trip-selesai", stats.trip_selesai || 0);
    this.updateStat("total-peserta", stats.total_peserta || 0);
    this.updateStat("pembayaran-pending", stats.pembayaran_pending || 0);
  }

  updateStat(statType, newValue) {
    const el = document.querySelector(`.stat-value[data-stat="${statType}"]`);
    if (!el) {
      console.warn(`Element not found for stat: ${statType}`);
      return;
    }
    const current = parseInt(el.textContent) || 0;
    if (current !== newValue) {
      this.animateNumber(el, current, newValue);
    }
  }

  animateNumber(el, start, end) {
    const duration = 600;
    const steps = 20;
    const inc = (end - start) / steps;
    let val = start,
      i = 0;
    const tm = setInterval(() => {
      i++;
      val += inc;
      if (i >= steps) {
        val = end;
        clearInterval(tm);
      }
      el.textContent = Math.round(val);
    }, duration / steps);
  }

  async loadMonthlyChart(isUpdate = false) {
    try {
      const res = await fetch(`${this.baseURL}?action=getParticipantsMonthly`);
      if (!res.ok) throw new Error(`HTTP error! status: ${res.status}`);
      const json = await res.json();
      if (!json.success) {
        console.warn("Chart data load failed:", json.error);
        return;
      }
      this.renderChart(json.labels || [], json.data || [], isUpdate);
    } catch (e) {
      console.error("Gagal memuat data chart:", e);
    }
  }

  renderChart(labels, data, isUpdate = false) {
    const canvas = document.getElementById("pesertaChart");
    if (!canvas) {
      console.warn("Chart canvas not found");
      return;
    }

    if (this.chart && isUpdate) {
      this.chart.data.labels = labels;
      this.chart.data.datasets[0].data = data;
      this.chart.update();
      return;
    }

    const ctx = canvas.getContext("2d");
    this.chart = new Chart(ctx, {
      type: "line",
      data: {
        labels,
        datasets: [
          {
            label: "Peserta per Bulan",
            data,
            fill: true,
            borderColor: "#bc6ff1",
            backgroundColor: "rgba(188,111,241,0.15)",
            pointBackgroundColor: "#185a9d",
            tension: 0.35,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
          legend: {
            labels: {
              color: "#432f17",
              font: { family: "Poppins", weight: "bold" },
            },
          },
          tooltip: {
            callbacks: {
              label: (ctx) => ` ${ctx.parsed.y.toLocaleString("id-ID")} orang`,
            },
          },
        },
        scales: {
          x: {
            ticks: { color: "#bc6ff1", font: { family: "Poppins" } },
            grid: { color: "#f5ede0" },
          },
          y: {
            beginAtZero: true,
            ticks: {
              color: "#bc6ff1",
              font: { family: "Poppins" },
              callback: (v) => Number(v).toLocaleString("id-ID"),
            },
            grid: { color: "#f5ede0" },
          },
        },
      },
    });
  }

  async getTripCountByStatus(status) {
    try {
      const r = await fetch(
        `${this.baseURL}?action=getTripCountByStatus&status=${status}`
      );
      const j = await r.json();
      return j.success ? j.count : 0;
    } catch (error) {
      console.error("Error getting trip count:", error);
      return 0;
    }
  }

  async getTripsOverview() {
    try {
      const r = await fetch(`${this.baseURL}?action=getTripsOverview`);
      const j = await r.json();
      return j.success ? j.data : null;
    } catch (error) {
      console.error("Error getting trips overview:", error);
      return null;
    }
  }

  async refresh() {
    await this.loadDashboardStats();
    await this.loadMonthlyChart(true);
  }

  showError(msg) {
    console.error("[DashboardStats Error]", msg);
  }
}

// INIT KETIKA DOM READY
document.addEventListener("DOMContentLoaded", () => {
  // WAIT untuk getApiUrl tersedia
  if (typeof getApiUrl === "function") {
    window.dashboardStats = new DashboardStats();
  } else {
    // Retry setelah 500ms jika config.js belum loaded
    setTimeout(() => {
      if (typeof getApiUrl === "function") {
        window.dashboardStats = new DashboardStats();
      } else {
        console.error(
          "FATAL: config.js not loaded. Cannot initialize DashboardStats"
        );
      }
    }, 500);
  }
});

// Global functions
window.refreshDashboard = () => {
  if (window.dashboardStats) {
    window.dashboardStats.refresh();
  }
};

window.getTripCount = async (s) => {
  if (window.dashboardStats) {
    return await window.dashboardStats.getTripCountByStatus(s);
  }
  return 0;
};

window.getTripsOverview = async () => {
  if (window.dashboardStats) {
    return await window.dashboardStats.getTripsOverview();
  }
  return null;
};
