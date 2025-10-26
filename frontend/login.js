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
          // ✅ CLOSE LOGIN MODAL FIRST
          closeLoginModal();

          // ✅ SHOW SUCCESS POPUP (Custom atau SweetAlert)
          Swal.fire({
            title: "Login berhasil!",
            text:
              "Selamat datang " + (result.username || formData.get("username")),
            icon: "success",
            confirmButtonText: "Lanjutkan",
            confirmButtonColor: "#7971ea",
            background: "#fff",
            color: "#34495e",
            showClass: {
              popup: "animate__animated animate__fadeIn animate__faster",
            },
            hideClass: {
              popup: "animate__animated animate__fadeOut animate__faster",
            },
          }).then(() => {
            // Redirect based on role
            if (["admin", "super_admin"].includes(result.role)) {
              setTimeout(() => {
                window.location.href = basePath + "admin/index.php";
              }, 100);
            } else {
              window.location.href = basePath;
            }
          });
        } else {
          // ✅ SHOW ERROR (MODAL TETAP TERBUKA)
          Swal.fire({
            title: "Login gagal",
            text: result.message,
            icon: "error",
            confirmButtonText: "Coba lagi",
            confirmButtonColor: "#7971ea",
          });
        }
      } catch (error) {
        closeLoginModal();
        Swal.fire({
          title: "Error",
          text: "Terjadi kesalahan sistem. Silakan coba lagi.",
          icon: "error",
          confirmButtonText: "OK",
          confirmButtonColor: "#7971ea",
        });
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
