/**
 * ========================================
 * Variabel Global dan Konfigurasi
 * ========================================
 */
let currentEditTripId = null;
let tripsData = []; // Menyimpan data trip global (cache)
const FORM_ID = "formTambahTrip";
const MODAL_ID = "tambahTripModal";
const API_URL = "../backend/trip-api.php";

// Helper untuk menampilkan notifikasi toast SweetAlert
function showToast(type, message) {
  Swal.fire({
    toast: true,
    position: "top-end",
    icon: type,
    title: message,
    showConfirmButton: false,
    timer: 2000,
    timerProgressBar: true,
    customClass: {
      popup: "colored-toast",
    },
  });
}

/**
 * ========================================
 * Fungsi Load dan Render Trip
 * ========================================
 */

// Muat semua trip dari backend API
async function loadTrips() {
  console.log("Memuat data trip...");
  try {
    const res = await fetch(`${API_URL}?action=getTrips`);
    if (!res.ok) {
      throw new Error(`HTTP error! status: ${res.status}`);
    }
    const trips = await res.json();
    tripsData = trips; // Perbarui data di global
    displayTrips(trips);
  } catch (err) {
    showToast("error", "Gagal memuat data trip");
    console.error("Kesalahan saat memuat trip:", err);
  }
}

// Render (tampilkan) trip ke dalam kartu di halaman
function displayTrips(trips) {
  const tripListContainer = document.getElementById("tripList");
  const emptyStateElement = document.getElementById("emptyState");
  tripListContainer.innerHTML = "";

  if (!Array.isArray(trips) || trips.length === 0) {
    emptyStateElement.style.display = "block";
    return;
  }

  emptyStateElement.style.display = "none";

  const html = trips
    .map((trip) => {
      const ulasanCount = Math.floor(Math.random() * 900) + 100;
      const isAvailable = trip.status.toLowerCase() === "available";
      const statusClass = trip.status.toLowerCase();
      const iconClass = isAvailable ? "check-circle" : "x-circle";
      const imageUrl = trip.gambar
        ? `../${trip.gambar}`
        : "https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=600&q=80";
      const formattedPrice = parseInt(trip.harga).toLocaleString("id-ID");

      return `
      <div class="trip-card">
        <span class="trip-status ${statusClass}">
          <i class="bi bi-${iconClass}"></i> ${trip.status}
        </span>
        <img src="${imageUrl}" alt="${trip.nama_gunung}" class="trip-thumb" />
        <div class="trip-card-body">
          <div class="trip-meta">
            <span><i class="bi bi-calendar-event"></i> ${trip.tanggal}</span>
            <span><i class="bi bi-clock"></i> ${trip.durasi}</span>
          </div>
          <div class="trip-title">${trip.nama_gunung}</div>
          <div class="trip-type mb-1"><i class="bi bi-flag"></i> ${trip.jenis_trip}</div>
          
          <div class="trip-rating">
            <i class="bi bi-star-fill"></i>
            <span class="rating-number">5</span>
            <span class="sub">(${ulasanCount}+ ulasan)</span>
          </div>

          <div class="trip-via"><i class="bi bi-signpost-2"></i> Via ${trip.via_gunung}</div>
          <div class="trip-price">Rp ${formattedPrice}</div>

          <div class="btn-action-group">
            <button class="btn-action btn-edit" data-id="${trip.id_trip}" title="Edit Trip">
              <i class="bi bi-pencil-square"></i>
            </button>
            <button class="btn-action btn-delete" data-id="${trip.id_trip}" title="Hapus Trip">
              <i class="bi bi-trash"></i>
            </button>
            <button class="btn-action btn-detail" data-id="${trip.id_trip}" title="Lihat Detail">
              <i class="bi bi-arrow-right"></i>
            </button>
          </div>
        </div>
      </div>
    `;
    })
    .join("");

  tripListContainer.innerHTML = html;
  attachEventListeners();
}

/**
 * ========================================
 * Handler Aksi (Delete, Edit, Detail)
 * ========================================
 */

// Menangani klik tombol Hapus (Delete)
async function handleDelete(id_trip) {
  const { isConfirmed } = await Swal.fire({
    title: "Hapus Trip?",
    text: "Data akan dihapus permanen.",
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Ya, Hapus",
    cancelButtonText: "Batal",
    reverseButtons: true,
  });

  if (isConfirmed) {
    try {
      const formData = new FormData();
      formData.append("id_trip", id_trip);

      const res = await fetch(`${API_URL}?action=deleteTrip`, {
        method: "POST",
        body: formData,
      });

      const result = await res.json();

      if (result.success) {
        showToast("success", "Trip berhasil dihapus");
        loadTrips();
      } else {
        showToast("error", result.msg || "Gagal menghapus trip");
      }
    } catch (e) {
      showToast("error", "Kesalahan koneksi saat menghapus trip");
      console.error("Kesalahan Hapus:", e);
    }
  }
}

