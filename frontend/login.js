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

// Fungsi untuk show error modal seperti logout confirmation
function showLoginErrorModal(errorMessage) {
  const errorModal = document.getElementById("login-error-modal");
  const errorMessageEl = document.getElementById("login-error-message");

  if (errorModal && errorMessageEl) {
    errorMessageEl.textContent =
      errorMessage || "Terjadi kesalahan. Silakan coba lagi.";
    errorModal.classList.add("show");
  }
}

// Fungsi untuk hide error modal
function hideLoginErrorModal() {
  const errorModal = document.getElementById("login-error-modal");
  if (errorModal) {
    errorModal.classList.remove("show");
  }
}

// Fungsi untuk handle Google OAuth Login
function handleGoogleLogin() {
  const basePath = getBasePath();
  window.location.href =
    window.location.origin + basePath + "backend/google-oauth.php?type=login";
}

// Helper function untuk detect base path
function getBasePath() {
  const path = window.location.pathname;
  if (path.includes("/user/") || path.includes("/admin/")) {
    return "/majelismdpl.com/";
  }
  return "/majelismdpl.com/";
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

// Handle regular form submission
document.addEventListener("DOMContentLoaded", function () {
  const loginForm = document.querySelector("#loginModal form");
  if (loginForm) {
    loginForm.addEventListener("submit", async function (e) {
      e.preventDefault();
      const formData = new FormData(this);
      const basePath = getBasePath();

      try {
        const response = await fetch(basePath + "backend/login-api.php", {
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

          // Show success message using alert (simple approach)
          // Or gunakan custom success modal jika ingin yang fancy
          setTimeout(() => {
            alert(
              "✅ Login berhasil! Selamat datang " +
                (result.username || formData.get("username"))
            );

            // Redirect based on role
            if (["admin", "super_admin"].includes(result.role)) {
              setTimeout(() => {
                window.location.href = basePath + "admin/index.php";
              }, 100);
            } else {
              window.location.href = basePath;
            }
          }, 350);
        } else {
          // ✅ LOGIN GAGAL - Close modal dulu, baru error muncul
          closeLoginModal();

          // Delay untuk smooth transition seperti logout confirmation
          setTimeout(() => {
            showLoginErrorModal(
              result.message || "Terjadi kesalahan. Silakan coba lagi."
            );
          }, 350);
        }
      } catch (error) {
        // ✅ ERROR SISTEM - Close modal dulu, baru error muncul
        closeLoginModal();

        setTimeout(() => {
          showLoginErrorModal(
            "Terjadi kesalahan sistem. Silakan coba lagi atau hubungi administrator."
          );
        }, 350);
      }
    });
  }

  // ========== ERROR MODAL EVENT HANDLERS ==========
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

// Global fallback function
window.handleGoogleLogin = handleGoogleLogin;
