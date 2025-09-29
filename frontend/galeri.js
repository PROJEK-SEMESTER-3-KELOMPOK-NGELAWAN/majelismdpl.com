const fileInput = document.getElementById("fileInput");
const imagePreview = document.getElementById("imagePreview");
const galleryGrid = document.getElementById("galleryGrid");
const formUpload = document.getElementById("formUpload");
const alertContainer = document.getElementById("alertContainer");

// Preview image saat file dipilih
fileInput.addEventListener("change", () => {
  const file = fileInput.files[0];
  if (file) {
    // Validasi tipe file
    if (!file.type.startsWith("image/")) {
      showAlert("File harus berupa gambar!", "danger");
      fileInput.value = "";
      return;
    }

    // Validasi ukuran file (max 5MB)
    if (file.size > 5 * 1024 * 1024) {
      showAlert("Ukuran file maksimal 5MB!", "danger");
      fileInput.value = "";
      return;
    }

    const reader = new FileReader();
    reader.onload = (e) => {
      imagePreview.src = e.target.result;
      imagePreview.style.display = "block";
    };
    reader.readAsDataURL(file);
  } else {
    imagePreview.style.display = "none";
    imagePreview.src = "";
  }
});

// Handle form upload
formUpload.addEventListener("submit", async (e) => {
  e.preventDefault();

  const file = fileInput.files[0];
  if (!file) {
    showAlert("Pilih gambar terlebih dahulu!", "danger");
    return;
  }

  // Disable button saat upload
  const submitBtn = formUpload.querySelector('button[type="submit"]');
  const originalText = submitBtn.innerHTML;
  submitBtn.innerHTML =
    '<span class="spinner-border spinner-border-sm me-2"></span>Uploading...';
  submitBtn.disabled = true;

  try {
    const formData = new FormData();
    formData.append("image", file);

    const response = await fetch("../backend/galeri-api.php", {
      method: "POST",
      body: formData,
    });

    const result = await response.json();

    if (result.success) {
      showToast("success", "Gambar berhasil diupload!");
      // Reset form
      fileInput.value = "";
      imagePreview.style.display = "none";
      imagePreview.src = "";
      // Refresh gallery
      loadGallery();
    } else {
      showToast("error", result.message || "Gagal upload gambar!");
    }
  } catch (error) {
    console.error("Error:", error);
    showAlert("Terjadi kesalahan saat upload!", "danger");
  } finally {
    // Restore button
    submitBtn.innerHTML = originalText;
    submitBtn.disabled = false;
  }
});

// Load gallery saat halaman dimuat
document.addEventListener("DOMContentLoaded", () => {
  loadGallery();
});

// Function untuk load gallery dari database
async function loadGallery() {
  try {
    const response = await fetch("../backend/galeri-api.php?action=get");
    const result = await response.json();

    if (result.success) {
      displayGallery(result.data);
    } else {
      console.error("Failed to load gallery:", result.message);
    }
  } catch (error) {
    console.error("Error loading gallery:", error);
  }
}

// Function untuk menampilkan gallery
function displayGallery(images) {
  galleryGrid.innerHTML = "";

  if (images.length === 0) {
    galleryGrid.innerHTML =
      '<div class="col-12 text-center text-muted" style = "justify-content: center">Belum ada gambar dalam galeri</div>';
    return;
  }

  images.forEach((image) => {
    const div = document.createElement("div");
    div.className = "gallery-item";
    div.innerHTML = `
            <div class="position-relative">
                <img src="../img/${image.gallery}" alt="${image.gallery}" loading="lazy">
                <div class="gallery-overlay">
                    <button class="btn btn-primary btn-sm btn-view" onclick="viewImage('${image.gallery}')" title="Lihat">
                        <i class="bi bi-eye"></i>
                    </button>
                    <button class="btn btn-danger btn-sm btn-delete" onclick="deleteImage(${image.id_galleries}, '${image.gallery}')" title="Hapus">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
            <div class="gallery-info">
                <small class="text-muted">${image.gallery}</small>
            </div>
        `;
    galleryGrid.appendChild(div);
  });
}

// Function untuk melihat gambar dalam modal
function viewImage(imageName) {
  const modal = document.createElement("div");
  modal.className = "modal fade";
  modal.innerHTML = `
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">${imageName}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <img src="../img/${imageName}" alt="${imageName}" class="img-fluid rounded">
                </div>
            </div>
        </div>
    `;

  document.body.appendChild(modal);
  const bootstrapModal = new bootstrap.Modal(modal);
  bootstrapModal.show();

  modal.addEventListener("hidden.bs.modal", () => {
    modal.remove();
  });
}

