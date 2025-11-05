// ========== CONFIG CHECK (CRITICAL!) ==========
if (typeof getApiUrl !== "function") {
  console.error("FATAL ERROR: config.js is not loaded!");
  console.error("Please ensure frontend/config.js is loaded BEFORE trip.js");

  // Fallback untuk debugging
  window.getApiUrl = function (endpoint) {
    console.warn("Using fallback getApiUrl - config.js might not be loaded");
    return "backend/" + endpoint;
  };
}

let currentEditTripId = null;
let tripsData = [];
const FORM_ID = "formTambahTrip";
const MODAL_ID = "tambahTripModal";

// GUNAKAN getApiUrl yang sudah ada di config.js
// JANGAN deklarasikan API_URL lagi karena sudah ada di config.js
const TRIP_API_URL =
  typeof getApiUrl === "function"
    ? getApiUrl("trip-api.php")
    : "backend/trip-api.php";

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
    // Verify getApiUrl is available
    if (typeof getApiUrl !== "function") {
      console.error("getApiUrl function not available");
      showToast("error", "Konfigurasi aplikasi tidak lengkap");
      return;
    }

    const res = await fetch(`${TRIP_API_URL}?action=getTrips`);
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

      // GUNAKAN getAssetsUrl dari config.js
      const imageUrl = trip.gambar
        ? typeof getAssetsUrl === "function"
          ? getAssetsUrl(trip.gambar)
          : trip.gambar
        : "https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=600&q=80";

      const formattedPrice = parseInt(trip.harga, 10).toLocaleString("id-ID");

      const showStatusBadge = rawStatus === "available" || rawStatus === "sold";
      const statusClass = rawStatus;
      const iconClass =
        rawStatus === "available"
          ? "check-circle"
          : rawStatus === "sold"
          ? "x-circle"
          : "flag";

      const stampUrl =
        typeof getAssetsUrl === "function"
          ? getAssetsUrl("assets/completed-stamp.png")
          : "assets/completed-stamp.png";

      const doneOverlay =
        rawStatus === "done"
          ? `<img src="${stampUrl}" alt="Completed Stamp" class="done-stamp" />`
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

/* ===== DELETE TRIP + FILE GAMBAR ===== */
async function handleDelete(id_trip) {
  const { isConfirmed } = await Swal.fire({
    title: "Hapus Trip?",
    html: "<p>Data trip akan dihapus permanen.</p>",
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Ya, Hapus",
    cancelButtonText: "Batal",
    reverseButtons: true,
  });

  if (isConfirmed) {
    Swal.fire({
      title: "Menghapus...",
      html: '<div class="spinner-border spinner-border-sm text-danger" role="status"><span class="visually-hidden">Loading...</span></div><p>Sedang menghapus trip dan file gambar...</p>',
      allowOutsideClick: false,
      allowEscapeKey: false,
      didOpen: async () => {
        Swal.showLoading();

        try {
          const formData = new FormData();
          formData.append("id_trip", id_trip);

          const res = await fetch(`${TRIP_API_URL}?action=deleteTrip`, {
            method: "POST",
            body: formData,
          });

          const result = await res.json();

          if (result.success) {
            showToast("success", "Trip dan file gambar berhasil dihapus");
            loadTrips();

            Swal.fire({
              title: "Berhasil!",
              html:
                "<p>Trip berhasil dihapus</p>" +
                (result.fileDeleteInfo
                  ? '<small style="color: #666;">File: ' +
                    result.fileDeleteInfo.message +
                    "</small>"
                  : ""),
              icon: "success",
              confirmButtonText: "OK",
            });
          } else {
            showToast("error", result.msg || "Gagal menghapus trip");
            Swal.fire({
              title: "Error!",
              text: result.msg || "Gagal menghapus trip",
              icon: "error",
              confirmButtonText: "OK",
            });
          }
        } catch (e) {
          showToast("error", "Kesalahan koneksi saat menghapus trip");
          Swal.fire({
            title: "Error!",
            text: "Kesalahan koneksi: " + e.message,
            icon: "error",
            confirmButtonText: "OK",
          });
          console.error("Kesalahan Hapus:", e);
        }
      },
    });
  }
}

/* ===== EDIT TRIP ===== */
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

/* ===== DETAIL TRIP ===== */
function handleDetail(id_trip) {
  // GUNAKAN getPageUrl dari config.js
  const detailUrl =
    typeof getPageUrl === "function"
      ? getPageUrl(`admin/detailTrip.php?id=${id_trip}`)
      : `detailTrip.php?id=${id_trip}`;
  window.location.href = detailUrl;
}

/* ===== Attach Event Listeners ===== */
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
  const url = `${TRIP_API_URL}?action=${action}`;

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

/* ===== Reset Form & Modal State ===== */
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
  // Verify config loaded
  if (typeof getApiUrl !== "function") {
    console.error("getApiUrl function not available at DOMContentLoaded");
    Swal.fire({
      title: "Error!",
      text: "Konfigurasi aplikasi tidak lengkap. Silakan refresh halaman.",
      icon: "error",
      confirmButtonColor: "#a97c50",
    });
    return;
  }

  loadTrips();
  const modalElement = document.getElementById(MODAL_ID);
  if (modalElement) {
    modalElement.addEventListener("hidden.bs.modal", function () {
      resetFormAndModalState();
    });
  }
});
