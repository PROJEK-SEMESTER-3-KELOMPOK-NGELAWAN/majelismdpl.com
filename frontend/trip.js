let currentEditTripId = null;
let tripsData = [];
const FORM_ID = "formTambahTrip";
const MODAL_ID = "tambahTripModal";
const API_URL = "../backend/trip-api.php";

/* ===== Util: Toast ===== */
function showToast(type, message) {
  Swal.fire({
    toast: true,
    position: "top-end",
    icon: type,
    title: message,
    showConfirmButton: false,
    timer: 2000,
    timerProgressBar: true,
    customClass: { popup: "colored-toast" },
  });
}

/* ===== Load Trips ===== */
async function loadTrips() {
  try {
    const res = await fetch(`${API_URL}?action=getTrips`);
    if (!res.ok) throw new Error(`HTTP error! status: ${res.status}`);
    const trips = await res.json();
    tripsData = trips;
    displayTrips(trips);
  } catch (err) {
    showToast("error", "Gagal memuat data trip");
    console.error("Kesalahan saat memuat trip:", err);
  }
}

/* ===== Render Trip Cards (Admin) ===== */
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
      const rawStatus = (trip.status || "available").toString().toLowerCase();
      const imageUrl = trip.gambar
        ? `../${trip.gambar}`
        : "https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=600&q=80";
      const formattedPrice = parseInt(trip.harga, 10).toLocaleString("id-ID");

      // Badge status text untuk available/sold
      const showStatusBadge = rawStatus === "available" || rawStatus === "sold";
      const statusClass = rawStatus; // available | sold | done
      const iconClass =
        rawStatus === "available"
          ? "check-circle"
          : rawStatus === "sold"
          ? "x-circle"
          : "flag";

      // Jika done, gunakan overlay gambar stempel, jangan tampilkan teks status.
      const doneOverlay =
        rawStatus === "done"
          ? `<img src="../assets/completed-stamp.png" alt="Completed Stamp" class="done-stamp" />`
          : "";

      return `
        <div class="trip-card">
          ${
            showStatusBadge
              ? `<span class="trip-status ${statusClass}">
                   <i class="bi bi-${iconClass}"></i> ${rawStatus}
                 </span>`
              : ""
          }
          <div class="trip-thumb-wrapper">
            ${doneOverlay}
            <img src="${imageUrl}" alt="${
        trip.nama_gunung
      }" class="trip-thumb" />
          </div>
          <div class="trip-card-body">
            <div class="trip-meta">
              <span><i class="bi bi-calendar-event"></i> ${trip.tanggal}</span>
              <span><i class="bi bi-clock"></i> ${trip.durasi}</span>
            </div>
            <div class="trip-title">${trip.nama_gunung}</div>
            <div class="trip-type mb-1"><i class="bi bi-flag"></i> ${
              trip.jenis_trip
            }</div>
            <div class="trip-rating">
              <i class="bi bi-star-fill"></i>
              <span class="rating-number">5</span>
              <span class="sub">(${ulasanCount}+ ulasan)</span>
            </div>
            <div class="trip-via"><i class="bi bi-signpost-2"></i> Via ${
              trip.via_gunung
            }</div>
            <div class="trip-price">Rp ${formattedPrice}</div>
            <div class="btn-action-group">
              <button class="btn-action btn-edit" data-id="${
                trip.id_trip
              }" title="Edit Trip"><i class="bi bi-pencil-square"></i></button>
              <button class="btn-action btn-delete" data-id="${
                trip.id_trip
              }" title="Hapus Trip"><i class="bi bi-trash"></i></button>
              <button class="btn-action btn-detail" data-id="${
                trip.id_trip
              }" title="Lihat Detail"><i class="bi bi-arrow-right"></i></button>
            </div>
          </div>
        </div>
      `;
    })
    .join("");

  tripListContainer.innerHTML = html;
  injectStampStylesOnce();
  attachEventListeners();
}

