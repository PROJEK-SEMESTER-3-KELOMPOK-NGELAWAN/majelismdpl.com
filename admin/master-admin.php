<?php
require_once 'auth_check.php';

// Proteksi khusus untuk master admin - hanya super admin yang bisa akses
if (!RoleHelper::canAccessMasterAdmin($user_role)) {
    header("Location: index.php?error=access_denied&message=" . urlencode("Hanya Super Admin yang dapat mengakses Master Admin"));
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Master Admin - Majelis MDPL</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.0/dist/sweetalert2.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">

    <style>
        .main {
            margin-left: 280px;
            min-height: 100vh;
            padding: 20px 25px;
            background: #f6f0e8;
            transition: margin-left 0.3s ease;
        }

        body.sidebar-collapsed .main {
            margin-left: 70px;
        }

        .content-wrapper {
            padding: 30px;
        }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
        }

        .card-header {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            border-bottom: 2px solid #a97c50;
            border-radius: 15px 15px 0 0 !important;
            padding: 20px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #a97c50 0%, #8b6332 100%);
            border: none;
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(169, 124, 80, 0.4);
            background: linear-gradient(135deg, #8b6332 0%, #a97c50 100%);
        }

        /* Compact Modal Styling */
        .modal-dialog {
            max-width: 650px;
            margin: 1.5rem auto;
        }

        .modal-content {
            border-radius: 15px;
            border: none;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            overflow: hidden;
        }

        .modal-header {
            background: linear-gradient(135deg, #a97c50 0%, #8b6332 100%);
            color: white;
            border: none;
            padding: 20px 25px;
        }

        .modal-title {
            font-size: 1.3rem;
            font-weight: 600;
            margin: 0;
        }

        .btn-close {
            filter: invert(1);
            opacity: 0.8;
        }

        .btn-close:hover {
            opacity: 1;
            transform: scale(1.1);
        }

        .modal-body {
            padding: 25px;
            background: #ffffff;
        }

        .modal-footer {
            padding: 20px 25px;
            background: #f8f9fa;
            border: none;
            gap: 10px;
        }

        /* Compact Form Styling */
        .form-group {
            margin-bottom: 1.2rem;
        }

        .form-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }

        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 10px 12px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            height: 42px;
        }

        .form-control:focus {
            border-color: #a97c50;
            box-shadow: 0 0 0 0.2rem rgba(169, 124, 80, 0.15);
            transform: translateY(-1px);
        }

        .form-control.is-valid {
            border-color: #28a745;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%2328a745' d='m2.3 6.73.94-.94 1.44 1.44L7.86 4.05l-.94-.94L4.86 5.17zM2.3 6.73z'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right calc(0.375em + 0.1875rem) center;
            background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
        }

        .form-control.is-invalid {
            border-color: #dc3545;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath d='m5.8 4.6 2.4 2.4M8.2 4.6l-2.4 2.4'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right calc(0.375em + 0.1875rem) center;
            background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
        }

        /* Custom Select Dropdown Styling */
        .custom-select-wrapper {
            position: relative;
        }

        .custom-select {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background: #ffffff;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 10px 45px 10px 40px;
            font-size: 0.95rem;
            font-weight: 500;
            color: #495057;
            cursor: pointer;
            transition: all 0.3s ease;
            height: 42px;
            width: 100%;
            background-image: none;
        }

        .custom-select:focus {
            border-color: #a97c50;
            box-shadow: 0 0 0 0.2rem rgba(169, 124, 80, 0.15);
            outline: none;
            transform: translateY(-1px);
        }

        .custom-select:hover {
            border-color: #a97c50;
        }

        .custom-select.is-valid {
            border-color: #28a745;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%2328a745' d='m2.3 6.73.94-.94 1.44 1.44L7.86 4.05l-.94-.94L4.86 5.17zM2.3 6.73z'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right calc(2.25rem + 0.375rem) center;
            background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
        }

        .custom-select.is-invalid {
            border-color: #dc3545;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath d='m5.8 4.6 2.4 2.4M8.2 4.6l-2.4 2.4'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right calc(2.25rem + 0.375rem) center;
            background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
        }

        .select-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #a97c50;
            font-size: 1.1rem;
            pointer-events: none;
            z-index: 1;
        }

        .select-arrow {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
            font-size: 0.9rem;
            pointer-events: none;
            transition: transform 0.3s ease;
        }

        .custom-select:focus + .select-arrow {
            transform: translateY(-50%) rotate(180deg);
            color: #a97c50;
        }

        .custom-select option {
            padding: 10px 15px;
            font-size: 0.95rem;
            background: #ffffff;
            color: #495057;
        }

        .custom-select option:hover {
            background: #f8f9fa;
        }

        .custom-select option[value="admin"] {
            background: linear-gradient(90deg, rgba(169, 124, 80, 0.1) 0%, transparent 100%);
            color: #a97c50;
            font-weight: 500;
        }

        .custom-select option[value="super_admin"] {
            background: linear-gradient(90deg, rgba(220, 53, 69, 0.1) 0%, transparent 100%);
            color: #dc3545;
            font-weight: 500;
        }

        /* Input Icons */
        .input-with-icon {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
            font-size: 1rem;
            pointer-events: none;
        }

        .input-with-icon .form-control {
            padding-left: 40px;
        }

        .input-with-icon .form-control:focus + .input-icon {
            color: #a97c50;
        }

        /* Role Info Card - Compact */
        .role-info-card {
            background: #f8f9fa;
            border: none;
            border-radius: 8px;
            padding: 15px;
            margin-top: 10px;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .role-info-card.role-admin-info {
            background: linear-gradient(135deg, rgba(169, 124, 80, 0.08) 0%, rgba(169, 124, 80, 0.05) 100%);
            border-left: 3px solid #a97c50;
        }

        .role-info-card.role-super-admin-info {
            background: linear-gradient(135deg, rgba(220, 53, 69, 0.08) 0%, rgba(220, 53, 69, 0.05) 100%);
            border-left: 3px solid #dc3545;
        }

        .form-text {
            font-size: 0.8rem;
            color: #6c757d;
            margin-top: 0.25rem;
        }

        .invalid-feedback {
            font-size: 0.8rem;
            margin-top: 0.25rem;
        }

        /* Table and other existing styles remain the same */
        .table {
            border-radius: 10px;
            overflow: hidden;
        }

        .table thead th {
            background: linear-gradient(135deg, #a97c50 0%, #8b6332 100%);
            color: white;
            font-weight: 600;
            border: none;
            padding: 15px;
        }

        .table tbody td {
            padding: 12px 15px;
            vertical-align: middle;
        }

        .table tbody tr:hover {
            background-color: #f8f9fa;
        }

        .main-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-top: 32px;
            padding-bottom: 28px;
        }

        .main-header h2 {
            font-size: 1.4rem;
            font-weight: 700;
            color: #a97c50;
            margin-bottom: 0;
            letter-spacing: 1px;
        }

        .permission-badge {
            background-color: #28a745;
            color: white;
            font-size: 0.7em;
            padding: 2px 6px;
            border-radius: 4px;
            margin-left: 8px;
        }

        .badge {
            padding: 8px 12px;
            font-size: 0.85em;
            border-radius: 6px;
        }

        .badge.bg-brown {
            background-color: #a97c50 !important;
            color: white;
        }

        .badge.bg-danger {
            background-color: #dc3545 !important;
            color: white;
        }

        .loading-spinner {
            display: none;
            text-align: center;
            padding: 20px;
        }

        .loading-spinner .spinner-border.text-brown {
            color: #a97c50 !important;
        }

        .action-buttons {
            white-space: nowrap;
        }

        .action-buttons .btn {
            margin-right: 5px;
            padding: 5px 10px;
            font-size: 0.85em;
        }

        .text-brown {
            color: #a97c50 !important;
        }

        .bg-brown {
            background-color: #a97c50 !important;
            color: white;
        }

        .btn-warning {
            background-color: #ffc107;
            border: none;
            border-radius: 6px;
        }

        .btn-danger {
            background-color: #dc3545;
            border: none;
            border-radius: 6px;
        }

        .btn-secondary {
            background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-secondary:hover {
            background: linear-gradient(135deg, #5a6268 0%, #495057 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(108, 117, 125, 0.3);
        }

        /* DataTables styling */
        .dataTables_wrapper .dataTables_filter input,
        .dataTables_wrapper .dataTables_length select {
            border: 2px solid #e9ecef;
            border-radius: 6px;
            transition: all 0.3s ease;
        }

        .dataTables_wrapper .dataTables_filter input:focus,
        .dataTables_wrapper .dataTables_length select:focus {
            border-color: #a97c50;
            box-shadow: 0 0 0 0.2rem rgba(169, 124, 80, 0.25);
            outline: none;
        }

        .page-link {
            color: #a97c50;
        }

        .page-link:hover {
            color: #8b6332;
            background-color: rgba(169, 124, 80, 0.1);
            border-color: #a97c50;
        }

        .page-item.active .page-link {
            background-color: #a97c50;
            border-color: #a97c50;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .main {
                margin-left: 0 !important;
            }

            .content-wrapper {
                padding: 15px;
            }

            .modal-dialog {
                margin: 1rem;
                max-width: calc(100vw - 2rem);
            }

            .modal-body {
                padding: 20px 15px;
            }
        }
    </style>
</head>

<body>
    <!-- Include Sidebar -->
    <?php include 'sidebar.php'; ?>

    <div class="main">
        <div class="content-wrapper">
            <!-- Page Header -->
            <div class="main-header">
                <div>
                    <h2>Master Administrator</h2>
                    <small class="text-muted">
                        <i class="bi bi-shield-check"></i> 
                        Kelola Admin & Super Admin
                        <span class="permission-badge">
                            <?= RoleHelper::getRoleDisplayName($user_role) ?>
                        </span>
                    </small>
                </div>
            </div>

            <!-- Main Card -->
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 text-brown">
                            <i class="bi bi-people-fill"></i> Data Administrator
                        </h5>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#userModal" onclick="openAddModal()">
                            <i class="bi bi-plus-circle"></i> Tambah Administrator
                        </button>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Loading Spinner -->
                    <div class="loading-spinner" id="loadingSpinner">
                        <div class="spinner-border text-brown" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2 text-brown">Memuat data administrator...</p>
                    </div>

                    <!-- Data Table -->
                    <div class="table-responsive">
                        <table class="table table-striped" id="usersTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>No. WhatsApp</th>
                                    <th>Alamat</th>
                                    <th>Role</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Data akan dimuat via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Compact User Modal -->
    <div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="userModalLabel">
                        <i class="bi bi-shield-plus"></i> <span id="modalTitle">Tambah Administrator Baru</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <form id="userForm">
                        <input type="hidden" id="userId" name="id_user">
                        <input type="hidden" id="actionType" name="action" value="create">

                        <div class="row">
                            <!-- Username -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="username" class="form-label">
                                        <i class="bi bi-person-circle me-1"></i> Username
                                    </label>
                                    <div class="input-with-icon">
                                        <input type="text" class="form-control" id="username" name="username" placeholder="Masukkan username" required>
                                        <i class="bi bi-person-badge input-icon"></i>
                                    </div>
                                    <div class="invalid-feedback">Username harus diisi (minimal 3 karakter)</div>
                                </div>
                            </div>

                            <!-- Email -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email" class="form-label">
                                        <i class="bi bi-envelope-at me-1"></i> Email
                                    </label>
                                    <div class="input-with-icon">
                                        <input type="email" class="form-control" id="email" name="email" placeholder="admin@example.com" required>
                                        <i class="bi bi-envelope input-icon"></i>
                                    </div>
                                    <div class="invalid-feedback">Email harus berformat yang benar</div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Password -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="password" class="form-label">
                                        <i class="bi bi-shield-lock me-1"></i> Password
                                    </label>
                                    <div class="input-with-icon">
                                        <input type="password" class="form-control" id="password" name="password" placeholder="Minimal 6 karakter" required>
                                        <i class="bi bi-key input-icon"></i>
                                    </div>
                                    <div class="form-text">Kosongkan jika tidak ingin mengubah (saat edit)</div>
                                    <div class="invalid-feedback">Password minimal 6 karakter</div>
                                </div>
                            </div>

                            <!-- Role -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="role" class="form-label">
                                        <i class="bi bi-shield-check me-1"></i> Role Administrator
                                    </label>
                                    <div class="custom-select-wrapper">
                                        <i class="bi bi-shield-check select-icon"></i>
                                        <select class="custom-select" id="role" name="role" required onchange="updateRoleInfo()">
                                            <option value="">Pilih Role Administrator</option>
                                            <?php foreach (RoleHelper::getAdminRoles() as $roleKey => $roleName): ?>
                                                <option value="<?= htmlspecialchars($roleKey) ?>">
                                                    <?= $roleKey === 'admin' ? '👤 ' : '⚡ ' ?><?= htmlspecialchars($roleName) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <i class="bi bi-chevron-down select-arrow"></i>
                                    </div>
                                    <div class="invalid-feedback">Role administrator harus dipilih</div>
                                </div>
                            </div>
                        </div>

                        <!-- Role Information -->
                        <div id="roleInfo" class="role-info-card" style="display: none;">
                            <div id="roleInfoContent"></div>
                        </div>

                        <div class="row">
                            <!-- WhatsApp -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="no_wa" class="form-label">
                                        <i class="bi bi-whatsapp me-1"></i> No. WhatsApp <small class="text-muted">(opsional)</small>
                                    </label>
                                    <div class="input-with-icon">
                                        <input type="text" class="form-control" id="no_wa" name="no_wa" placeholder="628xxxxxxxxxx">
                                        <i class="bi bi-phone input-icon"></i>
                                    </div>
                                    <div class="form-text">Format: 628xxxxxxxxxx</div>
                                </div>
                            </div>

                            <!-- Alamat -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="alamat" class="form-label">
                                        <i class="bi bi-geo-alt me-1"></i> Alamat <small class="text-muted">(opsional)</small>
                                    </label>
                                    <div class="input-with-icon">
                                        <input type="text" class="form-control" id="alamat" name="alamat" placeholder="Alamat lengkap">
                                        <i class="bi bi-house input-icon"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i> Batal
                    </button>
                    <button type="button" class="btn btn-primary" onclick="saveUser()">
                        <i class="bi bi-save me-1"></i> <span id="saveButtonText">Simpan</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../frontend/master-admin.js"></script>

    <script>
        // Function untuk update role information
        function updateRoleInfo() {
            const roleSelect = document.getElementById('role');
            const roleInfo = document.getElementById('roleInfo');
            const roleInfoContent = document.getElementById('roleInfoContent');
            
            if (roleSelect.value === 'admin') {
                roleInfo.className = 'role-info-card role-admin-info';
                roleInfo.style.display = 'block';
                roleInfoContent.innerHTML = `
                    <div class="d-flex align-items-center">
                        <i class="bi bi-shield-check fs-5 text-brown me-2"></i>
                        <div>
                            <strong class="text-brown">Admin:</strong> 
                            Dapat mengelola trip, peserta, pembayaran, dan galeri.
                            <br><small class="text-muted">Tidak dapat mengakses Master Admin.</small>
                        </div>
                    </div>
                `;
            } else if (roleSelect.value === 'super_admin') {
                roleInfo.className = 'role-info-card role-super-admin-info';
                roleInfo.style.display = 'block';
                roleInfoContent.innerHTML = `
                    <div class="d-flex align-items-center">
                        <i class="bi bi-shield-exclamation fs-5 text-danger me-2"></i>
                        <div>
                            <strong class="text-danger">Super Admin:</strong> 
                            Memiliki akses penuh ke semua fitur termasuk Master Admin.
                            <br><small class="text-danger">Dapat mengelola semua administrator.</small>
                        </div>
                    </div>
                `;
            } else {
                roleInfo.style.display = 'none';
            }
        }

        // Initialize role info on page load
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('role').addEventListener('change', updateRoleInfo);
        });
    </script>
</body>
</html>
