/**
 * ============================================
 * MODAL & ERROR HANDLING FUNCTIONS (LEGACY/FALLBACK)
 * ============================================
 */
function closeLoginModal() {
  const modal = document.getElementById("loginModal");
  if (modal) {
    // Close using the global closeModal function from auth-modals.php
    if (typeof closeModal === "function") {
      closeModal(modal);
    } else {
      modal.style.display = "none";
      modal.classList.remove("open");
      document.body.style.overflow = "";
    }
  }
}

// Fungsi untuk show error modal seperti logout confirmation (Hanya sebagai FALLBACK)
function showLoginErrorModal(errorMessage) {
  const errorModal = document.getElementById("login-error-modal");
  const errorMessageEl = document.getElementById("login-error-message");

  if (errorModal && errorMessageEl) {
    errorMessageEl.textContent =
      errorMessage || "Terjadi kesalahan. Silakan coba lagi.";
    errorModal.classList.add("show");
  }
}

// Fungsi untuk hide error modal (Hanya sebagai FALLBACK)
function hideLoginErrorModal() {
  const errorModal = document.getElementById("login-error-modal");
  if (errorModal) {
    errorModal.classList.remove("show");
  }
}

/**
 * ============================================
 * GOOGLE LOGIN & HELPER FUNCTIONS
 * ============================================
 */
// Fungsi untuk handle Google OAuth Login
function handleGoogleLogin() {
  window.location.href = getPageUrl("backend/google-oauth.php") + "?type=login";
}

// Helper function untuk detect base path - GUNAKAN CONFIG
function getBasePath() {
  // Asumsi getPageUrl("") mengembalikan base URL
  return getPageUrl("").replace(window.location.origin, "");
}

// Function untuk attach Google login button listener
function attachGoogleLoginListener() {
  const googleLoginBtn = document.getElementById("googleLoginBtn");

  if (googleLoginBtn && !googleLoginBtn.hasAttribute("data-login-listener")) {
    googleLoginBtn.setAttribute("data-login-listener", "true");

    googleLoginBtn.addEventListener("click", function (e) {
      e.preventDefault();
      e.stopPropagation();
      handleGoogleLogin();
    });

    googleLoginBtn.onclick = function (e) {
      e.preventDefault();
      handleGoogleLogin();
      return false;
    };

    return true;
  }
  return false;
}

// Global fallback function
window.handleGoogleLogin = handleGoogleLogin;

/**
 * ============================================
 * REGULAR FORM SUBMISSION HANDLER (INTEGRATED SWEETALERT2)
 * ============================================
 */
