<?php
require_once 'auth_check.php';
require_once 'sidebar.php';
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
            margin-left: 240px;
            min-height: 100vh;
            padding: 20px 25px;
            background: #f6f0e8;
        }

        .main-content {
            background-color: #f8f9fa;
            min-height: 100vh;
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
            /* Brown border for theme */
            border-radius: 15px 15px 0 0 !important;
            padding: 20px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #a97c50 0%, #8b6332 100%);
            /* Brown gradient */
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

        .btn-warning {
            background-color: #ffc107;
            border: none;
            border-radius: 6px;
            padding: 6px 12px;
        }

        .btn-danger {
            background-color: #dc3545;
            border: none;
            border-radius: 6px;
            padding: 6px 12px;
        }

        .table {
            border-radius: 10px;
            overflow: hidden;
        }

        .table thead th {
            background: linear-gradient(135deg, #a97c50 0%, #8b6332 100%);
            /* Brown gradient */
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

        .modal-header {
            background: linear-gradient(135deg, #a97c50 0%, #8b6332 100%);
            /* Brown gradient */
            color: white;
            border-radius: 15px 15px 0 0;
        }

        .modal-content {
            border-radius: 15px;
            border: none;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        }

        .form-control {
            border-radius: 8px;
            border: 2px solid #e9ecef;
            padding: 12px 15px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #a97c50;
            /* Brown focus for theme */
            box-shadow: 0 0 0 0.2rem rgba(169, 124, 80, 0.25);
        }

        .badge {
            padding: 8px 12px;
            font-size: 0.85em;
            border-radius: 6px;
        }

        .badge.bg-brown {
            background-color: #a97c50 !important;
            /* Brown badge for admin */
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

        /* Custom brown color utilities */
        .text-brown {
            color: #a97c50 !important;
        }

        .bg-brown {
            background-color: #a97c50 !important;
            color: white;
        }

        .border-brown {
            border-color: #a97c50 !important;
        }

        /* DataTables custom styling */
        .dataTables_wrapper .dataTables_filter input {
            border: 2px solid #e9ecef;
            border-radius: 6px;
            transition: all 0.3s ease;
        }

        .dataTables_wrapper .dataTables_filter input:focus {
            border-color: #a97c50;
            box-shadow: 0 0 0 0.2rem rgba(169, 124, 80, 0.25);
            outline: none;
        }

        .dataTables_wrapper .dataTables_length select {
            border: 2px solid #e9ecef;
            border-radius: 6px;
            transition: all 0.3s ease;
        }

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

        @media (max-width: 768px) {
            .content-wrapper {
                padding: 15px;
            }

            .main-header {
                padding: 20px;
            }

            .table-responsive {
                border-radius: 10px;
            }
        }
    </style>
</head>

<body>
        <div class="main">
            <div class="content-wrapper">
                <!-- Page Header -->
                <div class="main-header">
                    <h2>Master Administrator</h2>
                </div>

                <!-- Main Card -->
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 text-brown"><i class="bi bi-table"></i> Data Admin</h5>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#userModal" onclick="openAddModal()">
                                <i class="bi bi-plus-circle"></i> Tambah Admin
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
                                        <th>Id</th>
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

        <!-- User Modal -->
        <div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="userModalLabel">
                            <i class="bi bi-shield-plus"></i> Tambah Administrator Baru
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <form id="userForm">
                            <input type="hidden" id="userId" name="id_user">
                            <input type="hidden" id="actionType" name="action" value="create">

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="username" class="form-label text-brown">
                                            <i class="bi bi-person-badge"></i> Username Administrator *
                                        </label>
                                        <input type="text" class="form-control" id="username" name="username" required>
                                        <div class="invalid-feedback">Username administrator harus diisi dan unik</div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="email" class="form-label text-brown">
                                            <i class="bi bi-envelope-at"></i> Email Administrator *
                                        </label>
                                        <input type="email" class="form-control" id="email" name="email" required>
                                        <div class="invalid-feedback">Email administrator harus diisi dengan format yang benar</div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="password" class="form-label text-brown">
                                            <i class="bi bi-shield-lock"></i> Password Administrator *
                                        </label>
                                        <input type="password" class="form-control" id="password" name="password" required>
                                        <div class="invalid-feedback">Password minimal 6 karakter</div>
                                        <div class="form-text">Kosongkan jika tidak ingin mengubah password (saat edit)</div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="no_wa" class="form-label text-brown">
                                            <i class="bi bi-whatsapp"></i> No. WhatsApp
                                        </label>
                                        <input type="text" class="form-control" id="no_wa" name="no_wa" placeholder="628xxxxxxxxxx">
                                        <div class="form-text">Format: 628xxxxxxxxxx</div>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="alamat" class="form-label text-brown">
                                    <i class="bi bi-geo-alt-fill"></i> Alamat
                                </label>
                                <textarea class="form-control" id="alamat" name="alamat" rows="3" placeholder="Alamat lengkap administrator"></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="role" class="form-label text-brown">
                                    <i class="bi bi-shield-check"></i> Role Administrator
                                </label>
                                <input type="text" class="form-control" id="role" name="role" value="admin" readonly>
                                <div class="form-text">Role otomatis diset sebagai administrator</div>
                            </div>
                        </form>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle"></i> Batal
                        </button>
                        <button type="button" class="btn btn-primary" onclick="saveUser()">
                            <i class="bi bi-save"></i> Simpan
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
</body>

</html>