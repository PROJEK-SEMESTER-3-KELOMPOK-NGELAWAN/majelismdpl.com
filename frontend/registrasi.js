function closeSignUpModal() {
  const modal = document.getElementById("signUpModal");
  if (modal) {
    modal.style.display = "none";
  }
}

function handleGoogleOAuth() {
  window.location.href =
    window.location.origin + "/majelismdpl.com/backend/google-oauth.php";
}

function attachGoogleButtonListener() {
  const googleBtn = document.getElementById("googleSignUpBtn");

  if (googleBtn && !googleBtn.hasAttribute("data-js-listener")) {
    googleBtn.setAttribute("data-js-listener", "true");

    googleBtn.addEventListener("click", function (e) {
      e.preventDefault();
      e.stopPropagation();
      handleGoogleOAuth();
    });

    return true;
  }
  return false;
}

// Function to show resend verification option
function showResendVerification(email) {
  closeSignUpModal();
  Swal.fire({
    title: '📧 Email Verifikasi Diperlukan',
    html: `
      <div style="text-align: left; padding: 20px;">
        <p style="margin-bottom: 15px;">✅ <strong>Registrasi berhasil!</strong> Namun akun Anda belum aktif.</p>
        <p style="margin-bottom: 15px;">📬 Kami telah mengirimkan email verifikasi ke:</p>
        <p style="background: #f8f9fa; padding: 10px; border-radius: 5px; font-weight: bold; margin-bottom: 15px; word-break: break-all;">${email}</p>
        <p style="margin-bottom: 15px;">🔍 Silakan cek <strong>inbox</strong> dan <strong>folder spam</strong> Anda.</p>
        <p style="margin-bottom: 10px;">⏰ Email tidak diterima dalam 5 menit?</p>
      </div>
    `,
    icon: 'info',
    showCancelButton: true,
    confirmButtonText: '📤 Kirim Ulang Email',
    cancelButtonText: '❌ Tutup',
    confirmButtonColor: '#3498db',
    cancelButtonColor: '#95a5a6',
    width: '500px',
    customClass: { popup: 'email-verification-popup' }
  }).then((result) => {
    if (result.isConfirmed) {
      resendVerificationEmail(email);
    }
  });
}

// Function to resend verification email
async function resendVerificationEmail(email) {
  try {
    const formData = new FormData();
    formData.append('email', email);

    closeSignUpModal();
    Swal.fire({
      title: '📤 Mengirim Ulang Email...',
      text: 'Mohon tunggu sebentar',
      allowOutsideClick: false,
      showConfirmButton: false,
      willOpen: () => { Swal.showLoading(); }
    });

    const response = await fetch('./backend/email-verify/resend-verification.php', {
      method: 'POST',
      body: formData
    });

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }

    const contentType = response.headers.get("content-type");
    if (!contentType || !contentType.includes("application/json")) {
      const responseText = await response.text();
      console.error('Non-JSON response:', responseText);
      throw new Error("Server response bukan JSON");
    }

    const result = await response.json();

    closeSignUpModal();
    if (result.success) {
      Swal.fire({
        title: '✅ Email Terkirim!',
        html: `<p>${result.message}</p>
               <br>
               <p style="font-size: 14px; color: #666;">Silakan cek email Anda dalam beberapa menit.</p>`,
        icon: 'success',
        confirmButtonText: 'OK'
      });
    } else {
      Swal.fire({
        title: '❌ Gagal Mengirim Email',
        text: result.message || 'Gagal mengirim ulang email verifikasi',
        icon: 'error',
        confirmButtonText: 'OK'
      });
    }
  } catch (error) {
    closeSignUpModal();
    console.error('Resend email error:', error);
    Swal.fire({
      title: '⚠️ Error',
      text: 'Terjadi kesalahan saat mengirim ulang email: ' + error.message,
      icon: 'error',
      confirmButtonText: 'OK'
    });
  }
}

// Form validation functions
function validatePassword(password) {
  if (!password || password.length < 6) {
    return { valid: false, message: 'Password minimal 6 karakter' };
  }
  return { valid: true };
}

function validateEmail(email) {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!email || !emailRegex.test(email)) {
    return { valid: false, message: 'Format email tidak valid' };
  }
  return { valid: true };
}

function validatePhone(phone) {
  if (!phone) {
    return { valid: false, message: 'Nomor HP wajib diisi' };
  }
  const cleanPhone = phone.replace(/[\s-]/g, '');
  const phoneRegex = /^(\+62|62|0)[0-9]{9,12}$/;
  if (!phoneRegex.test(cleanPhone)) {
    return { valid: false, message: 'Format nomor HP tidak valid (contoh: 081234567890)' };
  }
  return { valid: true };
}

