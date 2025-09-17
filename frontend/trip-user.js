document.addEventListener('DOMContentLoaded', function() {
    fetch('backend/trip-api.php?action=getTrips')
        .then(response => response.json())
        .then(trips => {
            const track = document.querySelector('.carousel-track');
            track.innerHTML = ''; // Hapus card statis (jika ada)

            trips.forEach(trip => {
                const card = document.createElement('div');
                card.className = 'destination-card';
                card.innerHTML = `
                    <img src="${trip.gambar}" alt="${trip.nama_gunung}" />
                    <div class="card-info">
                        <div class="card-title">${trip.nama_gunung}</div>
                        <div class="card-meta">
                            <i class="fas fa-map-marker-alt"></i> ${trip.via_gunung} &nbsp; 
                            <i class="fas fa-star"></i> 5
                        </div>
                        <div class="card-price">Rp. ${Number(trip.harga).toLocaleString('id-ID')}</div>
                    </div>
                `;
                track.appendChild(card);
            });
        })
        .catch(err => {
            alert('Gagal mengambil daftar trip!');
            console.error(err);
        });
});
