// ========== CONFIG CHECK (CRITICAL!) ==========
if (typeof getApiUrl !== "function") {
  console.error("FATAL ERROR: config.js is not loaded!");
  console.error(
    "Please ensure frontend/config.js is loaded BEFORE master-admin.js"
  );

  // Fallback untuk debugging
  window.getApiUrl = function (endpoint) {
    console.warn("Using fallback getApiUrl - config.js might not be loaded");
    return "backend/" + endpoint;
  };
}

// ========== DOCUMENT READY ==========
$(document).ready(function () {
  // Verify config loaded
  if (typeof getApiUrl !== "function") {
    console.error("getApiUrl function not available");
    Swal.fire({
      title: "Error!",
      text: "Konfigurasi aplikasi tidak lengkap. Silakan refresh halaman.",
      icon: "error",
      confirmButtonColor: "#a97c50",
    });
    return;
  }

  // Initialize DataTable
  const usersTable = $("#usersTable").DataTable({
    processing: true,
    responsive: true,
    language: {
      processing: "Memuat data administrator...",
      lengthMenu: "Tampilkan _MENU_ administrator per halaman",
      zeroRecords: "Data administrator tidak ditemukan",
      info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ administrator",
      infoEmpty: "Menampilkan 0 sampai 0 dari 0 administrator",
      infoFiltered: "(difilter dari _MAX_ total data)",
      search: "Cari Administrator:",
      paginate: {
        first: "Pertama",
        last: "Terakhir",
        next: "Selanjutnya",
        previous: "Sebelumnya",
      },
    },
    columnDefs: [
      { orderable: false, targets: [6] },
      { className: "text-center", targets: [0, 5, 6] },
    ],
    order: [[0, "desc"]],
  });

  // Load administrators data
  loadAdministrators();

  // Setup form validation
  setupFormValidation();
});

/**
 * Load all administrators from API
 */
function loadAdministrators() {
  showLoading(true);

  $.ajax({
    url: getApiUrl("master-admin-api.php"),
    method: "GET",
    dataType: "json",
    timeout: 10000,
    success: function (response) {
      showLoading(false);

      if (response.success) {
        const administrators = response.data.filter(function (user) {
          return user.role === "admin" || user.role === "super_admin";
        });

        populateAdministratorsTable(administrators);

        if (administrators.length === 0) {
          showAlert(
            "info",
            "Informasi",
            "Belum ada administrator yang terdaftar dalam sistem"
          );
        }
      } else {
        showAlert(
          "error",
          "Error",
          response.message || "Gagal memuat data administrator"
        );
      }
    },
    error: function (xhr, status, error) {
      showLoading(false);
      console.error("Error loading administrators:", error);

      let errorMessage = "Terjadi kesalahan saat memuat data administrator";
      if (status === "timeout") {
        errorMessage = "Koneksi timeout. Silakan coba lagi.";
      }

      showAlert("error", "Error Koneksi", errorMessage);
    },
  });
}

/**
 * Populate administrators table
 */
function populateAdministratorsTable(administrators) {
  const table = $("#usersTable").DataTable();
  table.clear();

  const adminRoles = administrators.filter(
    (user) => user.role === "admin" || user.role === "super_admin"
  );

  adminRoles.forEach(function (admin) {
    let badgeClass = "bg-brown";
    let badgeIcon = "bi-shield-check";
    let roleText = "Admin";

    if (admin.role === "super_admin") {
      badgeClass = "bg-danger";
      badgeIcon = "bi-shield-exclamation";
      roleText = "Super Admin";
    }

    const row = [
      admin.id_user,
      `<span class="fw-semibold">${admin.username || "-"}</span>`,
      admin.email || "-",
      admin.no_wa
        ? `<a href="https://wa.me/${admin.no_wa}" target="_blank" class="text-success text-decoration-none">
        <i class="bi bi-whatsapp me-1"></i>${admin.no_wa}
      </a>`
        : "-",
      admin.alamat
        ? `<span class="text-truncate d-inline-block" style="max-width: 200px;" title="${admin.alamat}">
        ${admin.alamat}
      </span>`
        : "-",
      `<span class="badge ${badgeClass} fw-semibold">
        <i class="bi ${badgeIcon} me-1"></i>${roleText}
      </span>`,
      `<div class="action-buttons d-flex gap-1 justify-content-center">
        <button type="button" class="btn btn-warning btn-sm" onclick="editAdministrator(${admin.id_user})" title="Edit ${admin.username}">
          <i class="bi bi-pencil-square"></i>
        </button>
        <button type="button" class="btn btn-danger btn-sm" onclick="deleteAdministrator(${admin.id_user})" title="Hapus ${admin.username}">
          <i class="bi bi-trash3"></i>
        </button>
      </div>`,
    ];

    table.row.add(row);
  });

  table.draw();

  if (adminRoles.length > 0) {
    const adminCount = adminRoles.filter((u) => u.role === "admin").length;
    const superAdminCount = adminRoles.filter(
      (u) => u.role === "super_admin"
    ).length;
    $(".dataTables_info").html(
      `<strong>Total: ${adminRoles.length}</strong> administrator 
       <span class="badge bg-brown ms-2">${adminCount} Admin</span> 
       <span class="badge bg-danger ms-1">${superAdminCount} Super Admin</span>`
    );
  }
}

