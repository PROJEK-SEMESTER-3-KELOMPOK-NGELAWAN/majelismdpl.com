// frontend/trip-detail.js

async function loadTripDetail(id) {
  if (!id) return;

  // Ambil data trip utama
  const respTrip = await fetch(
    `../backend/trip-api.php?action=getTrip&id=${encodeURIComponent(id)}`
  );
  const resTrip = await respTrip.json();

  if (!resTrip.success || !resTrip.data) {
    alert("Data trip tidak ditemukan!");
    return;
  }

  const trip = resTrip.data;

  // Gambar
  const img = document.getElementById("tripGambar");
  img.src =
    trip.gambar && trip.gambar !== ""
      ? "../" + trip.gambar
      : "images/default.jpg";
  img.alt = trip.nama_gunung || "";

  // Data trip utama
  document.getElementById("tripJudul").textContent = trip.nama_gunung || "-";
  document.getElementById("tripTanggal").textContent = trip.tanggal || "-";
  document.getElementById("tripDurasi").textContent = trip.durasi || "-";
  document.getElementById("tripSlot").textContent = trip.slot || "-";
  document.getElementById("tripJenis").textContent = trip.jenis_trip || "-";
  document.getElementById("tripVia").textContent = trip.via_gunung || "-";
  document.getElementById("tripHarga").textContent =
    "Rp " + Number(trip.harga).toLocaleString("id-ID");

  // Status badge
  const statusEl = document.getElementById("tripStatus");
  statusEl.textContent = (trip.status || "-").toUpperCase();
  statusEl.className =
    "badge-status " + (trip.status === "available" ? "available" : "sold");

  // Ambil detail trip (meeting point dsb)
  const respDetail = await fetch(
    `../backend/detailTrip-api.php?action=getDetail&id_trip=${encodeURIComponent(
      id
    )}`
  );
  const resDetail = await respDetail.json();
  const detail = resDetail.success && resDetail.data ? resDetail.data : {};

  // Update isi detail trip
  document.getElementById("lokasiMeetingPoint").textContent =
    detail.nama_lokasi || "-";
  document.getElementById("alamatMeetingPoint").innerHTML = (
    detail.alamat || "-"
  ).replace(/\n/g, "<br>");
  document.getElementById("waktuKumpul").textContent =
    detail.waktu_kumpul || "-";
  document.getElementById("includeList").innerHTML = (
    detail.include || "-"
  ).replace(/\n/g, "<br>");
  document.getElementById("excludeList").innerHTML = (
    detail.exclude || "-"
  ).replace(/\n/g, "<br>");
  document.getElementById("syaratKetentuanList").innerHTML = (
    detail.syaratKetentuan || "-"
  ).replace(/\n/g, "<br>");

  // Google Maps iframe
  const gmapContainer = document.getElementById("gmapMeetingPoint");
  if (detail.link_map && detail.link_map !== "") {
    const embedUrl = detail.link_map.replace("/maps/", "/maps/embed/");
    gmapContainer.innerHTML = `<iframe src="${embedUrl}" allowfullscreen loading="lazy"></iframe>`;
  } else {
    gmapContainer.innerHTML =
      "<p><em>Belum ada link Google Map Meeting Point</em></p>";
  }
}
