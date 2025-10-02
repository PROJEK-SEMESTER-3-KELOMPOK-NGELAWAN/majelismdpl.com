/**
 * Master Admin JavaScript
 * Handles CRUD operations for administrator management - Filter hanya role 'admin'
 * Theme Color: #a97c50 (Brown)
 */

$(document).ready(function () {
  // Initialize DataTable
  const usersTable = $("#usersTable").DataTable({
    processing: true,
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
      { orderable: false, targets: [6] }, // Disable ordering on action column
      { className: "text-center", targets: [0, 5, 6] },
    ],
    order: [[0, "desc"]], // Sort by ID descending
  });

  // Load administrators data on page load
  loadAdministrators();

  // Form validation
  setupFormValidation();
});

/**
 * Load all administrators from API - Filter hanya role 'admin'
 */
function loadAdministrators() {
  showLoading(true);

  $.ajax({
    url: "../backend/master-admin-api.php",
    method: "GET",
    dataType: "json",
    success: function (response) {
      showLoading(false);

      if (response.success) {
        // Filter hanya user dengan role 'admin'
        const administrators = response.data.filter(function(user) {
          return user.role === 'admin';
        });
        
        populateAdministratorsTable(administrators);
        
        // Show info message if no administrators found
        if (administrators.length === 0) {
          showAlert("info", "Informasi", "Belum ada administrator yang terdaftar");
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
      showAlert(
        "error",
        "Error",
        "Terjadi kesalahan saat memuat data administrator"
      );
    },
  });
}

/**
 * Populate administrators table with data - Hanya admin role
 */
function populateAdministratorsTable(administrators) {
  const table = $("#usersTable").DataTable();
  table.clear();

  // Validasi tambahan untuk memastikan hanya role 'admin' yang ditampilkan
  const adminRoleOnly = administrators.filter(user => user.role === 'admin');

  adminRoleOnly.forEach(function (admin, index) {
    const row = [
      admin.id_user,
      admin.username || "-",
      admin.email || "-",
      admin.no_wa || "-",
      admin.alamat || "-",
      `<span class="badge bg-brown"><i class="bi bi-shield-check"></i> ${admin.role}</span>`, // Brown badge for admin
      `<div class="action-buttons">
                <button type="button" class="btn btn-warning btn-sm" onclick="editAdministrator(${admin.id_user})" title="Edit Administrator">
                    <i class="bi bi-pencil-square"></i>
                </button>
                <button type="button" class="btn btn-danger btn-sm" onclick="deleteAdministrator(${admin.id_user})" title="Hapus Administrator">
                    <i class="bi bi-trash3"></i>
                </button>
            </div>`,
    ];

    table.row.add(row);
  });

  table.draw();
  
  // Tampilkan info jumlah administrator
  console.log(`Menampilkan ${adminRoleOnly.length} administrator dengan role 'admin'`);
  
  // Update page info
  if (adminRoleOnly.length > 0) {
    $('.dataTables_info').html(`Menampilkan ${adminRoleOnly.length} administrator`);
  }
}

/**
 * Open modal for adding new administrator
 */
function openAddModal() {
  resetForm();
  $("#userModalLabel").html(
    '<i class="bi bi-shield-plus"></i> Tambah Administrator Baru'
  );
  $("#actionType").val("create");
  $("#password").prop("required", true);
  $("#role").val("admin"); // Set default role ke 'admin'
  $(".form-text").show();
}

/**
 * Edit administrator - load administrator data and open modal
 */
function editAdministrator(userId) {
  $.ajax({
    url: "../backend/master-admin-api.php",
    method: "GET",
    data: { id: userId },
    dataType: "json",
    success: function (response) {
      if (response.success) {
        const admin = response.data;
        
        // Validasi bahwa user yang diedit adalah role 'admin'
        if (admin.role !== 'admin') {
          showAlert(
            "error",
            "Error",
            "Anda hanya dapat mengedit pengguna dengan role 'admin'"
          );
          return;
        }

        // Populate form
        $("#userId").val(admin.id_user);
        $("#username").val(admin.username);
        $("#email").val(admin.email);
        $("#no_wa").val(admin.no_wa);
        $("#alamat").val(admin.alamat);
        $("#role").val("admin"); // Force role ke 'admin'
        $("#password").val("");

        // Update modal
        $("#userModalLabel").html('<i class="bi bi-shield-exclamation"></i> Edit Administrator');
        $("#actionType").val("update");
        $("#password").prop("required", false);
        $(".form-text").show();

        // Show modal
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
 * Delete administrator with confirmation
 */
function deleteAdministrator(userId) {
  // Validasi sebelum menghapus - pastikan hanya menghapus user dengan role 'admin'
  $.ajax({
    url: "../backend/master-admin-api.php",
    method: "GET",
    data: { id: userId },
    dataType: "json",
    success: function (response) {
      if (response.success && response.data.role === 'admin') {
        Swal.fire({
          title: "Konfirmasi Hapus Administrator",
          text: `Apakah Anda yakin ingin menghapus administrator "${response.data.username}"?`,
          icon: "warning",
          showCancelButton: true,
          confirmButtonColor: "#dc3545",
          cancelButtonColor: "#6c757d",
          confirmButtonText: "Ya, Hapus Administrator",
          cancelButtonText: "Batal",
          reverseButtons: true,
        }).then((result) => {
          if (result.isConfirmed) {
            performDelete(userId);
          }
        });
      } else {
        showAlert(
          "error",
          "Error",
          "Anda hanya dapat menghapus pengguna dengan role 'admin'"
        );
      }
    },
    error: function (xhr, status, error) {
      showAlert("error", "Error", "Terjadi kesalahan saat validasi administrator");
    }
  });
}

/**
 * Perform delete operation
 */
function performDelete(userId) {
  $.ajax({
    url: "../backend/master-admin-api.php",
    method: "POST",
    contentType: "application/json",
    data: JSON.stringify({
      action: "delete",
      id_user: userId,
    }),
    dataType: "json",
    success: function (response) {
      if (response.success) {
        showAlert("success", "Berhasil", "Administrator berhasil dihapus");
        loadAdministrators(); // Reload table
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
      showAlert("error", "Error", "Terjadi kesalahan saat menghapus administrator");
    },
  });
}

/**
 * Save administrator (create or update)
 */
function saveUser() {
  if (!validateForm()) {
    return;
  }

  const formData = getFormData();

  $.ajax({
    url: "../backend/master-admin-api.php",
    method: "POST",
    contentType: "application/json",
    data: JSON.stringify(formData),
    dataType: "json",
    beforeSend: function () {
      // Disable save button
      $('[onclick="saveUser()"]')
        .prop("disabled", true)
        .html('<i class="bi bi-hourglass-split"></i> Menyimpan...');
    },
    success: function (response) {
      if (response.success) {
        const actionText = formData.action === 'create' ? 'ditambahkan' : 'diupdate';
        showAlert("success", "Berhasil", `Administrator berhasil ${actionText}`);
        $("#userModal").modal("hide");
        loadAdministrators(); // Reload table
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
      showAlert(
        "error",
        "Error",
        "Terjadi kesalahan saat menyimpan data administrator"
      );
    },
    complete: function () {
      // Re-enable save button
      $('[onclick="saveUser()"]')
        .prop("disabled", false)
        .html('<i class="bi bi-save"></i> Simpan');
    },
  });
}

/**
 * Get form data - Force role ke 'admin'
 */
function getFormData() {
  return {
    action: $("#actionType").val(),
    id_user: $("#userId").val() || null,
    username: $("#username").val().trim(),
    email: $("#email").val().trim(),
    password: $("#password").val(),
    no_wa: $("#no_wa").val().trim(),
    alamat: $("#alamat").val().trim(),
    role: "admin", // Always set to 'admin'
  };
}

/**
 * Validate form for administrator data
 */
function validateForm() {
  let isValid = true;

  // Clear previous validation
  $(".form-control").removeClass("is-invalid");

  // Username validation
  const username = $("#username").val().trim();
  if (!username) {
    $("#username").addClass("is-invalid");
    isValid = false;
  } else if (username.length < 3) {
    $("#username").addClass("is-invalid");
    showAlert("warning", "Peringatan", "Username administrator minimal 3 karakter");
    isValid = false;
  }

  // Email validation
  const email = $("#email").val().trim();
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!email || !emailRegex.test(email)) {
    $("#email").addClass("is-invalid");
    isValid = false;
  }

  // Password validation (required for create, optional for update)
  if ($("#actionType").val() === "create" && !$("#password").val()) {
    $("#password").addClass("is-invalid");
    isValid = false;
  } else if ($("#password").val() && $("#password").val().length < 6) {
    $("#password").addClass("is-invalid");
    showAlert("warning", "Peringatan", "Password administrator minimal 6 karakter");
    isValid = false;
  }

  // WhatsApp number validation (optional but must be valid format if provided)
  const noWa = $("#no_wa").val().trim();
  if (noWa && !/^628\d{8,12}$/.test(noWa)) {
    $("#no_wa").addClass("is-invalid");
    showAlert(
      "warning",
      "Format Nomor WhatsApp",
      "Format nomor WhatsApp harus 628xxxxxxxxxx (8-12 digit setelah 628)"
    );
    isValid = false;
  }

  return isValid;
}

/**
 * Setup form validation
 */
function setupFormValidation() {
  // Real-time validation
  $("#username, #email, #password, #no_wa").on("blur", function () {
    $(this).removeClass("is-invalid");

    if ($(this).attr("id") === "username") {
      const username = $(this).val().trim();
      if (username && username.length < 3) {
        $(this).addClass("is-invalid");
      }
    }

    if ($(this).attr("id") === "email") {
      const email = $(this).val().trim();
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (email && !emailRegex.test(email)) {
        $(this).addClass("is-invalid");
      }
    }

    if ($(this).attr("id") === "password") {
      const password = $(this).val();
      if (password && password.length < 6) {
        $(this).addClass("is-invalid");
      }
    }

    if ($(this).attr("id") === "no_wa") {
      const noWa = $(this).val().trim();
      if (noWa && !/^628\d{8,12}$/.test(noWa)) {
        $(this).addClass("is-invalid");
      }
    }
  });
}

/**
 * Reset form - Set default role ke 'admin'
 */
function resetForm() {
  $("#userForm")[0].reset();
  $("#userId").val("");
  $("#role").val("admin"); // Set default role ke 'admin'
  $(".form-control").removeClass("is-invalid");
}

/**
 * Show loading spinner
 */
function showLoading(show) {
  if (show) {
    $("#loadingSpinner").show();
    $("#usersTable").hide();
  } else {
    $("#loadingSpinner").hide();
    $("#usersTable").show();
  }
}

/**
 * Show alert using SweetAlert2 with brown theme colors
 */
function showAlert(type, title, message) {
  let confirmButtonColor;
  
  switch(type) {
    case 'success':
      confirmButtonColor = '#28a745';
      break;
    case 'error':
      confirmButtonColor = '#dc3545';
      break;
    case 'warning':
      confirmButtonColor = '#ffc107';
      break;
    case 'info':
      confirmButtonColor = '#a97c50'; // Brown color for info
      break;
    default:
      confirmButtonColor = '#a97c50'; // Default brown color
  }
  
  Swal.fire({
    icon: type,
    title: title,
    text: message,
    confirmButtonColor: confirmButtonColor,
    timer: type === "success" ? 3000 : null,
    timerProgressBar: true,
  });
}

// Handle modal events
$("#userModal").on("hidden.bs.modal", function () {
  resetForm();
});

// Handle Enter key in form
$("#userForm").on("keypress", function (e) {
  if (e.which === 13) {
    // Enter key
    e.preventDefault();
    saveUser();
  }
});

// Auto refresh data every 30 seconds (optional)
setInterval(function() {
  loadAdministrators();
}, 30000);