// Function untuk menghapus gambar dengan modal custom
// Function untuk menghapus gambar dengan modal custom
async function deleteImage(id, imageName) {
  const modal = document.createElement("div");
  modal.className = "modal fade modal-delete";
  modal.setAttribute("data-bs-backdrop", "static");
  modal.innerHTML = `
    <div class="modal-dialog modal-dialog-centered" style="max-width: 380px;">
      <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; background: white;">
        <div class="modal-body text-center" style="padding: 2.5rem 2rem;">
          <!-- Icon peringatan dengan lingkaran orange -->
          <div class="mb-3">
            <div class="d-inline-flex align-items-center justify-content-center" 
                 style="width: 75px; height: 75px; background: #FFF3E0; border-radius: 50%;">
              <i class="bi bi-exclamation" style="font-size: 2.8rem; color: #FF9800; font-weight: bold; line-height: 1;"></i>
            </div>
          </div>
          
          <!-- Judul -->
          <h4 class="fw-bold mb-2" style="color: #2D3748; font-size: 1.4rem; margin-bottom: 0.8rem !important;">
            Hapus Trip?
          </h4>
          
          <!-- Pesan -->
          <p class="mb-4" style="color: #718096; font-size: 0.95rem; line-height: 1.4; margin-bottom: 2.2rem !important;">
            Data akan dihapus permanen.
          </p>
          
          <!-- Tombol dengan styling yang tepat -->
          <div class="d-flex gap-3 justify-content-center">
            <button type="button" 
                    class="btn flex-fill" 
                    style="background: #E2E8F0; color: #4A5568; border: none; border-radius: 10px; font-weight: 500; padding: 12px 20px; font-size: 0.9rem;"
                    data-bs-dismiss="modal">
              Batal
            </button>
            <button type="button" 
                    class="btn flex-fill" 
                    style="background: #673AB7; color: white; border: none; border-radius: 10px; font-weight: 500; padding: 12px 20px; font-size: 0.9rem;"
                    onclick="confirmDelete(${id}, '${imageName}')">
              Ya, Hapus
            </button>
          </div>
        </div>
      </div>
    </div>
  `;

  document.body.appendChild(modal);
  const bootstrapModal = new bootstrap.Modal(modal);
  bootstrapModal.show();

  modal.addEventListener("hidden.bs.modal", () => {
    modal.remove();
  });
}

// Function untuk konfirmasi delete
async function confirmDelete(id, imageName) {
  // Close modal first
  const modal = document.querySelector(".modal-delete");
  const bootstrapModal = bootstrap.Modal.getInstance(modal);
  bootstrapModal.hide();

  try {
    const formData = new FormData();
    formData.append("action", "delete");
    formData.append("id", id);
    formData.append("imageName", imageName);

    const response = await fetch("../backend/galeri-api.php", {
      method: "POST",
      body: formData,
    });

    const result = await response.json();

    if (result.success) {
      showToast("success", "Gambar berhasil dihapus");
      loadGallery();
    } else {
      showToast("error", result.message || "Gagal menghapus gambar!");
    }
  } catch (error) {
    console.error("Error:", error);
    showAlert("Terjadi kesalahan saat menghapus gambar!", "danger");
  }
}

// Function untuk menampilkan alert
function showAlert(message, type) {
  const alertDiv = document.createElement("div");
  alertDiv.className = `alert alert-${type} alert-dismissible fade show alert-custom alert-${type}-custom`;
  alertDiv.innerHTML = `
        <i class="bi bi-${
          type === "success" ? "check-circle" : "exclamation-triangle"
        } me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;

  alertContainer.appendChild(alertDiv);

  // Auto remove after 5 seconds
  setTimeout(() => {
    if (alertDiv.parentNode) {
      alertDiv.remove();
    }
  }, 5000);
}

// Function untuk menampilkan toast notification
function showToast(type, message) {
  Swal.fire({
    toast: true,
    position: "top-end",
    icon: type,
    title: message,
    showConfirmButton: false,
    timer: 2000,
    timerProgressBar: true,
    customClass: {
      popup: "colored-toast",
    },
  });
}