/* ===== Inject CSS untuk stempel (sekali saja) ===== */
function injectStampStylesOnce() {
  if (document.getElementById("trip-admin-stamp-style")) return;
  const style = document.createElement("style");
  style.id = "trip-admin-stamp-style";
  style.innerHTML = `
    .trip-thumb-wrapper{
      position: relative;
      width: 100%;
      height: 180px;
    }
    .trip-thumb-wrapper .trip-thumb{
      width: 100%;
      height: 100%;
      object-fit: cover;
      border-radius: 18px 18px 0 0;
      display: block;
    }
    .done-stamp{
      position: absolute;
      z-index: 3;
      top: 52%;
      left: 50%;
      transform: translate(-50%, -50%) rotate(-15deg);
      width: min(72%, 340px);
      opacity: .9;
      pointer-events: none;
      filter: drop-shadow(0 2px 6px rgba(0,0,0,.25));
    }
    /* Responsif */
    @media (max-width: 768px){
      .trip-thumb-wrapper{ height: 190px; }
      .done-stamp{ width: min(78%, 320px); top: 54%; }
    }
    @media (min-width: 769px) and (max-width: 1200px){
      .trip-thumb-wrapper{ height: 185px; }
      .done-stamp{ width: min(74%, 330px); }
    }
  `;
  document.head.appendChild(style);
}

/* ===== Actions ===== */
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

function handleEdit(id_trip) {
  const trip = tripsData.find((t) => t.id_trip == id_trip);
  if (trip) {
    currentEditTripId = trip.id_trip;
    const form = document.getElementById(FORM_ID);

    document.getElementById("modalTitleText").textContent = "Edit Trip";
    document.getElementById("tripIdInput").value = trip.id_trip;
    document.getElementById("actionType").value = "updateTrip";

    form.nama_gunung.value = trip.nama_gunung || "";
    form.tanggal.value = trip.tanggal || "";
    form.slot.value = trip.slot || "";
    form.durasi.value = trip.durasi || "";
    form.jenis_trip.value = trip.jenis_trip || "";
    form.harga.value = trip.harga || "";
    form.via_gunung.value = trip.via_gunung || "";
    form.status.value = (trip.status || "available").toString().toLowerCase();

    const modal = new bootstrap.Modal(document.getElementById(MODAL_ID));
    modal.show();
  } else {
    showToast("warning", "Trip tidak ditemukan!");
  }
}

function handleDetail(id_trip) {
  window.location.href = `detailTrip.php?id=${id_trip}`;
}

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

/* ===== Submit Form Add/Update ===== */
document.getElementById(FORM_ID).onsubmit = async function (e) {
  e.preventDefault();
  const form = e.target;
  const formData = new FormData(form);
  const action = document.getElementById("actionType").value;
  const url = `${API_URL}?action=${action}`;

  try {
    const res = await fetch(url, { method: "POST", body: formData });
    const result = await res.json();
    if (result.success) {
      const message =
        action === "updateTrip"
          ? "Trip berhasil diperbarui"
          : "Trip berhasil disimpan";
      showToast("success", message);
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

function resetFormAndModalState() {
  const form = document.getElementById(FORM_ID);
  currentEditTripId = null;
  form.reset();
  document.getElementById("actionType").value = "addTrip";
  document.getElementById("tripIdInput").value = "";
  document.getElementById("modalTitleText").textContent = "Tambah Trip Baru";
  const modalElement = document.getElementById(MODAL_ID);
  const modalInstance = bootstrap.Modal.getInstance(modalElement);
  if (modalInstance) {
    modalInstance.hide();
  }
}

/* ===== Init ===== */
document.addEventListener("DOMContentLoaded", () => {
  loadTrips();
  const modalElement = document.getElementById(MODAL_ID);
  if (modalElement) {
    modalElement.addEventListener("hidden.bs.modal", function () {
      resetFormAndModalState();
    });
  }
});
