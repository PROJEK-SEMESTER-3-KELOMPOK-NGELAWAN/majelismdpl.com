/**
 * ============================================
 * SCRIPT: GALERI MANAGEMENT - REVISED & ERROR-FREE
 * ============================================
 */

// --- Global DOM Selectors ---
const dom = {
    fileInput: document.getElementById("fileInput"),
    galleryGrid: document.getElementById("galleryGrid"),
    formUpload: document.getElementById("formUpload"),
    alertContainer: document.getElementById("alertContainer"),
};

// --- Helper Functions ---

/**
 * Menampilkan SweetAlert2 Toast Notification.
 */
function showToast(type, message) {
    if (typeof Swal === "undefined") {
        console.error("SweetAlert2 (Swal) is not loaded.");
        return;
    }

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

/**
 * Menampilkan Alert standar di alertContainer.
 */
function showAlert(message, type) {
    const iconClass =
        type === "success" ? "bi-check-circle" : "bi-exclamation-triangle";

    const alertDiv = document.createElement("div");
    alertDiv.className = `alert alert-${type} alert-dismissible fade show alert-custom alert-${type}-custom`;
    alertDiv.innerHTML = `
        <i class="bi ${iconClass} me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;

    dom.alertContainer.appendChild(alertDiv);

    // Auto remove after 5 seconds
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 5000);
}

// --- Gallery Core Logic ---

/**
 * Mengambil data galeri dari backend.
 */
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
        showAlert("Gagal memuat galeri. Periksa koneksi atau server.", "danger");
    }
}

/**
 * Menampilkan galeri ke DOM.
 * Dihapus: Tombol Lihat.
 */
function displayGallery(images) {
    dom.galleryGrid.innerHTML = "";

    if (images.length === 0) {
        dom.galleryGrid.innerHTML =
            '<div class="col-12 text-center text-muted" style="justify-content: center">Belum ada gambar dalam galeri</div>';
        return;
    }

    images.forEach((image) => {
        const div = document.createElement("div");
        div.className = "gallery-item";
        div.innerHTML = `
            <div class="position-relative">
                <img src="../img/${image.gallery}" alt="${image.gallery}" loading="lazy">
                <div class="gallery-overlay">
                    <button class="btn btn-danger btn-delete" onclick="deleteImage(${image.id_galleries}, '${image.gallery}')" title="Hapus">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        `;
        dom.galleryGrid.appendChild(div);
    });
}

/**
 * Memvalidasi file gambar yang dipilih.
 */
function validateFile(file) {
    const MAX_SIZE = 5 * 1024 * 1024; // 5MB

    if (!file.type.startsWith("image/")) {
        showAlert("File harus berupa gambar!", "danger");
        dom.fileInput.value = "";
        return false;
    }

    if (file.size > MAX_SIZE) {
        showAlert("Ukuran file maksimal 5MB!", "danger");
        dom.fileInput.value = "";
        return false;
    }
    return true;
}

// --- Event Handlers ---

function handleFileChange() {
    const file = dom.fileInput.files[0];
    if (!file) {
        return;
    }
    validateFile(file);
}

async function handleFormSubmit(e) {
    e.preventDefault();

    const file = dom.fileInput.files[0];
    if (!file) {
        showAlert("Pilih gambar terlebih dahulu!", "danger");
        return;
    }
    if (!validateFile(file)) {
        return;
    }

    const submitBtn = dom.formUpload.querySelector('button[type="submit"]');
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
            dom.fileInput.value = "";
            loadGallery();
        } else {
            showToast("error", result.message || "Gagal upload gambar!");
        }
    } catch (error) {
        console.error("Error:", error);
        showAlert(
            "Terjadi kesalahan saat upload! Cek konsol untuk detail.",
            "danger"
        );
    } finally {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }
}

// --- Modal Functions (Hanya menyisakan Delete) ---

/**
 * Function untuk melihat gambar dalam modal (DIHAPUS).
 * window.viewImage = function (imageName) { ... }
 * * Catatan: Jika tombol view benar-benar dihapus, fungsi ini tidak diperlukan.
 */


/**
 * Function untuk menampilkan modal konfirmasi hapus (Modal 'Delete').
 */
window.deleteImage = function (id, imageName) {
    if (typeof window.bootstrap === "undefined") {
        showAlert("Bootstrap JavaScript library not loaded.", "danger");
        return;
    }

    const modalId = `deleteConfirmModal_${id}`;

    const modal = document.createElement("div");
    modal.className = "modal fade modal-delete";
    modal.setAttribute("data-bs-backdrop", "static");
    modal.id = modalId;
    modal.setAttribute("tabindex", "-1");

    // HTML Modal Delete - (Menggunakan styling custom Anda)
    modal.innerHTML = `
        <div class="modal-dialog modal-dialog-centered" style="max-width: 380px;">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; background: white;">
                <div class="modal-body text-center" style="padding: 2.5rem 2rem;">
                    <div class="mb-3">
                        <div class="d-inline-flex align-items-center justify-content-center" 
                            style="width: 75px; height: 75px; background: #FFF3E0; border-radius: 50%;">
                            <i class="bi bi-exclamation" style="font-size: 2.8rem; color: #FF9800; font-weight: bold; line-height: 1;"></i>
                        </div>
                    </div>
                    
                    <h4 class="fw-bold mb-2" style="color: #2D3748; font-size: 1.4rem; margin-bottom: 0.8rem !important;">
                        Hapus Gambar?
                    </h4>
                    
                    <p class="mb-4" style="color: #718096; font-size: 0.95rem; line-height: 1.4; margin-bottom: 2.2rem !important;">
                        Data akan dihapus permanen.
                    </p>
                    
                    <div class="d-flex gap-3 justify-content-center">
                        <button type="button" 
                            class="btn flex-fill" 
                            style="background: #E2E8F0; color: #4A5568; border: none; border-radius: 10px; font-weight: 500; padding: 12px 20px; font-size: 0.9rem;"
                            data-bs-dismiss="modal">
                            Batal
                        </button>
                        <button type="button" 
                            class="btn flex-fill btn-confirm-delete" 
                            style="background: #dc3545; color: white; border: none; border-radius: 10px; font-weight: 500; padding: 12px 20px; font-size: 0.9rem;"
                            data-id="${id}"
                            data-imagename="${imageName}"
                            onclick="confirmDelete(${id}, '${imageName}', '${modalId}')">
                            Ya, Hapus
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;

    document.body.appendChild(modal);
    const bootstrapModal = new window.bootstrap.Modal(modal);
    bootstrapModal.show();

    // Hapus modal dari DOM setelah ditutup
    modal.addEventListener("hidden.bs.modal", () => {
        modal.remove();
    });
};

/**
 * Function untuk menjalankan logika penghapusan.
 */
window.confirmDelete = async function (id, imageName, modalId) {
    const modalElement = document.getElementById(modalId);
    if (modalElement && window.bootstrap) {
        let bootstrapModal = window.bootstrap.Modal.getInstance(modalElement);
        if (!bootstrapModal) {
            try {
                bootstrapModal = new window.bootstrap.Modal(modalElement);
            } catch (e) {
                console.warn("Gagal membuat instance modal baru (fallback):", e);
            }
        }
        if (bootstrapModal) {
            bootstrapModal.hide();
        } else {
            console.error(
                `Gagal menutup modal: Tidak dapat menemukan instance untuk ID ${modalId}. Pastikan Bootstrap JS dimuat.`
            );
        }
    } else {
        console.warn(
            `Elemen Modal (ID: ${modalId}) tidak ditemukan atau library Bootstrap tidak dimuat.`
        );
    }

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
        console.error("Error during delete:", error);
        showAlert("Terjadi kesalahan saat menghapus gambar!", "danger");
    }
};

// --- Initialization ---
document.addEventListener("DOMContentLoaded", () => {
    // 1. Load initial gallery
    loadGallery();

    // 2. Attach main event listeners
    dom.fileInput.addEventListener("change", handleFileChange);
    dom.formUpload.addEventListener("submit", handleFormSubmit);
});