class PesertaAPI {
  constructor() {
    this.baseURL = "../backend/peserta-api.php";
    this.participants = [];
    this.currentEditParticipantId = null;
    this.init();
  }

  async init() {
    await this.loadParticipants();
    this.setupEventListeners();
  }

  // Load all participants from API
  async loadParticipants() {
    try {
      const response = await fetch(`${this.baseURL}?action=all`);
      const result = await response.json();

        
      if (result.status === 200) {
        this.participants = result.data || [];
        this.renderParticipants();
      } else {
        this.showError("Gagal memuat data peserta: " + result.message);
      }
    } catch (error) {
      console.error("Error loading participants:", error);
      this.showError(
        "Terjadi kesalahan saat memuat data peserta: " + error.message
      );
    }
  }

  // BARU: Method untuk handle path gambar dengan benar
  getImagePath(imagePath) {
    if (!imagePath) return "";

    // Jika sudah http/https, return langsung
    if (imagePath.startsWith("http://") || imagePath.startsWith("https://")) {
      return imagePath;
    }

    // Jika sudah dimulai dengan '../', return langsung
    if (imagePath.startsWith("../")) {
      return imagePath;
    }

    // Jika dimulai dengan 'uploads/', tambahkan '../'
    if (imagePath.startsWith("uploads/")) {
      return "../" + imagePath;
    }

    // Jika hanya nama file, asumsikan di uploads/ktp/
    return "../uploads/ktp/" + imagePath;
  }

