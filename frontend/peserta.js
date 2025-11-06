// ========== CONFIG CHECK (CRITICAL!) ==========
if (typeof getApiUrl !== "function") {
  console.error("FATAL ERROR: config.js is not loaded!");
  console.error("Please ensure frontend/config.js is loaded BEFORE peserta.js");


  // Fallback untuk debugging
  window.getApiUrl = function (endpoint) {
    console.warn("Using fallback getApiUrl - config.js might not be loaded");
    return "backend/" + endpoint;
  };
}


/**
 * ============================================
 * FILE: frontend/peserta.js
 * FUNGSI: Handle UI Peserta management
 * UPDATED: Config.js integration + error handling + Nomor column styling FIXED
 * ============================================
 */


class PesertaAPI {
  constructor() {
    // GUNAKAN getApiUrl yang sudah ada di config.js
    if (typeof getApiUrl !== "function") {
      console.error("getApiUrl function not available in constructor");
      this.baseURL = "backend/peserta-api.php";
    } else {
      this.baseURL = getApiUrl("peserta-api.php");
    }


    this.participants = [];
    this.currentEditParticipantId = null;
    this.currentTripFilterId = "";
    this.currentSearchTerm = "";
    this.init();
  }


  async init() {
    await this.loadTripsForFilter();
    await this.loadParticipants();
    this.setupEventListeners();
    this.setupPrintHandler();
  }


  setupPrintHandler() {
    const btn = document.getElementById("printPdfBtn");
    const filterGunung = document.getElementById("filterGunung");
    const searchInput = document.getElementById("searchInput");
    if (!btn || !filterGunung) return;
    btn.addEventListener("click", () => {
      const idTrip = filterGunung.value || "";
      const search = ((searchInput && searchInput.value) || "").trim();
      const params = new URLSearchParams();
      params.set("action", "print_pdf");
      if (idTrip) params.set("id_trip", idTrip);
      if (search) params.set("search", search);
      window.open(`${this.baseURL}?${params.toString()}`, "_blank");
    });
  }


  async loadTripsForFilter() {
    const filterGunung = document.getElementById("filterGunung");
    if (!filterGunung) return;
    try {
      const response = await fetch(`${this.baseURL}?action=trips`);
      const result = await response.json().catch(() => null);
      if (result && result.status === 200) {
        filterGunung.innerHTML =
          '<option value="">Semua Gunung / Trip</option>';
        (result.data || []).forEach((trip) => {
          const option = document.createElement("option");
          option.value = this.escapeHtml(trip.id_trip);
          option.textContent = this.escapeHtml(trip.nama_gunung);
          filterGunung.appendChild(option);
        });
      } else {
        this.showError(
          "Gagal memuat daftar trip" +
            (result && result.message ? ": " + result.message : "")
        );
      }
    } catch (e) {
      this.showError("Terjadi kesalahan saat memuat daftar trip");
    }
  }


  async loadParticipants() {
    let url = `${this.baseURL}?action=all`;
    if (this.currentTripFilterId) url += `&id_trip=${this.currentTripFilterId}`;
    if (this.currentSearchTerm.trim())
      url += `&search=${encodeURIComponent(this.currentSearchTerm.trim())}`;


    const tableBody = document.getElementById("participantsTableBody");
    if (tableBody) {
      tableBody.innerHTML = `<tr><td colspan="15" class="text-center opacity-50"><i class="bi bi-arrow-clockwise spinner-border spinner-border-sm me-2"></i>Memuat data peserta...</td></tr>`;
    }


    try {
      const response = await fetch(url);
      const result = await response.json().catch(() => null);
      if (result && result.status === 200) {
        this.participants = result.data || [];
        this.renderParticipants();
      } else {
        const msg =
          result && result.message ? result.message : "Respons tidak valid";
        this.showError("Gagal memuat data peserta: " + msg);
        this.renderErrorState("Gagal memuat data");
      }
    } catch (error) {
      this.showError("Terjadi kesalahan saat memuat data peserta");
      this.renderErrorState("Koneksi gagal");
    }
  }


  renderErrorState(message) {
    const tableBody = document.getElementById("participantsTableBody");
    if (tableBody) {
      tableBody.innerHTML = `<tr><td colspan="15" class="text-center opacity-50 text-danger"><i class="bi bi-exclamation-triangle me-2"></i>${message}</td></tr>`;
    }
  }