// Menangani klik tombol Edit (Mengisi form modal)
function handleEdit(id_trip) {
  const trip = tripsData.find((t) => t.id_trip == id_trip);
  if (trip) {
    currentEditTripId = trip.id_trip;
    const form = document.getElementById(FORM_ID);

    // Set Judul Modal & Hidden Fields untuk UPDATE
    document.getElementById("modalTitleText").textContent = "Edit Trip";
    document.getElementById("tripIdInput").value = trip.id_trip;
    document.getElementById("actionType").value = "updateTrip";

    // Isi semua field form
    form.nama_gunung.value = trip.nama_gunung;
    form.tanggal.value = trip.tanggal;
    form.slot.value = trip.slot;
    form.durasi.value = trip.durasi;
    form.jenis_trip.value = trip.jenis_trip;
    form.harga.value = trip.harga;
    form.via_gunung.value = trip.via_gunung;
    form.status.value = trip.status;

    // Tampilkan modal
    const modal = new bootstrap.Modal(document.getElementById(MODAL_ID));
    modal.show();
  } else {
    showToast("warning", "Trip tidak ditemukan!");
  }
}

// Menangani klik tombol Detail
function handleDetail(id_trip) {
  window.location.href = `detailTrip.php?id=${id_trip}`;
}

// Pasang Event Listener ke tombol Aksi
function attachEventListeners() {
  document.querySelectorAll(".btn-delete").forEach((btn) => {
    btn.onclick = () => handleDelete(btn.dataset.id);
  });

  document.querySelectorAll(".btn-edit").forEach((btn) => {
    btn.onclick = () => handleEdit(btn.dataset.id);
  });

  document.querySelectorAll(".btn-detail").forEach((btn) => {
    btn.onclick = () => handleDetail(btn.dataset.id);
  });
}

/**
 * ========================================
 * Handler Form Submit (Tambah/Update)
 * ========================================
 */

// Menangani submit form Tambah/Edit Trip
document.getElementById(FORM_ID).onsubmit = async function (e) {
  e.preventDefault();
  const form = e.target;
  const formData = new FormData(form);

  // Ambil ID dan Action dari hidden fields (untuk konsistensi)
  const tripId = document.getElementById("tripIdInput").value;
  const action = document.getElementById("actionType").value;

  // Catatan: Anda bisa menggunakan 'currentEditTripId' atau 'actionType'
  // Disini kita konsisten menggunakan 'action' dari hidden field.
  let url = `${API_URL}?action=${action}`;

  try {
    const res = await fetch(url, {
      method: "POST",
      body: formData,
    });

    const result = await res.json();

    if (result.success) {
      const message =
        action === "updateTrip"
          ? "Trip berhasil diperbarui"
          : "Trip berhasil disimpan";
      showToast("success", message);

      // Reset state dan muat ulang data
      resetFormAndModalState();
      loadTrips();
    } else {
      showToast("error", result.msg || "Gagal menyimpan trip");
    }
  } catch (e) {
    showToast("error", "Kesalahan saat mengirim data trip");
    console.error("Kesalahan Form Submit:", e);
  }
};

// Fungsi untuk mereset state form dan modal setelah operasi
function resetFormAndModalState() {
  const form = document.getElementById(FORM_ID);

  // 1. Reset state global
  currentEditTripId = null;

  // 2. Reset form
  form.reset();

  // 3. Reset Hidden Fields dan Judul Modal
  document.getElementById("actionType").value = "addTrip"; // Kembali ke mode 'add'
  document.getElementById("tripIdInput").value = "";
  document.getElementById("modalTitleText").textContent = "Tambah Trip Baru";

  // 4. Sembunyikan modal
  const modalElement = document.getElementById(MODAL_ID);
  const modalInstance = bootstrap.Modal.getInstance(modalElement);
  if (modalInstance) {
    modalInstance.hide();
  }
}

/**
 * ========================================
 * Inisialisasi dan Event Modal
 * ========================================
 */

document.addEventListener("DOMContentLoaded", () => {
  // Muat trips saat halaman siap
  loadTrips();

  // Tambahkan event listener untuk mereset form saat modal ditutup
  const modalElement = document.getElementById(MODAL_ID);
  if (modalElement) {
    modalElement.addEventListener("hidden.bs.modal", function () {
      // Ini akan memastikan form bersih saat modal ditutup
      resetFormAndModalState();
    });
  }

  // Catatan: Tidak ada lagi event listener untuk #gambar karena image preview telah dihapus.
});
