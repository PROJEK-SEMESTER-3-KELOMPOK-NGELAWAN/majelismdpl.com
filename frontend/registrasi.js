function closeSignUpModal() {
  document.getElementById('signUpModal').style.display = 'none';
}

document.querySelector("#signUpModal form").addEventListener("submit", async function (e) {
  e.preventDefault();

  const formData = new FormData(this);

  const response = await fetch(
    "/majelismdpl.com/backend/registrasi-api.php",
    {
      method: "POST",
      body: formData,
    }
  );
  const result = await response.json();

  // Tutup modal di kedua kondisi
  closeSignUpModal();

  if (result.success) {
    Swal.fire({
      title: 'Registrasi Berhasil!',
      text: 'Selamat datang ' + formData.get("username"),
      icon: 'success',
      confirmButtonText: 'Ke beranda'
    }).then(() => {
      window.location.href = "/majelismdpl.com";
    });
  } else {
    Swal.fire({
      title: 'Registrasi Gagal',
      text: result.message,
      icon: 'error',
      confirmButtonText: 'Coba lagi'
    });
  }
});