  getImagePath(imagePath) {
    if (!imagePath) return "";
    // Jika sudah URL lengkap
    if (imagePath.startsWith("http://") || imagePath.startsWith("https://"))
      return imagePath;
    if (imagePath.startsWith("../")) return imagePath;
    // Format: 'ktp_1732023400_5678.jpg' atau 'uploads/ktp/filename.jpg'
    if (imagePath.startsWith("uploads/")) return "../" + imagePath;
    // Asumsikan hanya filename
    return "../uploads/ktp/" + imagePath;
  }


  renderParticipants() {
    const tableBody = document.getElementById("participantsTableBody");
    const participants = this.participants;
    if (!tableBody) return;


    if (!participants.length) {
      tableBody.innerHTML = `<tr><td colspan="15" class="text-center opacity-50">Tidak ada peserta yang ditemukan.</td></tr>`;
      return;
    }


    tableBody.innerHTML = participants
      .map((p, index) => {
        // Nomor urut
        const nomorUrut = index + 1;
        
        return `
      <tr>
        <td class="text-center col-number">${nomorUrut}</td>
        <td>${this.escapeHtml(p.id_participant || "")}</td>
        <td>${this.escapeHtml(p.nama || "")}</td>
        <td>${this.escapeHtml(p.email || "")}</td>
        <td>${this.escapeHtml(p.no_wa || "")}</td>
        <td class="hide-col">${this.escapeHtml(p.alamat || "")}</td>
        <td class="hide-col">${this.escapeHtml(p.riwayat_penyakit || "")}</td>
        <td class="hide-col">${this.escapeHtml(p.no_wa_darurat || "")}</td>
        <td class="hide-col">${this.escapeHtml(p.tanggal_lahir || "")}</td>
        <td class="hide-col">${this.escapeHtml(p.tempat_lahir || "")}</td>
        <td class="hide-col">${this.escapeHtml(p.nik || "")}</td>
        <td>${
          p.foto_ktp
            ? `<img src="${this.getImagePath(
                p.foto_ktp
              )}" alt="KTP" class="participant-photo"
                  data-participant-id="${p.id_participant}"
                  data-participant-name="${this.escapeHtml(p.nama || "")}"
                  data-participant-nik="${this.escapeHtml(p.nik || "")}"
                  title="Klik untuk melihat gambar lebih besar"
                  onerror="this.style.display='none'; this.nextElementSibling.style.display='inline';" />
                  <span class="opacity-50" style="display:none;">Gambar error</span>`
            : '<span class="opacity-50">Tidak ada</span>'
        }</td>
        <td>${this.escapeHtml(p.id_booking || "Belum booking")}
          ${
            p.nama_gunung
              ? `<br><small class="text-brown">(${this.escapeHtml(
                  p.nama_gunung
                )})</small>`
              : ""
          }</td>
        <td>
          <div class="btn-action-group">
            <button class="btn-edit" onclick="pesertaAPI.showEditModal(${
              p.id_participant
            })"><i class="bi bi-pencil-square"></i></button>
            <button class="btn-delete" onclick="pesertaAPI.deleteParticipant(${
              p.id_participant
            })"><i class="bi bi-trash"></i></button>
          </div>
        </td>
      </tr>
    `;
      })
      .join("");


    this.setupImagePreview();
  }


  setupImagePreview() {
    document.querySelectorAll(".participant-photo").forEach((img) => {
      img.addEventListener("click", (e) => {
        const participantName = e.target.dataset.participantName;
        const participantNik = e.target.dataset.participantNik;
        const participantId = e.target.dataset.participantId;
        const imagePath = e.target.src;
        this.showImagePreview(
          imagePath,
          participantName,
          participantNik,
          participantId
        );
      });
    });
  }


  showImagePreview(imagePath, participantName, participantNik, participantId) {
    const modal = document.getElementById("previewImageModal");
    const previewImage = document.getElementById("previewImageFull");
    const loadingSpinner = document.getElementById("imageLoadingSpinner");
    const errorMessage = document.getElementById("imageErrorMessage");
    const nameElement = document.getElementById("previewParticipantName");
    const nikElement = document.getElementById("previewParticipantNIK");
    const downloadBtn = document.getElementById("previewDownloadBtn");
    if (!modal || !previewImage) return;


    previewImage.style.display = "none";
    loadingSpinner.style.display = "block";
    errorMessage.style.display = "none";
    nameElement.textContent = participantName || "Tidak diketahui";
    nikElement.textContent = participantNik || "Tidak diketahui";
    downloadBtn.href = imagePath;
    downloadBtn.download = `KTP_${participantName || participantId}_${
      participantNik || "unknown"
    }.jpg`;


    previewImage.onload = () => {
      loadingSpinner.style.display = "none";
      previewImage.style.display = "block";
    };
    previewImage.onerror = () => {
      loadingSpinner.style.display = "none";
      errorMessage.style.display = "block";
    };
    previewImage.src = imagePath;


    const bootstrapModal = new bootstrap.Modal(modal);
    bootstrapModal.show();


    document.addEventListener("keydown", (e) => {
      const m = document.getElementById("previewImageModal");
      if (m && m.classList.contains("show") && e.key === "Escape") {
        const inst = bootstrap.Modal.getInstance(m);
        if (inst) inst.hide();
      }
    });
  }


  setupEventListeners() {
    const searchInput = document.getElementById("searchInput");
    const filterGunung = document.getElementById("filterGunung");
    if (searchInput)
      searchInput.addEventListener("input", (e) => {
        this.currentSearchTerm = e.target.value;
        this.loadParticipants();
      });
    if (filterGunung)
      filterGunung.addEventListener("change", (e) => {
        this.currentTripFilterId = e.target.value;
        this.loadParticipants();
      });
    this.setupEditFormHandler();
    this.setupFilePreview();
  }


  setupEditFormHandler() {
    const form = document.getElementById("formEditPeserta");
    if (!form) return;
    form.addEventListener("submit", async (e) => {
      e.preventDefault();
      await this.handleEditSubmit(e);
    });
  }


  setupFilePreview() {
    const fileInput = document.getElementById("edit_foto_ktp");
    const preview = document.getElementById("edit_preview_ktp");
    if (!fileInput || !preview) return;
    fileInput.addEventListener("change", (e) => {
      const file = e.target.files[0];
      if (file) {
        if (!file.type.startsWith("image/")) {
          this.showError("File harus berupa gambar");
          fileInput.value = "";
          return;
        }
        if (file.size > 5 * 1024 * 1024) {
          this.showError("Ukuran file maksimal 5MB");
          fileInput.value = "";
          return;
        }
        const reader = new FileReader();
        reader.onload = (ev) => {
          preview.src = ev.target.result;
          preview.style.display = "block";
        };
        reader.readAsDataURL(file);
      } else {
        preview.style.display = "none";
      }
    });
  }


  async getParticipantDetail(id) {
    try {
      const res = await fetch(`${this.baseURL}?action=detail&id=${id}`);
      const result = await res.json().catch(() => null);
      if (result && result.status === 200) return result.data;
      this.showError(
        "Gagal memuat detail peserta" +
          (result && result.message ? ": " + result.message : "")
      );
      return null;
    } catch (e) {
      this.showError("Terjadi kesalahan saat memuat detail peserta");
      return null;
    }
  }


  async updateParticipant(id, formData) {
    try {
      const response = await fetch(`${this.baseURL}?action=update&id=${id}`, {
        method: "POST",
        body: formData,
      });
      const result = await response.json().catch(() => null);
      if (result && result.status === 200) {
        await this.loadParticipants();
        this.showSuccess("Peserta berhasil diupdate");
        return true;
      } else {
        const msg =
          result && result.message ? result.message : "Respons tidak valid";
        this.showError("Gagal update peserta: " + msg);
        return false;
      }
    } catch (error) {
      this.showError("Terjadi kesalahan saat update peserta");
      return false;
    }
  }


  async deleteParticipant(id) {
    const confirm = await Swal.fire({
      title: "Konfirmasi Hapus",
      text: "Apakah Anda yakin ingin menghapus peserta ini?",
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#dc3545",
      cancelButtonColor: "#6c757d",
      confirmButtonText: "Ya, Hapus",
      cancelButtonText: "Batal",
    });
    if (!confirm.isConfirmed) return;


    try {
      const response = await fetch(`${this.baseURL}?action=delete&id=${id}`, {
        method: "DELETE",
      });
      const result = await response.json().catch(() => null);
      if (result && result.status === 200) {
        await this.loadParticipants();
        this.showSuccess("Peserta berhasil dihapus");
      } else {
        const msg =
          result && result.message ? result.message : "Respons tidak valid";
        this.showError("Gagal menghapus peserta: " + msg);
      }
    } catch (error) {
      this.showError("Terjadi kesalahan saat menghapus peserta");
    }
  }


  async showEditModal(id) {
    const participant = await this.getParticipantDetail(id);
    if (!participant) return;


    this.currentEditParticipantId = participant.id_participant;


    const form = document.getElementById("formEditPeserta");
    if (!form) {
      this.showError("Form edit tidak ditemukan");
      return;
    }


    form.id_participant.value = participant.id_participant;
    form.nama.value = participant.nama || "";
    form.email.value = participant.email || "";
    form.no_wa.value = participant.no_wa || "";
    form.alamat.value = participant.alamat || "";
    form.riwayat_penyakit.value = participant.riwayat_penyakit || "";
    form.no_wa_darurat.value = participant.no_wa_darurat || "";
    form.tanggal_lahir.value = participant.tanggal_lahir || "";
    form.tempat_lahir.value = participant.tempat_lahir || "";
    form.nik.value = participant.nik || "";


    const preview = document.getElementById("edit_preview_ktp");
    if (preview) {
      if (participant.foto_ktp) {
        const imagePath = this.getImagePath(participant.foto_ktp);
        preview.src = imagePath;
        preview.style.display = "block";
        preview.onerror = () => {
          preview.style.display = "none";
        };
      } else {
        preview.style.display = "none";
      }
    }


    const fileInput = document.getElementById("edit_foto_ktp");
    if (fileInput) fileInput.value = "";


    const modal = new bootstrap.Modal(
      document.getElementById("editPesertaModal")
    );
    modal.show();
  }


  async handleEditSubmit(event) {
    event.preventDefault();
    if (!this.currentEditParticipantId) {
      this.showError("ID peserta tidak ditemukan");
      return;
    }


    const submitBtn = event.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Menyimpan...';
    submitBtn.disabled = true;


    const form = event.target;
    const formData = new FormData(form);
    formData.append("id_participant", this.currentEditParticipantId);


    try {
      const success = await this.updateParticipant(
        this.currentEditParticipantId,
        formData
      );
      if (success) {
        const modal = bootstrap.Modal.getInstance(
          document.getElementById("editPesertaModal")
        );
        if (modal) modal.hide();
        this.currentEditParticipantId = null;
      }
    } catch (error) {
      this.showError("Terjadi kesalahan saat menyimpan data");
    } finally {
      submitBtn.innerHTML = originalText;
      submitBtn.disabled = false;
    }
  }


  escapeHtml(text) {
    if (text === null || text === undefined) return "";
    const div = document.createElement("div");
    div.textContent = text.toString();
    return div.innerHTML;
  }


  showSuccess(message) {
    if (typeof Swal !== "undefined") {
      Swal.fire({
        title: "Berhasil!",
        text: message,
        icon: "success",
        timer: 3000,
        showConfirmButton: false,
      });
    } else {
      alert("Success: " + message);
    }
  }


  showError(message) {
    if (typeof Swal !== "undefined") {
      Swal.fire({
        title: "Error!",
        text: message,
        icon: "error",
        confirmButtonText: "OK",
      });
    } else {
      alert("Error: " + message);
    }
  }


  async refresh() {
    await this.loadParticipants();
  }


  debugParticipants() {
    console.log("=== DEBUG PARTICIPANTS ===");
    this.participants.forEach((p, index) => {
      console.log(`Participant ${index + 1}:`, {
        nomor: index + 1,
        id: p.id_participant,
        nama: p.nama,
        foto_ktp: p.foto_ktp,
        foto_path: this.getImagePath(p.foto_ktp),
      });
    });
    console.log("=== END DEBUG ===");
  }
}


// ========== INITIALIZE ==========
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


  window.pesertaAPI = new PesertaAPI();
});
