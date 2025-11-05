// ========== CONFIG CHECK ==========
if (typeof getApiUrl !== "function") {
  console.error("FATAL ERROR: config.js is not loaded!");
  console.error(
    "Please ensure frontend/config.js is loaded BEFORE trip-detail.js"
  );
  alert("Konfigurasi aplikasi tidak lengkap. Silakan refresh halaman.");
  window.location.reload();
}

// ========== LOAD DETAIL TRIP DATA ==========
function loadTripDetail(idTrip) {
  if (!idTrip) {
    console.warn("No idTrip provided to loadTripDetail");
    return;
  }

  const apiUrl = getApiUrl("detailTrip-api.php") + "?id_trip=" + idTrip;
  console.log("Loading trip detail from:", apiUrl);

  fetch(apiUrl)
    .then((res) => {
      if (!res.ok) {
        throw new Error(`HTTP error! status: ${res.status}`);
      }
      return res.json();
    })
    .then((json) => {
      console.log("Trip detail loaded:", json);

      if (json.success) {
        const data = json.data;
        document.getElementById("nama_lokasi").value = data.nama_lokasi || "";
        document.getElementById("alamat").value = data.alamat || "";
        document.getElementById("waktu_kumpul").value = data.waktu_kumpul || "";
        document.getElementById("link_map").value = data.link_map || "";
        document.getElementById("include").value = data.include || "";
        document.getElementById("exclude").value = data.exclude || "";
        document.getElementById("syaratKetentuan").value =
          data.syaratKetentuan || "";
      } else {
        console.error("Failed to load trip detail:", json.message);
      }
    })
    .catch((err) => {
      console.error("Error loading trip detail:", err);
    });
}

// ========== SUBMIT DETAIL TRIP ==========
function submitDetailTrip(formElement) {
  // Verify config loaded
  if (typeof getApiUrl !== "function") {
    console.error("getApiUrl function not available");
    Swal.fire({
      icon: "error",
      title: "Error!",
      text: "Konfigurasi aplikasi tidak lengkap. Silakan refresh halaman.",
      confirmButtonText: "OK",
      customClass: { popup: "swal2-border-radius" },
    });
    return;
  }

  const form = formElement;
  const formData = new FormData(form);

  const apiUrl = getApiUrl("detailTrip-api.php");
  console.log("Submitting form to:", apiUrl);

  fetch(apiUrl, {
    method: "POST",
    body: formData,
  })
    .then((res) => {
      if (!res.ok) {
        throw new Error(`HTTP error! status: ${res.status}`);
      }
      return res.json();
    })
    .then((json) => {
      console.log("Submit response:", json);

      if (json.success) {
        Swal.fire({
          icon: "success",
          title: "Berhasil!",
          text: "Detail trip berhasil disimpan.",
          confirmButtonText: "OK",
          customClass: { popup: "swal2-border-radius" },
        }).then(() => {
          try {
            const modal = bootstrap.Modal.getInstance(
              document.getElementById("detailTripFormModal")
            );
            if (modal) {
              modal.hide();
            }
          } catch (e) {
            console.warn("Modal close error:", e);
          }
          location.reload();
        });
      } else {
        Swal.fire({
          icon: "error",
          title: "Gagal!",
          text: json.message || "Gagal menyimpan detail trip.",
          confirmButtonText: "Tutup",
          customClass: { popup: "swal2-border-radius" },
        });
      }
    })
    .catch((err) => {
      console.error("Submit error:", err);
      Swal.fire({
        icon: "error",
        title: "Error!",
        text: err.message || "Terjadi kesalahan saat menyimpan.",
        confirmButtonText: "Tutup",
        customClass: { popup: "swal2-border-radius" },
      });
    });
}

// ========== FORM EVENT LISTENER ==========
document.addEventListener("DOMContentLoaded", function () {
  const formDetailTrip = document.getElementById("formDetailTrip");

  if (formDetailTrip) {
    formDetailTrip.addEventListener("submit", function (e) {
      e.preventDefault();

      // Verify config loaded
      if (typeof getApiUrl !== "function") {
        console.error("getApiUrl function not available at form submit");
        Swal.fire({
          icon: "error",
          title: "Error!",
          text: "Konfigurasi aplikasi tidak lengkap.",
          confirmButtonText: "OK",
        });
        return;
      }

      submitDetailTrip(this);
    });
  } else {
    console.warn("formDetailTrip element not found");
  }
});