document.addEventListener("DOMContentLoaded", function () {
  const loginForm = document.querySelector("#loginModal form");
  if (loginForm) {
    loginForm.addEventListener("submit", async function (e) {
      e.preventDefault();
      const formData = new FormData(this);

      try {
        const response = await fetch(getApiUrl("backend/login-api.php"), {
          method: "POST",
          body: formData,
        });

        if (!response.ok) {
          throw new Error("Network response was not ok");
        }

        const result = await response.json();

        if (result.success) {
          // ✅ LOGIN BERHASIL
          closeLoginModal();

          if (typeof Swal !== "undefined") {
            const username = result.username || formData.get("username");

            // SWEETALERT REVISI: Menggunakan tombol OK/Lanjutkan
            Swal.fire({
              title: "Login Berhasil! 🎉",
              html: `Selamat datang, ${username}!`,
              icon: "success",
              showConfirmButton: true, // Tampilkan tombol
              confirmButtonText: "Lanjutkan", // Teks tombol seperti yang diminta
              position: "center",
              toast: false,
              allowOutsideClick: false,
              backdrop: `
                rgba(0,0,0,0.6)
                url("${getAssetsUrl(
                  "assets/login-bg.jpg"
                )}") 
                center center
                no-repeat
              `,
              customClass: {
                popup: "swal2-custom-background",
              },
            }).then(() => {
              // Redirect hanya dilakukan setelah pengguna mengklik tombol
              if (["admin", "super_admin"].includes(result.role)) {
                window.location.href = getPageUrl("admin/index.php");
              } else {
                window.location.href = getPageUrl("index.php");
              }
            });
          }
          } else {
          // ✅ LOGIN GAGAL - Tampilkan SweetAlert Error
          closeLoginModal();

          const errorMessage =
            result.message || "Username atau kata sandi salah."; // Teks lebih simpel

          setTimeout(() => {
            if (typeof Swal !== "undefined") {
              Swal.fire({
                title: "Login Gagal ⚠️",
                html: errorMessage,
                icon: "error",
                confirmButtonText: "Coba Lagi",
                showCancelButton: true,
                cancelButtonText: "Tutup",
                position: "center",
                allowOutsideClick: true,
                backdrop: `
                        rgba(0,0,0,0.6)
                        url("${getAssetsUrl(
                          "assets/error-bg.gif"
                        )}") // Ganti dengan path gambar error Anda
                        center center
                        no-repeat
                    `,
                customClass: {
                  popup: "swal2-custom-background",
                },
              }).then((action) => {
                if (action.isConfirmed) {
                  // Re-open login modal
                  const loginModal = document.getElementById("loginModal");
                  if (loginModal && typeof openModal === "function") {
                    openModal(loginModal);
                  }
                }
              });
            } else {
              // Fallback error
              showLoginErrorModal(errorMessage);
            }
          }, 350);
        }
      } catch (error) {
        // ✅ ERROR SISTEM - Tampilkan SweetAlert Sistem Error
        closeLoginModal();

        setTimeout(() => {
          if (typeof Swal !== "undefined") {
            Swal.fire({
              title: "Kesalahan Sistem ❌",
              text: "Terjadi kesalahan. Coba lagi nanti.", // Teks lebih simpel
              icon: "error",
              confirmButtonText: "Tutup",
              position: "center",
              allowOutsideClick: true,
              backdrop: `
                    rgba(0,0,0,0.6)
                    url("${getAssetsUrl(
                      "assets/system-error-bg.gif"
                    )}") // Ganti dengan path gambar system error Anda
                    center center
                    no-repeat
                `,
              customClass: {
                popup: "swal2-custom-background",
              },
            });
          } else {
            // Fallback system error
            showLoginErrorModal(
              "Terjadi kesalahan sistem. Silakan coba lagi atau hubungi administrator."
            );
          }
        }, 350);
      }
    });
  }

  // ========== ERROR MODAL EVENT HANDLERS (Hanya untuk Fallback/Modal Lain) ==========
  // Ini tetap dipertahankan karena Anda mungkin masih menggunakan showLoginErrorModal untuk tujuan lain atau sebagai fallback.
  const errorModal = document.getElementById("login-error-modal");
  const errorRetryBtn = document.getElementById("login-error-retry-btn");
  const errorCancelBtn = document.getElementById("login-error-cancel-btn");

  if (errorRetryBtn) {
    errorRetryBtn.addEventListener("click", function () {
      hideLoginErrorModal();
      // Re-open login modal
      setTimeout(() => {
        const loginModal = document.getElementById("loginModal");
        if (loginModal && typeof openModal === "function") {
          openModal(loginModal);
        }
      }, 300);
    });
  }

  if (errorCancelBtn) {
    errorCancelBtn.addEventListener("click", function () {
      hideLoginErrorModal();
    });
  }

  // Close modal dengan klik backdrop
  if (errorModal) {
    errorModal.addEventListener("click", function (e) {
      if (e.target === errorModal) {
        hideLoginErrorModal();
      }
    });
  }

  // Try to attach Google login listener immediately
  if (!attachGoogleLoginListener()) {
    setTimeout(() => {
      attachGoogleLoginListener();
    }, 500);
  }
});

/**
 * ============================================
 * GOOGLE LISTENER CHECK STRATEGIES
 * ============================================
 */
// Strategy for when DOM already loaded
if (
  document.readyState === "complete" ||
  document.readyState === "interactive"
) {
  setTimeout(attachGoogleLoginListener, 100);
}

// Strategy: Interval check as last resort
let loginAttempts = 0;
const maxLoginAttempts = 20;
const loginCheckInterval = setInterval(() => {
  loginAttempts++;

  if (attachGoogleLoginListener()) {
    clearInterval(loginCheckInterval);
  } else if (loginAttempts >= maxLoginAttempts) {
    clearInterval(loginCheckInterval);
  }
}, 250);

// Listen for modal open events
document.addEventListener("click", function (e) {
  if (e.target && e.target.id === "open-login") {
    setTimeout(() => {
      attachGoogleLoginListener();
    }, 200);
  }
});