/**
 * Open modal for adding new administrator
 */
function openAddModal() {
  resetForm();
  $("#userModalLabel").html(
    '<i class="bi bi-shield-plus me-2"></i> Tambah Administrator Baru'
  );
  $("#modalTitle").text("Tambah Administrator Baru");
  $("#saveButtonText").text("Simpan");
  $("#actionType").val("create");
  $("#password").prop("required", true);
  $("#role").val("");
  $(".form-text").show();

  $("#roleInfo").hide();
  $(".form-control, .custom-select").removeClass("is-invalid is-valid");
}

/**
 * Edit administrator
 */
function editAdministrator(userId) {
  $.ajax({
    url: getApiUrl("master-admin-api.php"),
    method: "GET",
    data: { id: userId },
    dataType: "json",
    success: function (response) {
      if (response.success) {
        const admin = response.data;

        if (admin.role !== "admin" && admin.role !== "super_admin") {
          showAlert(
            "error",
            "Akses Ditolak",
            "Anda hanya dapat mengedit pengguna dengan role Administrator"
          );
          return;
        }

        // Populate form
        $("#userId").val(admin.id_user);
        $("#username").val(admin.username);
        $("#email").val(admin.email);
        $("#no_wa").val(admin.no_wa || "");
        $("#alamat").val(admin.alamat || "");
        $("#role").val(admin.role);
        $("#password").val("");

        // Update modal
        $("#userModalLabel").html(
          '<i class="bi bi-shield-exclamation me-2"></i> Edit Administrator'
        );
        $("#modalTitle").text("Edit Administrator");
        $("#saveButtonText").text("Update");
        $("#actionType").val("update");
        $("#password").prop("required", false);
        $(".form-text").show();

        updateRoleInfo();
        $(".form-control, .custom-select").removeClass("is-invalid is-valid");

        $("#userModal").modal("show");
      } else {
        showAlert(
          "error",
          "Error",
          response.message || "Gagal memuat data administrator"
        );
      }
    },
    error: function (xhr, status, error) {
      console.error("Error loading administrator:", error);
      showAlert(
        "error",
        "Error",
        "Terjadi kesalahan saat memuat data administrator"
      );
    },
  });
}

/**
 * Delete administrator with confirmation (REVISI AKHIR: Sangat Ramping + Custom Style)
 */
