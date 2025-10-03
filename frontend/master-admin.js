/**
 * Master Admin JavaScript
 * Handles CRUD operations for administrator management - Manage both 'admin' and 'super_admin' roles
 * Theme Color: #a97c50 (Brown)
 * Super Admin can manage all administrators
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
 * Load all administrators from API - Include both 'admin' and 'super_admin' roles
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
        // Filter hanya user dengan role 'admin' dan 'super_admin'
        const administrators = response.data.filter(function(user) {
          return user.role === 'admin' || user.role === 'super_admin';
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
      console.error("XHR:", xhr);
      showAlert(
        "error",
        "Error",
        "Terjadi kesalahan saat memuat data administrator"
      );
    },
  });
}

/**
 * Populate administrators table with data - Include both admin and super_admin roles
 */
function populateAdministratorsTable(administrators) {
  const table = $("#usersTable").DataTable();
  table.clear();

  // Validasi untuk memastikan hanya role 'admin' dan 'super_admin' yang ditampilkan
  const adminRoles = administrators.filter(user => 
    user.role === 'admin' || user.role === 'super_admin'
  );

  adminRoles.forEach(function (admin, index) {
    // Determine badge class based on role
    let badgeClass = 'bg-brown';
    let badgeIcon = 'bi-shield-check';
    
    if (admin.role === 'super_admin') {
      badgeClass = 'bg-danger';
      badgeIcon = 'bi-shield-exclamation';
    }

    const row = [
      admin.id_user,
      admin.username || "-",
      admin.email || "-",
      admin.no_wa || "-",
      admin.alamat || "-",
      `<span class="badge ${badgeClass}">
        <i class="bi ${badgeIcon}"></i> 
        ${admin.role === 'admin' ? 'Admin' : 'Super Admin'}
      </span>`,
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
    
  // Update page info
  if (adminRoles.length > 0) {
    const adminCount = adminRoles.filter(u => u.role === 'admin').length;
    const superAdminCount = adminRoles.filter(u => u.role === 'super_admin').length;
    $('.dataTables_info').html(
      `Menampilkan ${adminRoles.length} administrator (${adminCount} Admin, ${superAdminCount} Super Admin)`
    );
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
  $("#modalTitle").text("Tambah Administrator Baru");
  $("#saveButtonText").text("Simpan");
  $("#actionType").val("create");
  $("#password").prop("required", true);
  $("#role").val(""); // Reset role selection
  $(".form-text").show();
  
  // Hide role info initially
  $("#roleInfo").hide();
  
  // Reset validation states
  $(".form-control, .form-select").removeClass("is-invalid");
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
        
        // Validasi bahwa user yang diedit adalah role 'admin' atau 'super_admin'
        if (admin.role !== 'admin' && admin.role !== 'super_admin') {
          showAlert(
            "error",
            "Error",
            "Anda hanya dapat mengedit pengguna dengan role Administrator"
          );
          return;
        }

        // Populate form
        $("#userId").val(admin.id_user);
        $("#username").val(admin.username);
        $("#email").val(admin.email);
        $("#no_wa").val(admin.no_wa || '');
        $("#alamat").val(admin.alamat || '');
        $("#role").val(admin.role);
        $("#password").val("");

        // Update modal
        $("#userModalLabel").html('<i class="bi bi-shield-exclamation"></i> Edit Administrator');
        $("#modalTitle").text("Edit Administrator");
        $("#saveButtonText").text("Update");
        $("#actionType").val("update");
        $("#password").prop("required", false);
        $(".form-text").show();

        // Update role info
        updateRoleInfo();

        // Reset validation states
        $(".form-control, .form-select").removeClass("is-invalid");

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
      console.error("XHR:", xhr);
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
  // Validasi sebelum menghapus - pastikan hanya menghapus admin roles
  $.ajax({
    url: "../backend/master-admin-api.php",
    method: "GET",
    data: { id: userId },
    dataType: "json",
    success: function (response) {
      if (response.success && (response.data.role === 'admin' || response.data.role === 'super_admin')) {
        const roleDisplay = response.data.role === 'admin' ? 'Admin' : 'Super Admin';
        
        Swal.fire({
          title: `Konfirmasi Hapus ${roleDisplay}`,
          text: `Apakah Anda yakin ingin menghapus ${roleDisplay.toLowerCase()} "${response.data.username}"?`,
          icon: "warning",
          showCancelButton: true,
          confirmButtonColor: "#dc3545",
          cancelButtonColor: "#6c757d",
          confirmButtonText: `Ya, Hapus ${roleDisplay}`,
          cancelButtonText: "Batal",
          reverseButtons: true,
          html: `
            <p>Apakah Anda yakin ingin menghapus ${roleDisplay.toLowerCase()} <strong>"${response.data.username}"</strong>?</p>
            <div class="alert alert-warning mt-3">
              <i class="bi bi-exclamation-triangle"></i> 
              ${response.data.role === 'super_admin' ? 
                'Menghapus Super Admin akan menghilangkan akses penuh ke sistem!' : 
                'Tindakan ini tidak dapat dibatalkan!'
              }
            </div>
          `
        }).then((result) => {
          if (result.isConfirmed) {
            performDelete(userId);
          }
        });
      } else {
        showAlert(
          "error",
          "Error",
          "Anda hanya dapat menghapus pengguna dengan role Administrator"
        );
      }
    },
    error: function (xhr, status, error) {
      console.error("Error validating administrator:", error);
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
        showAlert("success", "Berhasil", response.message);
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
      console.error("XHR:", xhr);
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
  
  // Debug: Log form data
  console.log("Form Data:", formData);

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
      console.log("Save Response:", response);
      if (response.success) {
        showAlert("success", "Berhasil", response.message);
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
      console.error("XHR Response:", xhr.responseText);
      console.error("Status Code:", xhr.status);
      
      let errorMessage = "Terjadi kesalahan saat menyimpan data administrator";
      
      if (xhr.responseText) {
        try {
          const errorResponse = JSON.parse(xhr.responseText);
          errorMessage = errorResponse.message || errorMessage;
        } catch (e) {
          errorMessage = xhr.responseText;
        }
      }
      
      showAlert("error", "Error", errorMessage);
    },
    complete: function () {
      // Re-enable save button
      $('[onclick="saveUser()"]')
        .prop("disabled", false)
        .html('<i class="bi bi-save"></i> <span id="saveButtonText">Simpan</span>');
    },
  });
}

/**
 * Get form data - Allow both 'admin' and 'super_admin' roles
 */
function getFormData() {
  const data = {
    action: $("#actionType").val(),
    username: $("#username").val().trim(),
    email: $("#email").val().trim(),
    role: $("#role").val(), // Allow selected role (admin or super_admin)
    no_wa: $("#no_wa").val().trim() || null,
    alamat: $("#alamat").val().trim() || null,
  };

  // Add password only if not empty
  const password = $("#password").val();
  if (password) {
    data.password = password;
  }

  // Add ID only for update action
  const userId = $("#userId").val();
  if (userId) {
    data.id_user = parseInt(userId);
  }

  return data;
}

/**
 * Validate form for administrator data
 */
function validateForm() {
  let isValid = true;

  // Clear previous validation
  $(".form-control, .form-select").removeClass("is-invalid");

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

  // Role validation
  const role = $("#role").val();
  if (!role) {
    $("#role").addClass("is-invalid");
    showAlert("warning", "Peringatan", "Role administrator harus dipilih");
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
 * Update role information display
 */
function updateRoleInfo() {
  const roleSelect = $("#role");
  const roleInfo = $("#roleInfo");
  const roleInfoContent = $("#roleInfoContent");
  
  if (roleSelect.val() === 'admin') {
    roleInfo.removeClass('role-super-admin-info').addClass('role-admin-info');
    roleInfo.show();
    roleInfoContent.html(`
      <strong><i class="bi bi-shield-check"></i> Admin:</strong> 
      Dapat mengelola trip, peserta, pembayaran, dan galeri. 
      <em class="text-muted">Tidak dapat mengakses Master Admin.</em>
    `);
  } else if (roleSelect.val() === 'super_admin') {
    roleInfo.removeClass('role-admin-info').addClass('role-super-admin-info');
    roleInfo.show();
    roleInfoContent.html(`
      <strong><i class="bi bi-shield-exclamation"></i> Super Admin:</strong> 
      Memiliki akses penuh ke semua fitur termasuk Master Admin. 
      <em class="text-danger">Dapat mengelola semua administrator.</em>
    `);
  } else {
    roleInfo.hide();
  }
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

  // Role change validation
  $("#role").on("change", function () {
    $(this).removeClass("is-invalid");
    updateRoleInfo();
  });
}

/**
 * Reset form - Clear all fields and validation
 */
function resetForm() {
  $("#userForm")[0].reset();
  $("#userId").val("");
  $("#role").val(""); // Reset role selection
  $(".form-control, .form-select").removeClass("is-invalid");
  $("#roleInfo").hide();
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
