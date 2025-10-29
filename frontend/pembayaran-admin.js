let allPayments = []; // Variabel global untuk menyimpan semua data pembayaran

async function loadPayments() {
    try {
        // Fetch data dari backend API
        const response = await fetch('../backend/pembayaran-admin-api.php');
        const result = await response.json();

        if (!result.success) {
            throw new Error(result.error || 'Gagal memuat data pembayaran');
        }

        allPayments = result.data;

        populateGunungFilter(allPayments);
        renderPayments(allPayments);
        updateSummary(allPayments);
        updateChart(allPayments);
        setupFilterListeners();

    } catch (error) {
        console.error('Error loading payments:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Gagal memuat data pembayaran: ' + error.message,
            confirmButtonColor: '#a97c50'
        });

        // Tampilkan pesan error di tabel
        document.getElementById('paymentList').innerHTML = 
            `<tr><td colspan="10" class="text-center text-danger p-4">
                <i class="bi bi-exclamation-triangle me-2"></i>
                Gagal memuat data pembayaran
            </td></tr>`;
    }
}

function populateGunungFilter(payments) {
    const select = document.getElementById('gunungFilter');
    const uniqueGunungs = [...new Set(payments.map(p => p.gunung))].sort();

    select.innerHTML = '<option value="">Semua Gunung (Trip)</option>';

    uniqueGunungs.forEach(gunung => {
        const option = document.createElement('option');
        option.value = gunung;
        option.textContent = gunung;
        select.appendChild(option);
    });
}

function setupFilterListeners() {
    const searchInput = document.getElementById('paymentSearchInput');
    const gunungFilter = document.getElementById('gunungFilter');

    const applyFilters = () => {
        const searchTerm = searchInput.value.toLowerCase().trim();
        const selectedGunung = gunungFilter.value;

        let filtered = allPayments.filter(p => {
            const matchGunung = selectedGunung === '' || p.gunung === selectedGunung;

            const searchFields = [
                p.idpayment, 
                p.idbooking, 
                p.statuspembayaran, 
                p.gunung,
                p.username,
                p.email
            ];
            const matchSearch = searchTerm === '' || searchFields.some(field =>
                (field || '').toString().toLowerCase().includes(searchTerm)
            );

            return matchGunung && matchSearch;
        });

        renderPayments(filtered);
    };

    searchInput.addEventListener('input', applyFilters);
    gunungFilter.addEventListener('change', applyFilters);
}

