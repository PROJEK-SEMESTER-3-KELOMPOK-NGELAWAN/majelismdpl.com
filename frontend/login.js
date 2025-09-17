function closeLoginModal() {
  document.getElementById('loginModal').style.display = 'none';
}
document.querySelector("#loginModal form").addEventListener("submit", async function (e) {
  e.preventDefault();
  const formData = new FormData(this);
  const response = await fetch(
    "/majelismdpl.com/backend/login-api.php", 
    {
      method: "POST",
      body: formData,
    }
  );
  const result = await response.json();
  closeLoginModal();

  if (result.success) {
    Swal.fire({
      title: 'Login berhasil!',
      text: 'Selamat datang ' + formData.get("username"),
      icon: 'success',
      confirmButtonText: 'Lanjutkan'
    }).then(() => {
      if(result.role === 'admin') {
        window.location.href = "/majelismdpl.com/admin/index.php";
      } else {
        window.location.href = "/majelismdpl.com";
      }
    });
  } else {
    Swal.fire({
      title: 'Login gagal',
      text: result.message,
      icon: 'error',
      confirmButtonText: 'Coba lagi'
    });
  }
});