function deleteAdministrator(userId) {
    $.ajax({
        url: getApiUrl("master-admin-api.php"),
        method: "GET",
        data: { id: userId },
        dataType: "json",
        success: function (response) {
            if (
                response.success &&
                (response.data.role === "admin" || response.data.role === "super_admin")
            ) {
                const admin = response.data;
                const roleDisplay = admin.role === "admin" ? "Admin" : "Super Admin";
                const isSuperAdmin = admin.role === "super_admin";

                // Mempersiapkan badge dan teks peringatan
                const roleBadge = `<span class="badge ${isSuperAdmin ? "bg-danger" : "bg-brown"}">${roleDisplay}</span>`;
                const warningText = isSuperAdmin
                    ? "Menghapus Super Admin akan **menghilangkan akses penuh**!"
                    : "Tindakan ini tidak dapat dibatalkan.";

                // Struktur HTML yang sangat ringkas
                const confirmationHtml = `
                    <div class="text-start p-2">
                        <div class="p-2 mb-3 rounded" style="background-color: #f7ffef; border: 1px solid #e2e6e9;">
                            <dl class="row mb-0 small text-dark">
                                <dt class="col-sm-4 text-muted">Username</dt>
                                <dd class="col-sm-8 mb-0 fw-semibold">${admin.username}</dd>

                                <dt class="col-sm-4 text-muted">Email</dt>
                                <dd class="col-sm-8 mb-0">${admin.email}</dd>

                                <dt class="col-sm-4 text-muted">Role</dt>
                                <dd class="col-sm-8 mb-0">${roleBadge}</dd>
                            </dl>
                        </div>
                        
                        <div class="alert alert-warning p-2 mb-0 d-flex align-items-center">
                            <i class="bi bi-exclamation-triangle-fill fs-5 me-2"></i>
                            <span class="small">${warningText}</span>
                        </div>
                    </div>
                `;

                Swal.fire({
                    // 1. Judul konfirmasi diubah warnanya menjadi merah (danger)
                    title: `<span class="text-brown">Konfirmasi Hapus ${roleDisplay}</span>`,
                    html: confirmationHtml,
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#a97c50", // Warna cokelat agar serasi dengan tombol di gambar
                    cancelButtonColor: "#6c757d",
                    confirmButtonText: `<i class="bi bi-trash3 me-2"></i> Ya, Hapus`,
                    cancelButtonText: '<i class="bi bi-x-circle me-2"></i> Batal',
                    reverseButtons: true,
                    customClass: {
                        popup: "rounded-3",
                        // 2. Tambahkan kelas custom untuk tombol confirm
                        confirmButton: 'swal2-confirm-no-border' 
                    },
                }).then((result) => {
                    if (result.isConfirmed) {
                        performDelete(userId, admin.username, roleDisplay);
                    }
                });
            } else {
                showAlert(
                    "error",
                    "Akses Ditolak",
                    "Anda hanya dapat menghapus pengguna dengan role Administrator"
                );
            }
        },
        error: function (xhr, status, error) {
            console.error("Error validating administrator:", error);
            showAlert(
                "error",
                "Error",
                "Terjadi kesalahan saat validasi administrator"
            );
        },
    });
}

// Catatan: Warna Judul diubah ke 'text-danger' (merah) karena icon peringatan SweetAlert default-nya kuning.
// Judul merah lebih efektif untuk menekankan bahaya dan konsisten dengan tombol 'danger' di aplikasi Anda.

/**
 * Perform delete operation
 */
function performDelete(userId, username, roleDisplay) {
  Swal.fire({
    title: `Menghapus ${roleDisplay}`,
    html: `Sedang menghapus administrator <strong>${username}</strong>...`,
    allowOutsideClick: false,
    showConfirmButton: false,
    willOpen: () => {
      Swal.showLoading();
    },
  });

  $.ajax({
    url: getApiUrl("master-admin-api.php"),
    method: "POST",
    contentType: "application/json",
    data: JSON.stringify({
      action: "delete",
      id_user: userId,
    }),
    dataType: "json",
    success: function (response) {
      if (response.success) {
        showAlert(
          "success",
          "Berhasil Dihapus",
          `${roleDisplay} <strong>${username}</strong> berhasil dihapus dari sistem`
        );
        loadAdministrators();
      } else {
        showAlert(
          "error",
          "Error",
          response.message || "Gagal menghapus administrator"
        );
      }
    },
    error: function (xhr, status, error) {
      console.error("Error deleting administrator:", error);
      showAlert(
        "error",
        "Error",
        "Terjadi kesalahan saat menghapus administrator"
      );
    },
  });
}

/**
 * Save administrator
 */
function saveUser() {
  if (!validateForm()) {
    return;
  }

  const formData = getFormData();
  const isUpdate = $("#actionType").val() === "update";

  const saveButton = $('[onclick="saveUser()"]');
  const originalHtml = saveButton.html();

  saveButton
    .prop("disabled", true)
    .html('<i class="bi bi-hourglass-split me-2"></i>Menyimpan...');

  $.ajax({
    url: getApiUrl("master-admin-api.php"),
    method: "POST",
    contentType: "application/json",
    data: JSON.stringify(formData),
    dataType: "json",
    timeout: 15000,
    success: function (response) {
      if (response.success) {
        const successMessage = isUpdate
          ? `Administrator <strong>${formData.username}</strong> berhasil diperbarui`
          : `Administrator <strong>${formData.username}</strong> berhasil ditambahkan`;

        showAlert("success", "Berhasil", successMessage);
        $("#userModal").modal("hide");
        loadAdministrators();
      } else {
        showAlert(
          "error",
          "Error",
          response.message || "Gagal menyimpan data administrator"
        );
      }
    },
    error: function (xhr, status, error) {
      console.error("Error saving administrator:", error);

      let errorMessage = "Terjadi kesalahan saat menyimpan data administrator";

      if (status === "timeout") {
        errorMessage = "Koneksi timeout. Silakan coba lagi.";
      } else if (xhr.responseText) {
        try {
          const errorResponse = JSON.parse(xhr.responseText);
          errorMessage = errorResponse.message || errorMessage;
        } catch (e) {
          if (xhr.responseText.length < 200) {
            errorMessage = xhr.responseText;
          }
        }
      }

      showAlert("error", "Error", errorMessage);
    },
    complete: function () {
      saveButton.prop("disabled", false).html(originalHtml);
    },
  });
}

