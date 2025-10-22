document.addEventListener("DOMContentLoaded", function () {
  fetch("backend/trip-api.php?action=getTrips")
    .then((response) => {
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      return response.json();
    })
    .then((trips) => {
      const carousel = document.querySelector(
        ".destination-carousel .carousel-track"
      );
      if (!carousel) {
        return;
      }

      carousel.innerHTML = "";

      if (trips.length === 0) {
        carousel.innerHTML = `
          <div class="no-trips">
            <p>🚫 Belum ada jadwal trip.</p>
          </div>
        `;
        return;
      }

      trips.forEach((trip) => {
        // Format tanggal
        const date = new Date(trip.tanggal);
        const formattedDate = date.toLocaleDateString("id-ID", {
          day: "2-digit",
          month: "2-digit",
          year: "numeric",
        });

        // Format harga
        const price = Number(trip.harga);
        const formattedPrice = price.toLocaleString("id-ID");

        const card = document.createElement("div");
        card.className = "destination-card";
        card.style.cursor = "pointer";

        // Path gambar
        let imagePath;
        if (trip.gambar && trip.gambar.trim() !== "") {
          if (trip.gambar.startsWith("img/")) {
            imagePath = trip.gambar;
          } else {
            imagePath = `img/${trip.gambar}`;
          }
        } else {
          imagePath = "img/default-mountain.jpg";
        }

        card.innerHTML = `
          <div class="card-custom">
            <span class="status-badge ${trip.status === "sold" ? "sold" : "available"}">
              ${
                trip.status === "sold"
                  ? '<i class="bi bi-x-circle"></i> Sold'
                  : '<i class="bi bi-check-circle"></i> available'
              }
            </span>
            <div class="card-image-container">
              <img src="${imagePath}" alt="${trip.nama_gunung}" class="card-image">
            </div>
            <div class="card-content">
              <div class="card-row-date-duration">
                <span class="card-date">
                  <i class="bi bi-calendar"></i> ${formattedDate}
                </span>
                <span class="card-duration">
                  <i class="bi bi-clock"></i> ${
                    trip.jenis_trip === "camp" ? trip.durasi || "1 hari" : "1 hari"
                  }
                </span>
              </div>
              <h3 class="card-title">${trip.nama_gunung}</h3>
              <div class="card-type mb-2">
                <span class="badge trip-type-badge bg-light text-dark">
                  <i class="bi bi-flag-fill"></i>
                  ${trip.jenis_trip.charAt(0).toUpperCase() + trip.jenis_trip.slice(1)}
                </span>
              </div>
              <div class="card-rating mb-2">
                <i class="bi bi-star-fill text-warning"></i>
                <span class="rating-number">5</span>
                <span class="rating-reviews">(${Math.floor(Math.random() * 200) + 549}+ ulasan)</span>
              </div>
              <div class="card-location mb-2">
                <i class="bi bi-signpost-2"></i>
                <span>Via ${trip.via_gunung || "paltuding"}</span>
              </div>
              <div class="card-price text-success fw-bold fs-4">
                Rp ${formattedPrice}
              </div>
            </div>
          </div>
        `;

        card.addEventListener("click", function() {
          window.location.href = `trip-detail-user.php?id=${trip.id_trip}`;
        });

        carousel.appendChild(card);
      });

      setupCarouselNavigation();
    })
    .catch((err) => {
      const carousel = document.querySelector(
        ".destination-carousel .carousel-track"
      );
      if (carousel) {
        carousel.innerHTML = `
          <div class="error-message">
            <p>❌ Gagal memuat data trip: ${err.message}</p>
          </div>
        `;
      }
    });
});

function setupCarouselNavigation() {
  const prevBtn = document.querySelector(".destination-carousel .prev");
  const nextBtn = document.querySelector(".destination-carousel .next");
  const track = document.querySelector(".destination-carousel .carousel-track");

  if (prevBtn && nextBtn && track) {
    prevBtn.addEventListener("click", () => {
      track.scrollBy({
        left: -340,
        behavior: "smooth",
      });
    });

    nextBtn.addEventListener("click", () => {
      track.scrollBy({
        left: 340,
        behavior: "smooth",
      });
    });
  }
}

async function autofillForm(){
  let res = await fetch('backend/user-session-api.php');
  let json = await res.json();
  if(json.logged_in && document.querySelector('[name=nama]')){
    document.querySelector('[name=nama]').value = json.user.nama || '';
    document.querySelector('[name=email]').value = json.user.email || '';
    document.querySelector('[name=alamat]').value = json.user.alamat || '';
    document.querySelector('[name=no_wa]').value = json.user.no_wa || '';
    document.querySelector('[name=tanggal_lahir]').value = json.user.tanggal_lahir || '';
  }
}
