<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

// Database connection - menggunakan mysqli connection yang sudah ada
require_once 'koneksi.php';

class MasterAdminAPI
{
    private $conn;

    public function __construct($connection)
    {
        $this->conn = $connection;
    }

    // Get all users
    public function getUsers()
    {
        try {
            $query = "SELECT id_user, username, email, role, no_wa, alamat FROM users ORDER BY id_user DESC";
            $result = $this->conn->query($query);

            if (!$result) {
                throw new Exception("Error executing query: " . $this->conn->error);
            }

            $users = [];
            while ($row = $result->fetch_assoc()) {
                $users[] = $row;
            }

            return [
                'success' => true,
                'data' => $users,
                'message' => 'Data pengguna berhasil diambil'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }

    // Get single user
    public function getUser($id)
    {
        try {
            $query = "SELECT id_user, username, email, role, no_wa, alamat FROM users WHERE id_user = ?";
            $stmt = $this->conn->prepare($query);
            
            if (!$stmt) {
                throw new Exception("Error preparing statement: " . $this->conn->error);
            }
            
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            
            $stmt->close();

            if ($user) {
                return [
                    'success' => true,
                    'data' => $user,
                    'message' => 'Data pengguna ditemukan'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Pengguna tidak ditemukan'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }

    // Create new user
    public function createUser($data)
    {
        try {
            // Validasi input
            if (empty($data['username']) || empty($data['email']) || empty($data['password'])) {
                return [
                    'success' => false,
                    'message' => 'Username, email, dan password harus diisi'
                ];
            }

            // Check if username or email already exists
            $checkQuery = "SELECT id_user FROM users WHERE username = ? OR email = ?";
            $checkStmt = $this->conn->prepare($checkQuery);
            
            if (!$checkStmt) {
                throw new Exception("Error preparing check statement: " . $this->conn->error);
            }
            
            $checkStmt->bind_param("ss", $data['username'], $data['email']);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();
            
            if ($checkResult->num_rows > 0) {
                $checkStmt->close();
                return [
                    'success' => false,
                    'message' => 'Username atau email sudah digunakan'
                ];
            }
            $checkStmt->close();

            // Hash password
            $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);

            // Insert new user with admin role
            $query = "INSERT INTO users (username, password, email, role, no_wa, alamat) VALUES (?, ?, ?, 'admin', ?, ?)";
            $stmt = $this->conn->prepare($query);
            
            if (!$stmt) {
                throw new Exception("Error preparing insert statement: " . $this->conn->error);
            }

            // Handle null/empty values for optional fields
            $no_wa = !empty($data['no_wa']) ? $data['no_wa'] : null;
            $alamat = !empty($data['alamat']) ? $data['alamat'] : null;
            
            $stmt->bind_param("sssss", $data['username'], $hashedPassword, $data['email'], $no_wa, $alamat);

            if ($stmt->execute()) {
                $insertId = $this->conn->insert_id;
                $stmt->close();
                
                return [
                    'success' => true,
                    'message' => 'Pengguna admin berhasil ditambahkan',
                    'data' => ['id_user' => $insertId]
                ];
            } else {
                $stmt->close();
                return [
                    'success' => false,
                    'message' => 'Gagal menambahkan pengguna: ' . $this->conn->error
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }

    // Update user
    public function updateUser($id, $data)
    {
        try {
            // Validasi input
            if (empty($data['username']) || empty($data['email'])) {
                return [
                    'success' => false,
                    'message' => 'Username dan email harus diisi'
                ];
            }

            // Check if username or email already exists (exclude current user)
            $checkQuery = "SELECT id_user FROM users WHERE (username = ? OR email = ?) AND id_user != ?";
            $checkStmt = $this->conn->prepare($checkQuery);
            
            if (!$checkStmt) {
                throw new Exception("Error preparing check statement: " . $this->conn->error);
            }
            
            $checkStmt->bind_param("ssi", $data['username'], $data['email'], $id);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();

            if ($checkResult->num_rows > 0) {
                $checkStmt->close();
                return [
                    'success' => false,
                    'message' => 'Username atau email sudah digunakan'
                ];
            }
            $checkStmt->close();

            // Handle null/empty values for optional fields
            $no_wa = !empty($data['no_wa']) ? $data['no_wa'] : null;
            $alamat = !empty($data['alamat']) ? $data['alamat'] : null;

            // Update query
            if (!empty($data['password'])) {
                // Update with password
                $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
                $query = "UPDATE users SET username = ?, password = ?, email = ?, role = 'admin', no_wa = ?, alamat = ? WHERE id_user = ?";
                $stmt = $this->conn->prepare($query);
                
                if (!$stmt) {
                    throw new Exception("Error preparing update statement: " . $this->conn->error);
                }
                
                $stmt->bind_param("sssssi", $data['username'], $hashedPassword, $data['email'], $no_wa, $alamat, $id);
            } else {
                // Update without password
                $query = "UPDATE users SET username = ?, email = ?, role = 'admin', no_wa = ?, alamat = ? WHERE id_user = ?";
                $stmt = $this->conn->prepare($query);
                
                if (!$stmt) {
                    throw new Exception("Error preparing update statement: " . $this->conn->error);
                }
                
                $stmt->bind_param("ssssi", $data['username'], $data['email'], $no_wa, $alamat, $id);
            }

            if ($stmt->execute()) {
                $affectedRows = $stmt->affected_rows;
                $stmt->close();
                
                if ($affectedRows > 0) {
                    return [
                        'success' => true,
                        'message' => 'Data pengguna berhasil diupdate'
                    ];
                } else {
                    return [
                        'success' => false,
                        'message' => 'Tidak ada perubahan data atau pengguna tidak ditemukan'
                    ];
                }
            } else {
                $error = $this->conn->error;
                $stmt->close();
                return [
                    'success' => false,
                    'message' => 'Gagal mengupdate pengguna: ' . $error
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }

    // Delete user
    public function deleteUser($id)
    {
        try {
            // Check if user exists
            $checkQuery = "SELECT id_user FROM users WHERE id_user = ?";
            $checkStmt = $this->conn->prepare($checkQuery);
            
            if (!$checkStmt) {
                throw new Exception("Error preparing check statement: " . $this->conn->error);
            }
            
            $checkStmt->bind_param("i", $id);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();

            if ($checkResult->num_rows == 0) {
                $checkStmt->close();
                return [
                    'success' => false,
                    'message' => 'Pengguna tidak ditemukan'
                ];
            }
            $checkStmt->close();

            // Delete user
            $query = "DELETE FROM users WHERE id_user = ?";
            $stmt = $this->conn->prepare($query);
            
            if (!$stmt) {
                throw new Exception("Error preparing delete statement: " . $this->conn->error);
            }
            
            $stmt->bind_param("i", $id);

            if ($stmt->execute()) {
                $stmt->close();
                return [
                    'success' => true,
                    'message' => 'Pengguna berhasil dihapus'
                ];
            } else {
                $error = $this->conn->error;
                $stmt->close();
                return [
                    'success' => false,
                    'message' => 'Gagal menghapus pengguna: ' . $error
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }
}

// Handle request
try {
    // Gunakan koneksi MySQLi yang sudah ada dari koneksi.php
    $api = new MasterAdminAPI($conn);

    $method = $_SERVER['REQUEST_METHOD'];
    $input = json_decode(file_get_contents('php://input'), true);

    switch ($method) {
        case 'GET':
            if (isset($_GET['id']) && !empty($_GET['id'])) {
                $result = $api->getUser((int)$_GET['id']);
            } else {
                $result = $api->getUsers();
            }
            break;

        case 'POST':
            if (isset($input['action'])) {
                switch ($input['action']) {
                    case 'create':
                        $result = $api->createUser($input);
                        break;
                    case 'update':
                        if (isset($input['id_user']) && !empty($input['id_user'])) {
                            $result = $api->updateUser((int)$input['id_user'], $input);
                        } else {
                            $result = ['success' => false, 'message' => 'ID pengguna tidak ditemukan'];
                        }
                        break;
                    case 'delete':
                        if (isset($input['id_user']) && !empty($input['id_user'])) {
                            $result = $api->deleteUser((int)$input['id_user']);
                        } else {
                            $result = ['success' => false, 'message' => 'ID pengguna tidak ditemukan'];
                        }
                        break;
                    default:
                        $result = ['success' => false, 'message' => 'Action tidak valid'];
                        break;
                }
            } else {
                $result = ['success' => false, 'message' => 'Action tidak ditemukan'];
            }
            break;

        default:
            $result = ['success' => false, 'message' => 'Method tidak didukung'];
            break;
    }

    http_response_code($result['success'] ? 200 : 400);
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
?>
