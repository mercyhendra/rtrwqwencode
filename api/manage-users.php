<?php
/**
 * Manage Users API Endpoint
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../includes/auth.php';

if (!isLoggedIn()) {
    jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
}

$user = getCurrentUser();
$isStaff = isStaff();

// Only admin can manage users
if ($user['role'] !== 'admin') {
    jsonResponse(['success' => false, 'message' => 'Unauthorized. Admin only.'], 403);
}

$pdo = getDbConnection();
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        getUser($pdo, $user);
        break;
    case 'POST':
        createUser($pdo, $user);
        break;
    case 'PUT':
        updateUser($pdo, $user);
        break;
    case 'DELETE':
        deleteUser($pdo, $user);
        break;
    default:
        jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

function getUser($pdo, $user) {
    if (!isset($_GET['id'])) {
        jsonResponse(['success' => false, 'message' => 'ID required'], 400);
    }
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $userData = $stmt->fetch();
    
    if (!$userData) {
        jsonResponse(['success' => false, 'message' => 'User not found'], 404);
    }
    
    unset($userData['password']);
    jsonResponse(['success' => true, 'data' => $userData]);
}

function createUser($pdo, $user) {
    $input = getJsonInput();
    
    $validation = validateRequired($input, ['nama', 'email', 'nik', 'no_kk', 'role', 'password']);
    if (!$validation['valid']) {
        jsonResponse(['success' => false, 'message' => $validation['message']], 400);
    }
    
    // Check duplicate NIK
    $stmt = $pdo->prepare("SELECT id FROM users WHERE nik = ?");
    $stmt->execute([$input['nik']]);
    if ($stmt->fetch()) {
        jsonResponse(['success' => false, 'message' => 'NIK sudah terdaftar'], 400);
    }
    
    // Check duplicate email
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$input['email']]);
    if ($stmt->fetch()) {
        jsonResponse(['success' => false, 'message' => 'Email sudah terdaftar'], 400);
    }
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO users (uuid, email, password, role, nama, nik, no_kk, rt, rw, no_rumah, whatsapp, pekerjaan, status) 
            VALUES (UUID(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'aktif')
        ");
        $stmt->execute([
            $input['email'],
            hashPassword($input['password']),
            $input['role'],
            sanitize($input['nama']),
            $input['nik'],
            $input['no_kk'],
            $input['rt'] ?? null,
            $input['rw'] ?? null,
            $input['no_rumah'] ?? null,
            $input['whatsapp'] ?? null,
            $input['pekerjaan'] ?? null
        ]);
        
        jsonResponse(['success' => true, 'message' => 'User berhasil ditambahkan']);
    } catch (PDOException $e) {
        jsonResponse(['success' => false, 'message' => 'Gagal menambahkan user: ' . $e->getMessage()], 500);
    }
}

function updateUser($pdo, $user) {
    $input = getJsonInput();
    
    if (empty($input['id'])) {
        jsonResponse(['success' => false, 'message' => 'ID diperlukan'], 400);
    }
    
    // Change password action
    if (isset($input['action']) && $input['action'] === 'change_password') {
        if (empty($input['password'])) {
            jsonResponse(['success' => false, 'message' => 'Password required'], 400);
        }
        
        try {
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([hashPassword($input['password']), (int)$input['id']]);
            
            jsonResponse(['success' => true, 'message' => 'Password berhasil diubah']);
        } catch (PDOException $e) {
            jsonResponse(['success' => false, 'message' => 'Gagal mengubah password'], 500);
        }
        return;
    }
    
    // Update user data
    $updateFields = [];
    $params = [];
    
    $allowedFields = ['nama', 'email', 'no_kk', 'role', 'rt', 'rw', 'no_rumah', 'whatsapp', 'pekerjaan', 'status'];
    foreach ($allowedFields as $field) {
        if (isset($input[$field])) {
            $updateFields[] = "$field = ?";
            $params[] = $input[$field];
        }
    }
    
    if (empty($updateFields)) {
        jsonResponse(['success' => false, 'message' => 'Tidak ada data untuk diupdate'], 400);
    }
    
    $params[] = (int)$input['id'];
    
    try {
        $sql = "UPDATE users SET " . implode(', ', $updateFields) . " WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        jsonResponse(['success' => true, 'message' => 'User berhasil diupdate']);
    } catch (PDOException $e) {
        jsonResponse(['success' => false, 'message' => 'Gagal mengupdate user: ' . $e->getMessage()], 500);
    }
}

function deleteUser($pdo, $user) {
    $input = getJsonInput();
    
    if (empty($input['id'])) {
        jsonResponse(['success' => false, 'message' => 'ID diperlukan'], 400);
    }
    
    // Prevent deleting self
    if ($input['id'] == $user['id']) {
        jsonResponse(['success' => false, 'message' => 'Cannot delete yourself'], 400);
    }
    
    try {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([(int)$input['id']]);
        
        jsonResponse(['success' => true, 'message' => 'User berhasil dihapus']);
    } catch (PDOException $e) {
        jsonResponse(['success' => false, 'message' => 'Gagal menghapus user: ' . $e->getMessage()], 500);
    }
}
