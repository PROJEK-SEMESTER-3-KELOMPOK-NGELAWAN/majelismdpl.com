<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'koneksi.php';

// Logging aktivitas, selalu simpan username target hapus di kolom aktivitas
function log_activity($conn, $aktivitas, $status, $id_user)
{
    $query = "INSERT INTO activity_logs (aktivitas, waktu, status, id_user) VALUES (?, NOW(), ?, ?)";
    $stmt = $conn->prepare($query);
    if ($stmt) {
        $stmt->bind_param("ssi", $aktivitas, $status, $id_user);
        $stmt->execute();
        $stmt->close();
    }
}

function get_users($conn)
{
    $query = "SELECT id_user, username, email, role, no_wa, alamat FROM users WHERE role IN ('admin','super_admin') ORDER BY id_user DESC";
    $result = $conn->query($query);
    if (!$result) return ['success' => false, 'message' => 'Error executing query: ' . $conn->error];
    $users = [];
    while ($row = $result->fetch_assoc()) $users[] = $row;
    return ['success' => true, 'data' => $users, 'message' => 'Data pengguna berhasil diambil'];
}

function get_user($conn, $id)
{
    $query = "SELECT id_user, username, email, role, no_wa, alamat FROM users WHERE id_user = ?";
    $stmt = $conn->prepare($query);
    if (!$stmt) return ['success' => false, 'message' => 'Error preparing statement: ' . $conn->error];
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    if ($user) return ['success' => true, 'data' => $user, 'message' => 'Data pengguna ditemukan'];
    else return ['success' => false, 'message' => 'Pengguna tidak ditemukan'];
}

function create_user($conn, $data)
{
    if (empty($data['username']) || empty($data['email']) || empty($data['password']) || empty($data['role']))
        return ['success' => false, 'message' => 'Username, email, password, dan role harus diisi'];
    $allowed_roles = ['admin', 'super_admin'];
    if (!in_array($data['role'], $allowed_roles))
        return ['success' => false, 'message' => 'Role tidak valid. Hanya admin atau super_admin yang diperbolehkan'];
    $check = $conn->prepare("SELECT id_user FROM users WHERE username = ? OR email = ?");
    $check->bind_param("ss", $data['username'], $data['email']);
    $check->execute();
    $res = $check->get_result();
    if ($res->num_rows > 0) {
        $check->close();
        return ['success' => false, 'message' => 'Username atau email sudah digunakan'];
    }
    $check->close();
    $hashed = password_hash($data['password'], PASSWORD_DEFAULT);
    $no_wa = !empty($data['no_wa']) ? $data['no_wa'] : null;
    $alamat = !empty($data['alamat']) ? $data['alamat'] : null;
    $stmt = $conn->prepare("INSERT INTO users (username, password, email, role, no_wa, alamat) VALUES (?, ?, ?, ?, ?, ?)");
    if (!$stmt) return ['success' => false, 'message' => 'Error preparing insert statement: ' . $conn->error];
    $stmt->bind_param("ssssss", $data['username'], $hashed, $data['email'], $data['role'], $no_wa, $alamat);
    if ($stmt->execute()) {
        $insertId = $conn->insert_id;
        $stmt->close();
        $roleDisplay = $data['role'] == 'admin' ? 'Admin' : 'Super Admin';
        log_activity($conn, "Menambahkan $roleDisplay baru (username: {$data['username']})", "success", $insertId);
        return ['success' => true, 'message' => $roleDisplay . ' berhasil ditambahkan', 'data' => ['id_user' => $insertId]];
    } else {
        $stmt->close();
        return ['success' => false, 'message' => 'Gagal menambahkan pengguna: ' . $conn->error];
    }
}