  // Render participants table dengan path handling
  renderParticipants(participantsToRender = null) {
    const tableBody = document.getElementById("participantsTableBody");
    const participants = participantsToRender || this.participants;

    if (!tableBody) {
      console.error("Table body element not found");
      return;
    }

    if (participants.length === 0) {
      tableBody.innerHTML = `
            <tr>
                <td colspan="13" class="text-center opacity-50">Belum ada peserta</td>
            </tr>
        `;
      return;
    }

    tableBody.innerHTML = participants
      .map(
        (p) => `
        <tr>
            <td>${this.escapeHtml(p.id_participant || "")}</td>
            <td>${this.escapeHtml(p.nama || "")}</td>
            <td>${this.escapeHtml(p.email || "")}</td>
            <td>${this.escapeHtml(p.no_wa || "")}</td>
            <td>${this.escapeHtml(p.alamat || "")}</td>
            <td>${this.escapeHtml(p.riwayat_penyakit || "")}</td>
            <td>${this.escapeHtml(p.no_wa_darurat || "")}</td>
            <td>${this.escapeHtml(p.tanggal_lahir || "")}</td>
            <td>${this.escapeHtml(p.tempat_lahir || "")}</td>
            <td>${this.escapeHtml(p.nik || "")}</td>
            <td>
                ${
                  p.foto_ktp
                    ? `<img src="${this.getImagePath(
                        p.foto_ktp
                      )}" alt="KTP" class="participant-photo" 
                         data-participant-id="${p.id_participant}"
                         data-participant-name="${this.escapeHtml(
                           p.nama || ""
                         )}"
                         data-participant-nik="${this.escapeHtml(p.nik || "")}"
                         title="Klik untuk melihat gambar lebih besar"
                         onerror="this.style.display='none'; this.nextElementSibling.style.display='inline';" />
                     <span class="opacity-50" style="display:none;">Gambar error</span>`
                    : '<span class="opacity-50">Tidak ada</span>'
                }
            </td>
            <td>${this.escapeHtml(p.id_booking || "Belum booking")}</td>
            <td>
                <div class="btn-action-group">
                    <button class="btn-edit" data-id="${
                      p.id_participant
                    }">Edit</button>
                    <button class="btn-delete" onclick="pesertaAPI.deleteParticipant(${
                      p.id_participant
                    })">Hapus</button>
                </div>
            </td>
        </tr>
    `
      )
      .join("");

    this.setupEditButtons();
    this.setupImagePreview(); // BARU: Setup event handler untuk preview gambar
  }

  // BARU: Setup event handlers untuk preview gambar
  setupImagePreview() {
    document.querySelectorAll(".participant-photo").forEach((img) => {
      img.addEventListener("click", (e) => {
        const participantId = e.target.dataset.participantId;
        const participantName = e.target.dataset.participantName;
        const participantNik = e.target.dataset.participantNik;
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

  // BARU: Method untuk menampilkan preview gambar dalam modal
  showImagePreview(imagePath, participantName, participantNik, participantId) {
    // Ambil elemen modal dan komponennya
    const modal = document.getElementById("previewImageModal");
    const previewImage = document.getElementById("previewImageFull");
    const loadingSpinner = document.getElementById("imageLoadingSpinner");
    const errorMessage = document.getElementById("imageErrorMessage");
    const nameElement = document.getElementById("previewParticipantName");
    const nikElement = document.getElementById("previewParticipantNIK");
    const downloadBtn = document.getElementById("previewDownloadBtn");

    if (!modal || !previewImage) {
      console.error("Preview modal elements not found");
      return;
    }

    // Reset state
    previewImage.style.display = "none";
    loadingSpinner.style.display = "block";
    errorMessage.style.display = "none";

    // Set participant info
    nameElement.textContent = participantName || "Tidak diketahui";
    nikElement.textContent = participantNik || "Tidak diketahui";

    // Set download link
    downloadBtn.href = imagePath;
    downloadBtn.download = `KTP_${participantName || participantId}_${
      participantNik || "unknown"
    }.jpg`;

    // Load image
    previewImage.onload = () => {
      loadingSpinner.style.display = "none";
      previewImage.style.display = "block";
      console.log("Image loaded successfully:", imagePath);
    };

    previewImage.onerror = () => {
      loadingSpinner.style.display = "none";
      errorMessage.style.display = "block";
      console.error("Failed to load image:", imagePath);
    };

    // Set image source
    previewImage.src = imagePath;

    // Show modal
    const bootstrapModal = new bootstrap.Modal(modal);
    bootstrapModal.show();

    console.log("Opening image preview for:", participantName, participantNik);
    
    // BARU: Keyboard navigation untuk modal
    document.addEventListener("keydown", (e) => {
      const modal = document.getElementById("previewImageModal");
      if (modal && modal.classList.contains("show")) {
        if (e.key === "Escape") {
          this.closeImagePreview();
        }
      }
    });
  }

  // BARU: Method untuk close image preview (opsional)
  closeImagePreview() {
    const modal = bootstrap.Modal.getInstance(
      document.getElementById("previewImageModal")
    );
    if (modal) {
      modal.hide();
    }
  }

  // Setup event handlers untuk tombol edit
  setupEditButtons() {
    document.querySelectorAll(".btn-edit").forEach((btn) => {
      btn.onclick = () => {
        const id_participant = btn.dataset.id;
        const participant = this.participants.find(
          (p) => p.id_participant == id_participant
        );
        if (participant) {
          this.showEditModal(participant);
        }
      };
    });
  }

  // Setup event listeners
  setupEventListeners() {
    const searchInput = document.getElementById("searchInput");
    if (searchInput) {
      searchInput.addEventListener("input", (e) => {
        this.searchParticipants(e.target.value);
      });
    }

    this.setupEditFormHandler();
    this.setupFilePreview();
    // Image preview akan di-setup di renderParticipants() setiap kali render
  }

  // Setup form edit handler
  setupEditFormHandler() {
    const form = document.getElementById("formEditPeserta");
    if (form) {
      form.addEventListener("submit", async (e) => {
        e.preventDefault();
        await this.handleEditSubmit(e);
      });
    }
  }

  // Setup file preview untuk foto KTP
  setupFilePreview() {
    const fileInput = document.getElementById("edit_foto_ktp");
    const preview = document.getElementById("edit_preview_ktp");

    if (fileInput && preview) {
      fileInput.addEventListener("change", (e) => {
        const file = e.target.files[0];
        if (file) {
          // VALIDASI FILE
          if (!file.type.startsWith("image/")) {
            this.showError("File harus berupa gambar");
            fileInput.value = "";
            return;
          }

          if (file.size > 5 * 1024 * 1024) {
            // Max 5MB
            this.showError("Ukuran file maksimal 5MB");
            fileInput.value = "";
            return;
          }

          const reader = new FileReader();
          reader.onload = (e) => {
            preview.src = e.target.result;
            preview.style.display = "block";
          };
          reader.readAsDataURL(file);
        } else {
          preview.style.display = "none";
        }
      });
    }
  }

  // Search participants
  searchParticipants(searchTerm) {
    if (!searchTerm.trim()) {
      this.renderParticipants();
      return;
    }

    const filteredParticipants = this.participants.filter((participant) => {
      const searchFields = [
        participant.nama,
        participant.email,
        participant.no_wa,
        participant.alamat,
        participant.nik,
        participant.id_booking,
      ].map((field) => (field || "").toString().toLowerCase());

      return searchFields.some((field) =>
        field.includes(searchTerm.toLowerCase())
      );
    });

    this.renderParticipants(filteredParticipants);
  }

  // Get participant detail
  async getParticipantDetail(id) {
    try {
      const response = await fetch(`${this.baseURL}?action=detail&id=${id}`);
      const result = await response.json();

      if (result.status === 200) {
        return result.data;
      } else {
        this.showError("Gagal memuat detail peserta: " + result.message);
        return null;
      }
    } catch (error) {
      console.error("Error getting participant detail:", error);
      this.showError("Terjadi kesalahan saat memuat detail peserta");
      return null;
    }
  }

  // Update participant dengan file upload
  async updateParticipant(id, formData) {
    try {
      const response = await fetch(`${this.baseURL}?action=update&id=${id}`, {
        method: "POST",
        body: formData,
      });

      const result = await response.json();

      if (result.status === 200) {
        await this.loadParticipants();
        this.showSuccess("Peserta berhasil diupdate");
        return true;
      } else {
        this.showError("Gagal update peserta: " + result.message);
        return false;
      }
    } catch (error) {
      console.error("Error updating participant:", error);
      this.showError("Terjadi kesalahan saat update peserta");
      return false;
    }
  }

  // Delete participant
  async deleteParticipant(id) {
    const result = await Swal.fire({
      title: "Konfirmasi Hapus",
      text: "Apakah Anda yakin ingin menghapus peserta ini?",
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#dc3545",
      cancelButtonColor: "#6c757d",
      confirmButtonText: "Ya, Hapus",
      cancelButtonText: "Batal",
    });

    if (!result.isConfirmed) return;

    try {
      const response = await fetch(`${this.baseURL}?action=delete&id=${id}`, {
        method: "DELETE",
      });

      const result = await response.json();

      if (result.status === 200) {
        await this.loadParticipants();
        this.showSuccess("Peserta berhasil dihapus");
      } else {
        this.showError("Gagal menghapus peserta: " + result.message);
      }
    } catch (error) {
      console.error("Error deleting participant:", error);
      this.showError("Terjadi kesalahan saat menghapus peserta");
    }
  }

  // Edit participant - Menampilkan modal dengan data terisi
  async editParticipant(id) {
    const participant = await this.getParticipantDetail(id);
    if (!participant) return;

    this.showEditModal(participant);
  }

  // Show edit modal dengan data terisi
  showEditModal(participant) {
    this.currentEditParticipantId = participant.id_participant;

    const form = document.getElementById("formEditPeserta");
    if (!form) {
      this.showError("Form edit tidak ditemukan");
      return;
    }

    // Isi semua field form dengan data peserta
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

    // Setup preview foto KTP jika ada
    const preview = document.getElementById("edit_preview_ktp");
    if (preview) {
      if (participant.foto_ktp) {
        const imagePath = this.getImagePath(participant.foto_ktp);
        preview.src = imagePath;
        preview.style.display = "block";

        // Add error handler untuk preview
        preview.onerror = () => {
          console.error("Error loading preview image:", imagePath);
          preview.style.display = "none";
        };
      } else {
        preview.style.display = "none";
      }
    }

    // Reset file input
    const fileInput = document.getElementById("edit_foto_ktp");
    if (fileInput) {
      fileInput.value = "";
    }

    // Tampilkan modal
    const modal = new bootstrap.Modal(
      document.getElementById("editPesertaModal")
    );
    modal.show();
  }

  // Handle form edit submit dengan file upload
  async handleEditSubmit(event) {
    event.preventDefault();

    if (!this.currentEditParticipantId) {
      this.showError("ID peserta tidak ditemukan");
      return;
    }

    // Tampilkan loading
    const submitBtn = event.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Menyimpan...';
    submitBtn.disabled = true;

    const form = event.target;
    const formData = new FormData(form);

    // Tambahkan ID untuk update
    formData.append("id_participant", this.currentEditParticipantId);

    try {
      const success = await this.updateParticipant(
        this.currentEditParticipantId,
        formData
      );

      if (success) {
        // Tutup modal
        const modal = bootstrap.Modal.getInstance(
          document.getElementById("editPesertaModal")
        );
        if (modal) {
          modal.hide();
        }

        // Reset current edit ID
        this.currentEditParticipantId = null;
      }
    } catch (error) {
      console.error("Error in handleEditSubmit:", error);
      this.showError("Terjadi kesalahan saat menyimpan data");
    } finally {
      // Kembalikan tombol
      submitBtn.innerHTML = originalText;
      submitBtn.disabled = false;
    }
  }

  // UTILITY FUNCTIONS - INI YANG HILANG!

  // Escape HTML untuk mencegah XSS
  escapeHtml(text) {
    if (text === null || text === undefined) return "";
    const div = document.createElement("div");
    div.textContent = text.toString();
    return div.innerHTML;
  }

  // Show success message
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

  // Show error message
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

  // Refresh data
  async refresh() {
    await this.loadParticipants();
  }

  // Debug method
  debugParticipants() {
    console.log("=== DEBUG PARTICIPANTS ===");
    this.participants.forEach((p, index) => {
      console.log(`Participant ${index + 1}:`, {
        id: p.id_participant,
        nama: p.nama,
        foto_ktp: p.foto_ktp,
        foto_path: this.getImagePath(p.foto_ktp),
      });
    });
    console.log("=== END DEBUG ===");
  }
}

// Initialize when DOM is loaded
document.addEventListener("DOMContentLoaded", () => {
  window.pesertaAPI = new PesertaAPI();
});
