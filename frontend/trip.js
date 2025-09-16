let currentEditTripId = null;

// Helper untuk toast sweetalert
function showToast(type, message) {
  Swal.fire({
    toast: true,
    position: 'top-end',
    icon: type,
    title: message,
    showConfirmButton: false,
    timer: 2000,
    timerProgressBar: true,
    customClass: {
      popup: 'colored-toast'
    }
  });
}

async function loadTrips() {
  try {
    const res = await fetch('../backend/trip-api.php?action=getTrips');
    if (!res.ok) throw new Error('Gagal memuat data trip');
    const trips = await res.json();
    displayTrips(trips);
  } catch (err) {
    showToast('error', 'Gagal memuat data trip');
    console.error(err);
  }
}

function displayTrips(trips) {
  const list = document.getElementById('tripList');
  const empty = document.getElementById('emptyState');
  list.innerHTML = '';
  if (!Array.isArray(trips) || trips.length === 0) {
    empty.style.display = '';
    return;
  }
  empty.style.display = 'none';
  trips.forEach(trip => {
    list.innerHTML += `
      <div class="trip-card">
        <span class="trip-status ${trip.status.toLowerCase()}">
          <i class="bi bi-${trip.status.toLowerCase() === 'available' ? 'check-circle' : 'x-circle'}"></i> ${trip.status}
        </span>
        <img src="${trip.gambar ? '../'+trip.gambar : 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=600&q=80'}" alt="${trip.nama_gunung}" class="trip-thumb" />
        <div class="trip-card-body">
          <div class="trip-meta mb-0">
            <span><i class="bi bi-calendar-event"></i> ${trip.tanggal}</span>
            <span><i class="bi bi-clock"></i> ${trip.durasi}</span>
          </div>
          <div class="trip-title">${trip.nama_gunung}</div>
          <div class="trip-type mb-1"><i class="bi bi-flag"></i> ${trip.jenis_trip}</div>
          <div class="trip-via mb-1"><i class="bi bi-signpost-2"></i> Via ${trip.via_gunung}</div>
          <div class="trip-price">Rp ${parseInt(trip.harga).toLocaleString('id-ID')}</div>
          <div class="btn-action-group">
            <button class="btn-action btn-edit" data-id="${trip.id_trip}">Edit</button>
            <button class="btn-action btn-delete" data-id="${trip.id_trip}">Hapus</button>
            <button class="btn-action btn-detail" data-id="${trip.id_trip}">Detail</button>
          </div>
        </div>
      </div>`;
  });

  document.querySelectorAll('.btn-delete').forEach(btn => {
    btn.onclick = async function() {
      const id_trip = this.dataset.id;
      const { isConfirmed } = await Swal.fire({
        title: 'Hapus Trip?',
        text: "Data akan dihapus permanen.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal',
        reverseButtons: true
      });
      if (isConfirmed) {
        try {
          const formData = new FormData();
          formData.append('id_trip', id_trip);
          const res = await fetch('../backend/trip-api.php?action=deleteTrip', {
            method: 'POST',
            body: formData
          });
          const result = await res.json();
          if (result.success) {
            showToast('success', 'Trip berhasil dihapus');
            loadTrips();
          } else {
            showToast('error', 'Gagal menghapus trip');
          }
        } catch (e) {
          showToast('error', 'Kesalahan saat menghapus trip');
          console.error(e);
        }
      }
    };
  });

  document.querySelectorAll('.btn-edit').forEach(btn => {
    btn.onclick = function() {
      const id_trip = this.dataset.id;
      const trip = trips.find(t => t.id_trip == id_trip);
      if (trip) {
        currentEditTripId = trip.id_trip;
        const form = document.getElementById('formTambahTrip');
        form.nama_gunung.value = trip.nama_gunung;
        form.tanggal.value = trip.tanggal;
        form.slot.value = trip.slot;
        form.durasi.value = trip.durasi;
        form.jenis_trip.value = trip.jenis_trip;
        form.harga.value = trip.harga;
        form.via_gunung.value = trip.via_gunung;
        form.status.value = trip.status;
        const preview = document.getElementById('preview');
        preview.src = trip.gambar ? '../'+trip.gambar : '';
        preview.style.display = trip.gambar ? 'block' : 'none';
        const modal = new bootstrap.Modal(document.getElementById('tambahTripModal'));
        modal.show();
      }
    };
  });
}

document.getElementById('formTambahTrip').onsubmit = async function(e) {
  e.preventDefault();
  const form = e.target;
  const formData = new FormData(form);

  // Saat edit, kirim juga id_trip
  if (currentEditTripId) {
    formData.append('id_trip', currentEditTripId);
  }

  let url = '../backend/trip-api.php?action=addTrip';
  if (currentEditTripId) {
    url = '../backend/trip-api.php?action=updateTrip';
  }

  try {
    const res = await fetch(url, {
      method: 'POST',
      body: formData
    });
    const result = await res.json();
    if (result.success) {
      showToast('success', currentEditTripId ? 'Trip berhasil diperbarui' : 'Trip berhasil disimpan');
      currentEditTripId = null;
      form.reset();
      document.getElementById('preview').style.display = 'none';
      bootstrap.Modal.getInstance(document.getElementById('tambahTripModal')).hide();
      loadTrips();
    } else {
      showToast('error', result.msg || 'Gagal menyimpan trip');
    }
  } catch (e) {
    showToast('error', 'Kesalahan saat menyimpan trip');
    console.error(e);
  }
};

loadTrips();