/**
 * Get form data
 */
function getFormData() {
  const data = {
    action: $("#actionType").val(),
    username: $("#username").val().trim(),
    email: $("#email").val().trim().toLowerCase(),
    role: $("#role").val(),
    no_wa: $("#no_wa").val().trim() || null,
    alamat: $("#alamat").val().trim() || null,
  };

  const password = $("#password").val();
  if (password) {
    data.password = password;
  }

  const userId = $("#userId").val();
  if (userId) {
    data.id_user = parseInt(userId);
  }

  return data;
}

/**
 * Validate form
 */
function validateForm() {
  let isValid = true;

  $(".form-control, .custom-select").removeClass("is-invalid is-valid");

  // Username validation
  const username = $("#username").val().trim();
  if (!username) {
    $("#username").addClass("is-invalid");
    isValid = false;
  } else if (username.length < 3) {
    $("#username").addClass("is-invalid");
    showAlert("warning", "Format Username", "Username minimal 3 karakter");
    isValid = false;
  } else if (!/^[a-zA-Z0-9_]+$/.test(username)) {
    $("#username").addClass("is-invalid");
    showAlert(
      "warning",
      "Format Username",
      "Username hanya boleh mengandung huruf, angka, dan underscore"
    );
    isValid = false;
  } else {
    $("#username").addClass("is-valid");
  }

  // Email validation
  const email = $("#email").val().trim().toLowerCase();
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!email || !emailRegex.test(email)) {
    $("#email").addClass("is-invalid");
    isValid = false;
  } else {
    $("#email").addClass("is-valid");
  }

  // Role validation
  const role = $("#role").val();
  if (!role) {
    $("#role").addClass("is-invalid");
    showAlert("warning", "Role Diperlukan", "Role administrator harus dipilih");
    isValid = false;
  } else {
    $("#role").addClass("is-valid");
  }

  // Password validation
  const password = $("#password").val();
  const isCreate = $("#actionType").val() === "create";

  if (isCreate && !password) {
    $("#password").addClass("is-invalid");
    isValid = false;
  } else if (password && password.length < 6) {
    $("#password").addClass("is-invalid");
    showAlert(
      "warning",
      "Password Terlalu Pendek",
      "Password minimal 6 karakter"
    );
    isValid = false;
  } else if (password) {
    $("#password").addClass("is-valid");
  }

  // WhatsApp validation
  const noWa = $("#no_wa").val().trim();
  if (noWa) {
    if (!/^628\d{8,12}$/.test(noWa)) {
      $("#no_wa").addClass("is-invalid");
      showAlert(
        "warning",
        "Format WhatsApp",
        "Format: 628xxxxxxxxxx (8-12 digit setelah 628)"
      );
      isValid = false;
    } else {
      $("#no_wa").addClass("is-valid");
    }
  }

  // Address validation
  const alamat = $("#alamat").val().trim();
  if (alamat && alamat.length > 0) {
    $("#alamat").addClass("is-valid");
  }

  return isValid;
}

/**
 * Setup form validation
 */
function setupFormValidation() {
  // Real-time validation
  $("#username").on("blur input", function () {
    const $this = $(this);
    const value = $this.val().trim();

    $this.removeClass("is-invalid is-valid");

    if (value) {
      if (value.length < 3 || !/^[a-zA-Z0-9_]+$/.test(value)) {
        $this.addClass("is-invalid");
      } else {
        $this.addClass("is-valid");
      }
    }
  });

  $("#email").on("blur input", function () {
    const $this = $(this);
    const value = $this.val().trim().toLowerCase();
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    $this.removeClass("is-invalid is-valid");

    if (value) {
      if (emailRegex.test(value)) {
        $this.addClass("is-valid");
      } else {
        $this.addClass("is-invalid");
      }
    }
  });

  $("#password").on("input", function () {
    const $this = $(this);
    const value = $this.val();

    $this.removeClass("is-invalid is-valid");

    if (value) {
      if (value.length < 6) {
        $this.addClass("is-invalid");
      } else {
        $this.addClass("is-valid");
      }
    }
  });

  $("#no_wa").on("blur input", function () {
    const $this = $(this);
    const value = $this.val().trim();

    $this.removeClass("is-invalid is-valid");

    if (value) {
      if (/^628\d{8,12}$/.test(value)) {
        $this.addClass("is-valid");
      } else {
        $this.addClass("is-invalid");
      }
    }
  });

  $("#role").on("change", function () {
    const $this = $(this);
    $this.removeClass("is-invalid is-valid");

    if ($this.val()) {
      $this.addClass("is-valid");
    }

    updateRoleInfo();
  });

  $("#alamat").on("input", function () {
    const $this = $(this);
    const value = $this.val().trim();

    $this.removeClass("is-invalid is-valid");

    if (value && value.length > 0) {
      $this.addClass("is-valid");
    }
  });
}