function validateUsername(username) {
  if (!username || username.length < 3) {
    return { valid: false, message: 'Username minimal 3 karakter' };
  }
  return { valid: true };
}

function validateAlamat(alamat) {
  if (!alamat || alamat.trim().length < 5) {
    return { valid: false, message: 'Alamat minimal 5 karakter' };
  }
  return { valid: true };
}

document.addEventListener("DOMContentLoaded", function () {
  if (!attachGoogleButtonListener()) {
    setTimeout(() => { attachGoogleButtonListener(); }, 500);
  }

  // Handle form submission
  const signUpForm = document.querySelector("#signUpModal form");
  if (signUpForm) {
    signUpForm.addEventListener("submit", async function (e) {
      e.preventDefault();

      const formData = new FormData(this);

      // Get form values
      const username = formData.get('username')?.trim() || '';
      const password = formData.get('password') || '';
      const confirmPassword = formData.get('confirm_password') || '';
      const email = formData.get('email')?.trim() || '';
      const phone = formData.get('no_wa')?.trim() || '';
      const alamat = formData.get('alamat')?.trim() || '';

      // Client-side validation
      const usernameValidation = validateUsername(username);
      if (!usernameValidation.valid) {
        closeSignUpModal();
        Swal.fire({
          title: 'Validasi Gagal',
          text: usernameValidation.message,
          icon: 'error',
          confirmButtonText: 'OK'
        });
        return;
      }

      const passwordValidation = validatePassword(password);
      if (!passwordValidation.valid) {
        closeSignUpModal();
        Swal.fire({
          title: 'Validasi Gagal',
          text: passwordValidation.message,
          icon: 'error',
          confirmButtonText: 'OK'
        });
        return;
      }

      if (password !== confirmPassword) {
        closeSignUpModal();
        Swal.fire({
          title: 'Password Tidak Cocok',
          text: 'Password dan konfirmasi password harus sama',
          icon: 'error',
          confirmButtonText: 'OK'
        });
        return;
      }

      const emailValidation = validateEmail(email);
      if (!emailValidation.valid) {
        closeSignUpModal();
        Swal.fire({
          title: 'Email Tidak Valid',
          text: emailValidation.message,
          icon: 'error',
          confirmButtonText: 'OK'
        });
        return;
      }

      const phoneValidation = validatePhone(phone);
      if (!phoneValidation.valid) {
        closeSignUpModal();
        Swal.fire({
          title: 'Nomor HP Tidak Valid',
          text: phoneValidation.message,
          icon: 'error',
          confirmButtonText: 'OK'
        });
        return;
      }

      const alamatValidation = validateAlamat(alamat);
      if (!alamatValidation.valid) {
        closeSignUpModal();
        Swal.fire({
          title: 'Alamat Tidak Valid',
          text: alamatValidation.message,
          icon: 'error',
          confirmButtonText: 'OK'
        });
        return;
      }

      // Show loading
      closeSignUpModal();
      Swal.fire({
        title: '🏔️ Mendaftarkan Akun...',
        html: `
          <div style="padding: 20px;">
            <p>Sedang memproses registrasi Anda...</p>
            <p style="font-size: 14px; color: #666; margin-top: 10px;">Mohon tunggu sebentar</p>
          </div>
        `,
        allowOutsideClick: false,
        showConfirmButton: false,
        willOpen: () => { Swal.showLoading(); }
      });

      try {
        const response = await fetch("./backend/registrasi-api.php", {
          method: "POST",
          body: formData,
        });

        if (!response.ok) {
          let errorMessage = `Server error: ${response.status} - ${response.statusText}`;
          if (response.status === 500) errorMessage = 'Server mengalami masalah internal. Silakan hubungi administrator.';
          else if (response.status === 404) errorMessage = 'File API tidak ditemukan. Periksa konfigurasi server.';
          else if (response.status === 403) errorMessage = 'Akses ditolak. Periksa permission file.';
          throw new Error(errorMessage);
        }

        const responseText = await response.text();
        if (!responseText.trim()) throw new Error("Server mengembalikan response kosong");

        let result;
        try { result = JSON.parse(responseText); }
        catch (parseError) {
          console.error('JSON Parse Error:', parseError);
          console.error('Response was:', responseText);
          throw new Error("Server response bukan JSON yang valid");
        }

        closeSignUpModal();

        if (result.success) {
          if (result.email_sent) {
            showResendVerification(result.email || email);
          } else {
            closeSignUpModal();
            Swal.fire({
              title: "🎉 Registrasi Berhasil!",
              html: `
                <div style="text-align: left; padding: 20px;">
                  <p style="margin-bottom: 15px;">✅ Akun Anda telah berhasil dibuat!</p>
                  <p style="margin-bottom: 15px;">⚠️ <strong>Namun:</strong> ${result.message}</p>
                  <p style="margin-bottom: 10px;">Apa yang ingin Anda lakukan?</p>
                </div>
              `,
              icon: "warning",
              showCancelButton: true,
              confirmButtonText: "📤 Kirim Email Verifikasi",
              cancelButtonText: "⏭️ Nanti Saja",
              confirmButtonColor: '#3498db',
              cancelButtonColor: '#95a5a6'
            }).then((swalResult) => {
              if (swalResult.isConfirmed) {
                resendVerificationEmail(result.email || email);
              }
            });
          }
          signUpForm.reset();
        } else {
          closeSignUpModal();
          Swal.fire({
            title: "❌ Registrasi Gagal",
            text: result.message || 'Terjadi kesalahan saat mendaftar',
            icon: "error",
            confirmButtonText: "Coba Lagi"
          });
        }
      } catch (error) {
        closeSignUpModal();
        console.error('Registration error:', error);
        Swal.fire({
          title: "⚠️ Error",
          text: error.message,
          icon: "error",
          confirmButtonText: "OK",
          footer: '<small>Jika masalah berlanjut, silakan hubungi administrator</small>'
        });
      }
    });
  } else {
    closeSignUpModal();
    console.error('Registration form not found!');
  }
});