function update_user($conn, $id, $data)
{
    if (empty($data['username']) || empty($data['email']) || empty($data['role']))
        return ['success' => false, 'message' => 'Username, email, dan role harus diisi'];
    $allowed = ['admin', 'super_admin'];
    if (!in_array($data['role'], $allowed))
        return ['success' => false, 'message' => 'Role tidak valid. Hanya admin atau super_admin yang diperbolehkan'];
    $check = $conn->prepare("SELECT id_user FROM users WHERE (username = ? OR email = ?) AND id_user != ?");
    $check->bind_param("ssi", $data['username'], $data['email'], $id);
    $check->execute();
    $res = $check->get_result();
    if ($res->num_rows > 0) {
        $check->close();
        return ['success' => false, 'message' => 'Username atau email sudah digunakan'];
    }
    $check->close();
    $no_wa = !empty($data['no_wa']) ? $data['no_wa'] : null;
    $alamat = !empty($data['alamat']) ? $data['alamat'] : null;
    if (!empty($data['password'])) {
        $hashed = password_hash($data['password'], PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET username=?, password=?, email=?, role=?, no_wa=?, alamat=? WHERE id_user=?");
        if (!$stmt) return ['success' => false, 'message' => 'Error preparing update statement: ' . $conn->error];
        $stmt->bind_param("ssssssi", $data['username'], $hashed, $data['email'], $data['role'], $no_wa, $alamat, $id);
    } else {
        $stmt = $conn->prepare("UPDATE users SET username=?, email=?, role=?, no_wa=?, alamat=? WHERE id_user=?");
        if (!$stmt) return ['success' => false, 'message' => 'Error preparing update statement: ' . $conn->error];
        $stmt->bind_param("sssssi", $data['username'], $data['email'], $data['role'], $no_wa, $alamat, $id);
    }
    if ($stmt->execute()) {
        $affected = $stmt->affected_rows;
        $stmt->close();
        if ($affected > 0) {
            $roleDisplay = $data['role'] == 'admin' ? 'Admin' : 'Super Admin';
            log_activity($conn, "Data $roleDisplay (username: {$data['username']}) diupdate", "update", $id);
            return ['success' => true, 'message' => 'Data ' . $roleDisplay . ' berhasil diupdate'];
        } else {
            return ['success' => false, 'message' => 'Tidak ada perubahan data atau pengguna tidak ditemukan'];
        }
    } else {
        $err = $conn->error;
        $stmt->close();
        return ['success' => false, 'message' => 'Gagal mengupdate pengguna: ' . $err];
    }
}

// --- Bagian delete_user: log selalu simpan detail nama di kolom aktivitas ---
function delete_user($conn, $id)
{
    $q = $conn->prepare("SELECT id_user, role, username FROM users WHERE id_user=?");
    $q->bind_param("i", $id);
    $q->execute();
    $r = $q->get_result();
    if ($r->num_rows == 0) {
        $q->close();
        return ['success' => false, 'message' => 'Pengguna tidak ditemukan'];
    }
    $user = $r->fetch_assoc();
    $q->close();
    $roleDisplay = $user['role'] == 'admin' ? 'Admin' : 'Super Admin';

    // Aktivitas selalu simpan detail (role dan username) supaya log tak hilang
    $aktivitasLog = "Akun $roleDisplay username '{$user['username']}' (id: $id) berhasil dihapus";
    log_activity($conn, $aktivitasLog, "delete", $id);

    $stmt = $conn->prepare("DELETE FROM users WHERE id_user = ?");
    if (!$stmt) {
        return ['success' => false, 'message' => 'Error preparing delete statement: ' . $conn->error];
    }
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $affected = $stmt->affected_rows;
        $stmt->close();
        if ($affected > 0) {
            return ['success' => true, 'message' => $roleDisplay . ' "' . $user['username'] . '" berhasil dihapus'];
        } else {
            return ['success' => false, 'message' => 'Gagal menghapus pengguna: pengguna tidak ditemukan atau sudah dihapus'];
        }
    } else {
        $error = $conn->error;
        $stmt->close();
        return ['success' => false, 'message' => 'Gagal menghapus pengguna: ' . $error];
    }
}

// -------- Handler --------
try {
    $method = $_SERVER['REQUEST_METHOD'];
    $rawInput = file_get_contents('php://input');
    $input = json_decode($rawInput, true);

    if (json_last_error() !== JSON_ERROR_NONE && !empty($rawInput)) {
        http_response_code(200);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid JSON: ' . json_last_error_msg()
        ]);
        exit;
    }

    switch ($method) {
        case 'GET':
            if (isset($_GET['id']) && !empty($_GET['id'])) {
                $result = get_user($conn, (int)$_GET['id']);
            } else {
                $result = get_users($conn);
            }
            break;
        case 'POST':
            if (isset($input['action'])) {
                switch ($input['action']) {
                    case 'create':
                        $requiredFields = ['username', 'email', 'password', 'role'];
                        $missingFields = [];
                        foreach ($requiredFields as $field) if (empty($input[$field])) $missingFields[] = $field;
                        if (!empty($missingFields)) $result = ['success' => false, 'message' => 'Field yang diperlukan: ' . implode(', ', $missingFields)];
                        else $result = create_user($conn, $input);
                        break;
                    case 'update':
                        if (isset($input['id_user']) && !empty($input['id_user'])) {
                            $requiredFields = ['username', 'email', 'role'];
                            $missingFields = [];
                            foreach ($requiredFields as $field) if (empty($input[$field])) $missingFields[] = $field;
                            if (!empty($missingFields)) $result = ['success' => false, 'message' => 'Field yang diperlukan: ' . implode(', ', $missingFields)];
                            else $result = update_user($conn, (int)$input['id_user'], $input);
                        } else $result = ['success' => false, 'message' => 'ID pengguna tidak ditemukan'];
                        break;
                    case 'delete':
                        if (isset($input['id_user']) && !empty($input['id_user']))
                            $result = delete_user($conn, (int)$input['id_user']);
                        else $result = ['success' => false, 'message' => 'ID pengguna tidak ditemukan'];
                        break;
                    default:
                        $result = ['success' => false, 'message' => 'Action tidak valid: ' . $input['action']];
                        break;
                }
            } else $result = ['success' => false, 'message' => 'Action tidak ditemukan dalam request'];
            break;
        default:
            $result = ['success' => false, 'message' => 'Method tidak didukung: ' . $method];
            break;
    }

    http_response_code(200);
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(200);
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
    exit;
}