function renderPayments(payments) {
    const tbody = document.getElementById('paymentList');
    tbody.innerHTML = '';

    if (payments.length === 0) {
        tbody.innerHTML = `<tr><td colspan="10" class="text-center opacity-50 p-4">Tidak ada data pembayaran yang cocok dengan filter.</td></tr>`;
        return;
    }

    payments.forEach((p, index) => {
        const tr = document.createElement('tr');

        let statusClass = 'bg-secondary';
        let statusText = p.statuspembayaran;

        // Mapping status dari database
        if (p.statuspembayaran.toLowerCase() === 'paid' || 
            p.statuspembayaran.toLowerCase() === 'settlement' || 
            p.statuspembayaran.toLowerCase() === 'selesai' || 
            p.statuspembayaran.toLowerCase() === 'lunas') {
            statusClass = 'bg-success';
            statusText = 'Lunas';
        } else if (p.statuspembayaran.toLowerCase() === 'pending' || 
                   p.statuspembayaran.toLowerCase() === 'menunggu' || 
                   p.statuspembayaran.toLowerCase() === 'proses') {
            statusClass = 'bg-warning text-dark';
            statusText = 'Menunggu';
        } else if (p.statuspembayaran.toLowerCase() === 'failed' || 
                   p.statuspembayaran.toLowerCase() === 'cancelled' || 
                   p.statuspembayaran.toLowerCase() === 'batal') {
            statusClass = 'bg-danger';
            statusText = 'Batal';
        }

        const statusBadge = `<span class="badge ${statusClass}">${statusText}</span>`;

        tr.innerHTML = `
            <td>${p.idpayment}</td>
            <td>${p.idbooking}</td>
            <td>${p.gunung || '-'}</td>
            <td>${p.username || '-'}</td>
            <td>Rp ${p.jumlahbayar.toLocaleString('id-ID')}</td>
            <td>${formatTanggal(p.tanggal)}</td>
            <td>${p.jenispembayaran || '-'}</td>
            <td>${p.metode || '-'}</td>
            <td>${statusBadge}</td>
            <td>
                <button class="btn-detail detail-btn" data-payment-id="${p.idpayment}">
                    <i class="bi bi-eye"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
    });

    // Event listener untuk tombol detail
    document.querySelectorAll('.detail-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const paymentId = e.currentTarget.getAttribute('data-payment-id');
            const paymentDetail = allPayments.find(p => p.idpayment == paymentId);
            if (paymentDetail) showPaymentDetail(paymentDetail);
        });
    });
}

function formatTanggal(dateString) {
    if (!dateString) return '-';
    
    const date = new Date(dateString);
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return date.toLocaleDateString('id-ID', options);
}

function updateSummary(payments) {
    // Hitung total bayar (hanya yang tidak batal)
    const totalBayar = payments.filter(p => {
        const status = p.statuspembayaran.toLowerCase();
        return status !== 'batal' && status !== 'cancelled' && status !== 'failed';
    }).reduce((acc, p) => acc + p.jumlahbayar, 0);

    // Hitung transaksi lunas
    const lunasCount = payments.filter(p => {
        const status = p.statuspembayaran.toLowerCase();
        return status === 'selesai' || status === 'lunas' || status === 'paid' || status === 'settlement';
    }).length;

    // Hitung transaksi pending
    const prosesCount = payments.filter(p => {
        const status = p.statuspembayaran.toLowerCase();
        return status === 'menunggu' || status === 'proses' || status === 'pending';
    }).length;

    document.getElementById('totalBayarDisplay').textContent = `Rp ${totalBayar.toLocaleString('id-ID')}`;
    document.getElementById('lunasCountDisplay').textContent = `${lunasCount} Transaksi`;
    document.getElementById('prosesCountDisplay').textContent = `${prosesCount} Transaksi`;
}

function updateChart(payments) {
    const ctx = document.getElementById('paymentsChart').getContext('2d');
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    const monthlyTotals = new Array(12).fill(0);

    payments.forEach(p => {
        const status = p.statuspembayaran.toLowerCase();
        if (status !== 'batal' && status !== 'cancelled' && status !== 'failed') {
            const date = new Date(p.tanggal);
            if (!isNaN(date)) {
                const monthIndex = date.getMonth();
                monthlyTotals[monthIndex] += p.jumlahbayar;
            }
        }
    });

    if (window.paymentsChartInstance) {
        window.paymentsChartInstance.destroy();
    }

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
                        color: '#432f17',
                        callback: function(value) {
                            return 'Rp ' + value.toLocaleString('id-ID');
                        }
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
    document.getElementById('detail_tanggal').textContent = formatTanggal(payment.tanggal);
    document.getElementById('detail_jenispembayaran').textContent = payment.jenispembayaran || '-';
    document.getElementById('detail_metode').textContent = payment.metode || '-';
    document.getElementById('detail_jumlahbayar').textContent = 'Rp ' + payment.jumlahbayar.toLocaleString('id-ID');
    document.getElementById('subtotal_bayar').textContent = 'Rp ' + payment.jumlahbayar.toLocaleString('id-ID');
    document.getElementById('jumlah_total').textContent = 'Rp ' + payment.total_trip.toLocaleString('id-ID');

    // Tambahkan info user
    document.getElementById('detail_username').textContent = payment.username || '-';
    document.getElementById('detail_email').textContent = payment.email || '-';
    document.getElementById('detail_gunung').textContent = payment.gunung || '-';
    document.getElementById('detail_jenis_trip').textContent = payment.jenis_trip || '-';

    // Status styling
    let statusClass = 'text-secondary';
    let statusText = payment.statuspembayaran;

    if (payment.statuspembayaran.toLowerCase() === 'paid' || 
        payment.statuspembayaran.toLowerCase() === 'settlement' || 
        payment.statuspembayaran.toLowerCase() === 'selesai' || 
        payment.statuspembayaran.toLowerCase() === 'lunas') {
        statusClass = 'text-success';
        statusText = 'Lunas';
    } else if (payment.statuspembayaran.toLowerCase() === 'pending' || 
               payment.statuspembayaran.toLowerCase() === 'menunggu' || 
               payment.statuspembayaran.toLowerCase() === 'proses') {
        statusClass = 'text-warning';
        statusText = 'Menunggu Verifikasi';
    } else if (payment.statuspembayaran.toLowerCase() === 'failed' || 
               payment.statuspembayaran.toLowerCase() === 'cancelled' || 
               payment.statuspembayaran.toLowerCase() === 'batal') {
        statusClass = 'text-danger';
        statusText = 'Dibatalkan';
    }

    document.getElementById('detail_statuspembayaran').className = `fw-bold ${statusClass}`;
    document.getElementById('detail_statuspembayaran').textContent = statusText;

    // Sisa bayar
    const sisaBayar = payment.total_trip - payment.jumlahbayar;
    document.getElementById('detail_sisabayar').textContent = 'Rp ' + (sisaBayar > 0 ? sisaBayar : 0).toLocaleString('id-ID');

    const myModal = new bootstrap.Modal(document.getElementById('detailPaymentModal'));
    myModal.show();
}

// Panggil fungsi saat halaman dimuat
document.addEventListener('DOMContentLoaded', function() {
    loadPayments();
});