// Real-time validation
document.addEventListener('DOMContentLoaded', function() {
  const passwordField = document.getElementById('signupPassword');
  const confirmPasswordField = document.getElementById('confirmPassword');
  const emailField = document.querySelector('input[name="email"]');
  const phoneField = document.querySelector('input[name="no_wa"]');
  const usernameField = document.querySelector('input[name="username"]');
  const alamatField = document.querySelector('input[name="alamat"]');

  if (usernameField) {
    usernameField.addEventListener('blur', function() {
      const validation = validateUsername(this.value);
      if (!validation.valid && this.value) {
        this.style.borderColor = '#e74c3c';
        this.style.boxShadow = '0 0 5px rgba(231, 76, 60, 0.3)';
      } else {
        this.style.borderColor = '';
        this.style.boxShadow = '';
      }
    });
  }

  if (passwordField) {
    passwordField.addEventListener('blur', function() {
      const validation = validatePassword(this.value);
      if (!validation.valid && this.value) {
        this.style.borderColor = '#e74c3c';
        this.style.boxShadow = '0 0 5px rgba(231, 76, 60, 0.3)';
      } else {
        this.style.borderColor = '';
        this.style.boxShadow = '';
      }
    });
  }
  if (confirmPasswordField && passwordField) {
    confirmPasswordField.addEventListener('blur', function() {
      if (this.value && this.value !== passwordField.value) {
        this.style.borderColor = '#e74c3c';
        this.style.boxShadow = '0 0 5px rgba(231, 76, 60, 0.3)';
      } else {
        this.style.borderColor = '';
        this.style.boxShadow = '';
      }
    });
  }

  if (emailField) {
    emailField.addEventListener('blur', function() {
      const validation = validateEmail(this.value);
      if (!validation.valid && this.value) {
        this.style.borderColor = '#e74c3c';
        this.style.boxShadow = '0 0 5px rgba(231, 76, 60, 0.3)';
      } else {
        this.style.borderColor = '';
        this.style.boxShadow = '';
      }
    });
  }
  if (phoneField) {
    phoneField.addEventListener('blur', function() {
      const validation = validatePhone(this.value);
      if (!validation.valid && this.value) {
        this.style.borderColor = '#e74c3c';
        this.style.boxShadow = '0 0 5px rgba(231, 76, 60, 0.3)';
      } else {
        this.style.borderColor = '';
        this.style.boxShadow = '';
      }
    });
  }
  if (alamatField) {
    alamatField.addEventListener('blur', function() {
      const validation = validateAlamat(this.value);
      if (!validation.valid && this.value) {
        this.style.borderColor = '#e74c3c';
        this.style.boxShadow = '0 0 5px rgba(231, 76, 60, 0.3)';
      } else {
        this.style.borderColor = '';
        this.style.boxShadow = '';
      }
    });
  }
});

// Other initialization strategies
if (document.readyState === "complete" || document.readyState === "interactive") {
  setTimeout(attachGoogleButtonListener, 100);
}

let attempts = 0;
const maxAttempts = 20;
const checkInterval = setInterval(() => {
  attempts++;
  if (attachGoogleButtonListener()) {
    clearInterval(checkInterval);
  } else if (attempts >= maxAttempts) {
    clearInterval(checkInterval);
  }
}, 250);

document.addEventListener("click", function (e) {
  if (e.target && e.target.id === "open-signup") {
    setTimeout(() => { attachGoogleButtonListener(); }, 200);
  }
});

window.handleGoogleSignUp = handleGoogleOAuth;
