class DashboardStats {
  constructor() {
    this.baseURL = "../backend/dashboard-api.php";
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
    } catch (_) {
      this.showError("Terjadi kesalahan saat memuat statistik");
    }
  }

  updateAllStats(stats) {
    this.updateStat("trip-aktif", stats.trip_aktif);
    this.updateStat("trip-selesai", stats.trip_selesai); // done
    this.updateStat("total-peserta", stats.total_peserta); // dari participants
    this.updateStat("pembayaran-pending", stats.pembayaran_pending);
  }

  updateStat(statType, newValue) {
    const el = document.querySelector(`.stat-value[data-stat="${statType}"]`);
    if (!el) return;
    const current = parseInt(el.textContent) || 0;
    if (current !== newValue) this.animateNumber(el, current, newValue);
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

  // Ambil data peserta bulanan dari participants JOIN bookings
  async loadMonthlyChart(isUpdate = false) {
    try {
      const res = await fetch(`${this.baseURL}?action=getParticipantsMonthly`);
      if (!res.ok) throw new Error(`HTTP error! status: ${res.status}`);
      const json = await res.json();
      if (!json.success) return;
      this.renderChart(json.labels || [], json.data || [], isUpdate);
    } catch (e) {
      console.error("Gagal memuat data chart:", e);
    }
  }

  renderChart(labels, data, isUpdate = false) {
    const canvas = document.getElementById("pesertaChart");
    if (!canvas) return;
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
    } catch (_) {
      return 0;
    }
  }
  async getTripsOverview() {
    try {
      const r = await fetch(`${this.baseURL}?action=getTripsOverview`);
      const j = await r.json();
      return j.success ? j.data : null;
    } catch (_) {
      return null;
    }
  }
  async refresh() {
    await this.loadDashboardStats();
    await this.loadMonthlyChart(true);
  }
  showError(msg) {
    console.error(msg);
  }
}

document.addEventListener("DOMContentLoaded", () => {
  window.dashboardStats = new DashboardStats();
});
window.refreshDashboard = () => window.dashboardStats?.refresh();
window.getTripCount = async (s) =>
  window.dashboardStats
    ? await window.dashboardStats.getTripCountByStatus(s)
    : 0;
window.getTripsOverview = async () =>
  window.dashboardStats ? await window.dashboardStats.getTripsOverview() : null;
