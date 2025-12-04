document.addEventListener("DOMContentLoaded", function () {
  fetch(getApiUrl("backend/trip-api.php") + "?action=getTrips")
    .then((response) => {
      if (!response.ok)
        throw new Error(`HTTP error! status: ${response.status}`);
      return response.json();
    })
    .then((trips) => {
      const carousel = document.querySelector(
        ".destination-carousel .carousel-track"
      );
      if (!carousel) return;

      carousel.innerHTML = "";

      const visibleTrips = Array.isArray(trips)
        ? trips.filter((t) => {
            const s = (t.status || "").toString().toLowerCase();
            return s === "available" || s === "sold";
          })
        : [];

      if (visibleTrips.length === 0) {
        carousel.innerHTML = `<div class="no-trips" style="width:100%; text-align:center; padding:20px;"><p>🚫 Belum ada jadwal trip.</p></div>`;
        return;
      }

      visibleTrips.forEach((trip) => {
        // Format Data
        const date = new Date(trip.tanggal);
        const formattedDate = isNaN(date.getTime())
          ? trip.tanggal
          : date.toLocaleDateString("id-ID", {
              day: "2-digit",
              month: "2-digit",
              year: "numeric",
            });
        const formattedPrice = Number(trip.harga || 0).toLocaleString("id-ID");

        let imagePath =
          trip.gambar && trip.gambar.trim() !== ""
            ? trip.gambar.startsWith("img/")
              ? trip.gambar
              : `img/${trip.gambar}`
            : "img/default-mountain.jpg";

        // Logic Status
        const rawStatus = (trip.status || "available").toString().toLowerCase();
        const isSold = rawStatus === "sold";

        // CLASS CSS: 'sold' atau kosong (available default hijau di CSS)
        const statusClass = isSold ? "sold" : "";
        const badgeIcon = isSold ? "bi-x-circle" : "bi-check-circle";
        const badgeText = isSold ? "Sold Out" : "Available";

        // Buat Elemen
        const card = document.createElement("div");
        card.className = "trip-card";
        card.style.cursor = "pointer";

        // HTML KARTU
        // PERHATIKAN: Saya menghapus style="background-color:..." agar CSS Transparan bekerja
        card.innerHTML = `
          <div class="card-image">
            <div class="status-badge ${statusClass}">
               <i class="bi ${badgeIcon}"></i> ${badgeText}
            </div>
            <img src="${imagePath}" alt="${
          trip.nama_gunung
        }" onerror="this.src='img/default-mountain.jpg'">
          </div>

          <div class="card-body">
            
            <div class="card-meta-row">
               <div class="meta-left">
                 <i class="bi bi-calendar-event"></i>
                 <span>${formattedDate}</span>
               </div>
               <div class="meta-right">
                 <i class="bi bi-clock"></i>
                 <span>${trip.durasi || "1 Hari"}</span>
               </div>
            </div>

            <h3 class="card-title">${trip.nama_gunung}</h3>

            <div class="trip-type-badge">
               <i class="bi bi-flag-fill"></i> ${trip.jenis_trip || "Camp"}
            </div>

            <div class="card-rating">
               <i class="bi bi-star-fill"></i> 5
               <span class="rating-count">(${
                 Math.floor(Math.random() * 200) + 500
               }+ ulasan)</span>
            </div>

            <div class="card-location">
               <i class="bi bi-geo-alt-fill"></i> Via ${
                 trip.via_gunung || "Jalur Resmi"
               }
            </div>

            <div class="card-price">
               Rp ${formattedPrice}
            </div>

          </div>
        `;

        card.addEventListener("click", () => {
          window.location.href =
            getPageUrl("user/trip-detail-user.php") + `?id=${trip.id_trip}`;
        });

        carousel.appendChild(card);
      });

      setupCarouselNavigation();
      handleCarouselLayout();
      window.addEventListener("resize", handleCarouselLayout);
    })
    .catch((err) => {
      console.error(err);
      const c = document.querySelector(".destination-carousel .carousel-track");
      if (c)
        c.innerHTML = `<p style="text-align:center; color:red;">Gagal memuat data.</p>`;
    });
});

// Setup Navigasi (Sama seperti sebelumnya)
function setupCarouselNavigation() {
  const prevBtn = document.querySelector(".destination-carousel .prev");
  const nextBtn = document.querySelector(".destination-carousel .next");
  const track = document.querySelector(".destination-carousel .carousel-track");
  if (prevBtn && nextBtn && track) {
    prevBtn.addEventListener("click", () =>
      track.scrollBy({ left: -345, behavior: "smooth" })
    );
    nextBtn.addEventListener("click", () =>
      track.scrollBy({ left: 345, behavior: "smooth" })
    );
  }
}

function handleCarouselLayout() {
  const track = document.querySelector(".carousel-track");
  const indicators = document.querySelector(".scroll-indicators");
  if (!track) return;
  const count = track.querySelectorAll(".trip-card").length;
  if (count === 0) {
    if (indicators) indicators.style.display = "none";
    return;
  }

  const hasOverflow = track.scrollWidth > track.clientWidth;
  const isMobile = window.innerWidth <= 768;

  if (indicators) {
    indicators.style.display = hasOverflow && !isMobile ? "flex" : "none";
  }

  // Rata tengah jika kartu sedikit, rata kiri jika overflow/mobile
  track.style.justifyContent =
    isMobile || hasOverflow ? "flex-start" : "center";
}

async function autofillForm() {
  /* biarkan sama */
}