/**
 * Update role information with compact styling
 */
function updateRoleInfo() {
  const roleSelect = $("#role");
  const roleInfo = $("#roleInfo");
  const roleInfoContent = $("#roleInfoContent");

  if (roleSelect.val() === "admin") {
    roleInfo.removeClass("role-super-admin-info").addClass("role-admin-info");
    roleInfo.show();
    roleInfoContent.html(`
      <div class="d-flex align-items-center">
        <i class="bi bi-shield-check fs-5 text-brown me-2"></i>
        <div>
          <strong class="text-brown">Admin:</strong> 
          Dapat mengelola trip, peserta, pembayaran, dan galeri.
          <br><small class="text-muted">Tidak dapat mengakses Master Admin.</small>
        </div>
      </div>
    `);
  } else if (roleSelect.val() === "super_admin") {
    roleInfo.removeClass("role-admin-info").addClass("role-super-admin-info");
    roleInfo.show();
    roleInfoContent.html(`
      <div class="d-flex align-items-center">
        <i class="bi bi-shield-exclamation fs-5 text-danger me-2"></i>
        <div>
          <strong class="text-danger">Super Admin:</strong> 
          Memiliki akses penuh ke semua fitur termasuk Master Admin.
          <br><small class="text-danger">Dapat mengelola semua administrator.</small>
        </div>
      </div>
    `);
  } else {
    roleInfo.hide();
  }
}

/**
 * Reset form
 */
function resetForm() {
  $("#userForm")[0].reset();
  $("#userId").val("");
  $("#role").val("");

  $(".form-control, .custom-select").removeClass("is-invalid is-valid");
  $("#roleInfo").hide();
}

/**
 * Show loading
 */
function showLoading(show) {
  if (show) {
    $("#loadingSpinner").fadeIn(300);
    $("#usersTable").fadeOut(300);
  } else {
    $("#loadingSpinner").fadeOut(300);
    $("#usersTable").fadeIn(300);
  }
}

/**
 * Show alert
 */
function showAlert(
  type,
  title,
  message,
  showConfirmButton = true,
  timer = null
) {
  let confirmButtonColor;

  switch (type) {
    case "success":
      confirmButtonColor = "#28a745";
      timer = timer || 3000;
      break;
    case "error":
      confirmButtonColor = "#dc3545";
      break;
    case "warning":
      confirmButtonColor = "#ffc107";
      break;
    case "info":
      confirmButtonColor = "#a97c50";
      timer = timer || 2000;
      break;
    default:
      confirmButtonColor = "#a97c50";
  }

  const config = {
    icon: type,
    title: title,
    html: message,
    confirmButtonColor: confirmButtonColor,
    showConfirmButton: showConfirmButton,
    customClass: {
      popup: "rounded-3",
      title: "fs-6 fw-bold",
      confirmButton: "btn-sm",
    },
    buttonsStyling: false,
    allowOutsideClick: true,
    allowEscapeKey: true,
  };

  if (timer) {
    config.timer = timer;
    config.timerProgressBar = true;
  }

  Swal.fire(config);
}

// Modal event handlers
$("#userModal").on("hidden.bs.modal", function () {
  resetForm();
});

$("#userModal").on("shown.bs.modal", function () {
  $("#username").focus();
});

// Form submission
$("#userForm").on("keypress", function (e) {
  if (e.which === 13 && !$(e.target).is("textarea")) {
    e.preventDefault();
    saveUser();
  }
});

// Auto refresh
let refreshInterval = setInterval(function () {
  if (!$("#userModal").hasClass("show")) {
    loadAdministrators();
  }
}, 30000);

$(window).on("beforeunload", function () {
  clearInterval(refreshInterval);
});
