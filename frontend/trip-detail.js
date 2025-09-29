function loadTripDetail(idTrip) {
  if (!idTrip) return;
  fetch("../backend/detailTrip-api.php?id_trip=" + idTrip)
    .then((res) => res.json())
    .then((json) => {
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
      }
    });
}

document.getElementById("formDetailTrip")?.addEventListener("submit", function (e) {
  e.preventDefault();
  const form = e.target;
  const formData = new FormData(form);

  fetch("../backend/detailTrip-api.php", {
    method: "POST",
    body: formData,
  })
    .then((res) => res.json())
    .then((json) => {
      if (json.success) {
        Swal.fire({
          icon: "success",
          title: "Trip berhasil disimpan!",
          text: "Data detail trip sudah tersimpan.",
          confirmButtonText: "OK",
          customClass: { popup: 'swal2-border-radius' }
        }).then(() => {
          var modal = bootstrap.Modal.getInstance(document.getElementById("detailTripFormModal"));
          modal.hide();
          location.reload();
        });
      } else {
        Swal.fire({
          icon: "error",
          title: "Gagal!",
          text: "Gagal menyimpan: " + json.message,
          confirmButtonText: "Tutup",
          customClass: { popup: 'swal2-border-radius' }
        });
      }
    });
});