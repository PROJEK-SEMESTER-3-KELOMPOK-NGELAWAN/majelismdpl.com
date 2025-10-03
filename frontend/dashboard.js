class DashboardStats {
    constructor() {
        // UPDATED: Ganti ke dashboard-api.php
        this.baseURL = '../backend/dashboard-api.php';
        this.init();
    }

    async init() {
        await this.loadDashboardStats();
        
        // Auto-refresh setiap 30 detik
        setInterval(() => {
            this.loadDashboardStats();
        }, 30000);
    }

    // Load semua statistik dashboard
    async loadDashboardStats() {
        try {
            const response = await fetch(`${this.baseURL}?action=getDashboardStats`);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const result = await response.json();            
            if (result.success) {
                this.updateAllStats(result.data);
            } else {
                console.error('Failed to load dashboard stats:', result.error);
                this.showError('Gagal memuat statistik dashboard: ' + result.error);
            }
        } catch (error) {
            console.error('Error loading dashboard stats:', error);
            this.showError('Terjadi kesalahan saat memuat statistik');
        }
    }

    // Update semua statistik di dashboard
    updateAllStats(stats) {
        
        // Update Trip Aktif (status = available)
        this.updateStat('trip-aktif', stats.trip_aktif);
        
        // Update Trip Selesai (status = sold)
        this.updateStat('trip-selesai', stats.trip_selesai);
        
        // Update Total Peserta
        this.updateStat('total-peserta', stats.total_peserta);
        
        // Update Pembayaran Pending
        this.updateStat('pembayaran-pending', stats.pembayaran_pending);
    }

    // Update statistik individual dengan animasi
    updateStat(statType, newValue) {
        const element = document.querySelector(`.stat-value[data-stat="${statType}"]`);
        
        if (!element) {
            console.warn(`Element with data-stat="${statType}" not found`);
            return;
        }

        const currentValue = parseInt(element.textContent) || 0;
        
        if (currentValue !== newValue) {
            this.animateNumber(element, currentValue, newValue);
        }
    }

    // Animasi perubahan angka
    animateNumber(element, startValue, endValue) {
        const duration = 1000; // 1 detik
        const steps = 20;
        const increment = (endValue - startValue) / steps;
        let currentValue = startValue;
        let step = 0;

        // Tambah class untuk visual feedback
        element.classList.add('updating');

        const timer = setInterval(() => {
            step++;
            currentValue += increment;
            
            if (step >= steps) {
                currentValue = endValue;
                clearInterval(timer);
                element.classList.remove('updating');
            }
            
            element.textContent = Math.round(currentValue);
        }, duration / steps);
    }

    // UPDATED: Load count berdasarkan status dari dashboard-api
    async getTripCountByStatus(status) {
        try {
            const response = await fetch(`${this.baseURL}?action=getTripCountByStatus&status=${status}`);
            const result = await response.json();
            
            if (result.success) {
                return result.count;
            } else {
                return 0;
            }
        } catch (error) {
            return 0;
        }
    }

    // BONUS: Load trips overview
    async getTripsOverview() {
        try {
            const response = await fetch(`${this.baseURL}?action=getTripsOverview`);
            const result = await response.json();
            
            if (result.success) {
                return result.data;
            } else {
                return null;
            }
        } catch (error) {
            console.error('Error getting trips overview:', error);
            return null;
        }
    }

    // Manual refresh
    async refresh() {
        await this.loadDashboardStats();
    }

    // Show error
    showError(message) {
        console.error(message);
        // Bisa ditambahkan toast notification jika perlu
    }
}

// Initialize saat DOM ready
document.addEventListener('DOMContentLoaded', () => {
    window.dashboardStats = new DashboardStats();
});

// Global functions untuk debugging
window.refreshDashboard = () => {
    if (window.dashboardStats) {
        window.dashboardStats.refresh();
    }
};

window.getTripCount = async (status) => {
    if (window.dashboardStats) {
        return await window.dashboardStats.getTripCountByStatus(status);
    }
    return 0;
};

window.getTripsOverview = async () => {
    if (window.dashboardStats) {
        return await window.dashboardStats.getTripsOverview();
    }
    return null;
};


// GRAFIK Statistik Peserta Bulanan
const ctx = document.getElementById('pesertaChart').getContext('2d');
    const pesertaChart = new Chart(ctx, {
      type: 'line',
      data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep','Okt','Nov','Des'],
        datasets: [{
          label: 'Peserta',
          data: [12, 15, 14, 22, 19, 25, 29, 26, 17],
          fill: true,
          borderColor: '#bc6ff1',
          backgroundColor: 'rgba(188,111,241,0.15)',
          pointBackgroundColor: '#185a9d',
          tension: 0.4
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
          x: {
            ticks: {
              color: '#bc6ff1',
              font: {
                family: 'Poppins'
              }
            },
            grid: {
              color: '#f5ede0'
            }
          },
          y: {
            beginAtZero: true,
            ticks: {
              color: '#bc6ff1',
              font: {
                family: 'Poppins'
              }
            },
            grid: {
              color: '#f5ede0'
            }
          }
        }
      }
    });

