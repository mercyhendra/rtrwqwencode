<?php
/**
 * Users API - Handle CRUD operations for users
 * VILLA BINTARO REGENCY RT/RW Digital
 */

require_once 'config.php';

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$pdo = getDbConnection();
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'register':
        registerUser($pdo);
        break;
    
    case 'login':
        loginUser($pdo);
        break;
    
    case 'get_all':
        getAllUsers($pdo);
        break;
    
    case 'get_one':
        getUserById($pdo);
        break;
    
    case 'update':
        updateUser($pdo);
        break;
    
    case 'delete':
        deleteUser($pdo);
        break;
    
    default:
        jsonResponse(['success' => false, 'message' => 'Invalid action'], 400);
}

/**
 * Register new user
 */
function registerUser($pdo) {
    checkMethod('POST');
    
    $data = getJsonInput();
    
    // Validate required fields
    $validation = validateRequired($data, ['email', 'password', 'nama', 'nik', 'no_kk', 'rt', 'no_rumah', 'whatsapp']);
    if (!$validation['valid']) {
        jsonResponse(['success' => false, 'message' => $validation['message']], 400);
    }
    
    try {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$data['email']]);
        if ($stmt->fetch()) {
            jsonResponse(['success' => false, 'message' => 'Email sudah terdaftar'], 409);
        }
        
        // Check if NIK already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE nik = ?");
        $stmt->execute([$data['nik']]);
        if ($stmt->fetch()) {
            jsonResponse(['success' => false, 'message' => 'NIK sudah terdaftar'], 409);
        }
        
        // Check if KK already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE no_kk = ?");
        $stmt->execute([$data['no_kk']]);
        if ($stmt->fetch()) {
            jsonResponse(['success' => false, 'message' => 'Nomor KK sudah terdaftar'], 409);
        }
        
        // Insert new user
        $uuid = generateUUID();
        $hashedPassword = hashPassword($data['password']);
        
        $stmt = $pdo->prepare("
            INSERT INTO users (uuid, email, password, role, nama, nik, no_kk, rt, rw, no_rumah, whatsapp, pekerjaan, status) 
            VALUES (?, ?, ?, 'warga', ?, ?, ?, ?, ?, ?, ?, ?, 'aktif')
        ");
        
        $stmt->execute([
            $uuid,
            $data['email'],
            $hashedPassword,
            $data['nama'],
            $data['nik'],
            $data['no_kk'],
            $data['rt'] ?? '',
            $data['rw'] ?? '',
            $data['no_rumah'] ?? '',
            $data['whatsapp'] ?? '',
            $data['pekerjaan'] ?? ''
        ]);
        
        $userId = $pdo->lastInsertId();
        
        jsonResponse([
            'success' => true,
            'message' => 'Registrasi berhasil',
            'user_id' => $userId
        ]);
        
    } catch (PDOException $e) {
        jsonResponse(['success' => false, 'message' => 'Database error: ' . $e->getMessage()], 500);
    }
}

/**
 * Login user
 */
function loginUser($pdo) {
    checkMethod('POST');
    
    $data = getJsonInput();
    
    if (empty($data['email']) || empty($data['password'])) {
        jsonResponse(['success' => false, 'message' => 'Email dan password required'], 400);
    }
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$data['email']]);
        $user = $stmt->fetch();
        
        if (!$user || !verifyPassword($data['password'], $user['password'])) {
            jsonResponse(['success' => false, 'message' => 'Email atau password salah'], 401);
        }
        
        // Remove password from response
        unset($user['password']);
        
        jsonResponse([
            'success' => true,
            'message' => 'Login berhasil',
            'user' => $user
        ]);
        
    } catch (PDOException $e) {
        jsonResponse(['success' => false, 'message' => 'Database error: ' . $e->getMessage()], 500);
    }
}

/**
 * Get all users (for admin)
 */
function getAllUsers($pdo) {
    checkMethod('GET');
    
    try {
        $stmt = $pdo->prepare("
            SELECT id, uuid, email, role, nama, nik, no_kk, rt, rw, no_rumah, whatsapp, pekerjaan, status, created_at, updated_at 
            FROM users 
            ORDER BY created_at DESC
        ");
        $stmt->execute();
        $users = $stmt->fetchAll();
        
        jsonResponse([
            'success' => true,
            'data' => $users
        ]);
        
    } catch (PDOException $e) {
        jsonResponse(['success' => false, 'message' => 'Database error: ' . $e->getMessage()], 500);
    }
}

/**
 * Get user by ID
 */
function getUserById($pdo) {
    checkMethod('GET');
    
    $id = $_GET['id'] ?? '';
    
    if (empty($id)) {
        jsonResponse(['success' => false, 'message' => 'User ID required'], 400);
    }
    
    try {
        $stmt = $pdo->prepare("
            SELECT id, uuid, email, role, nama, nik, no_kk, rt, rw, no_rumah, whatsapp, pekerjaan, status, created_at, updated_at 
            FROM users 
            WHERE id = ?
        ");
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        
        if (!$user) {
            jsonResponse(['success' => false, 'message' => 'User not found'], 404);
        }
        
        jsonResponse([
            'success' => true,
            'data' => $user
        ]);
        
    } catch (PDOException $e) {
        jsonResponse(['success' => false, 'message' => 'Database error: ' . $e->getMessage()], 500);
    }
}

/**
 * Update user
 */
function updateUser($pdo) {
    checkMethod('PUT');
    
    $data = getJsonInput();
    
    if (empty($data['id'])) {
        jsonResponse(['success' => false, 'message' => 'User ID required'], 400);
    }
    
    try {
        $fields = [];
        $values = [];
        
        // Allowed fields to update
        $allowedFields = ['nama', 'nik', 'no_kk', 'rt', 'rw', 'no_rumah', 'whatsapp', 'pekerjaan', 'status', 'email', 'status_keluarga'];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = ?";
                $values[] = $data[$field];
            }
        }
        
        if (empty($fields)) {
            jsonResponse(['success' => false, 'message' => 'No fields to update'], 400);
        }
        
        $values[] = $data['id'];
        
        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($values);
        
        jsonResponse([
            'success' => true,
            'message' => 'User updated successfully'
        ]);
        
    } catch (PDOException $e) {
        jsonResponse(['success' => false, 'message' => 'Database error: ' . $e->getMessage()], 500);
    }
}

/**
 * Delete user
 */
function deleteUser($pdo) {
    checkMethod('DELETE');
    
    $id = $_GET['id'] ?? '';
    
    if (empty($id)) {
        jsonResponse(['success' => false, 'message' => 'User ID required'], 400);
    }
    
    try {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);
        
        if ($stmt->rowCount() === 0) {
            jsonResponse(['success' => false, 'message' => 'User not found'], 404);
        }
        
        jsonResponse([
            'success' => true,
            'message' => 'User deleted successfully'
        ]);
        
    } catch (PDOException $e) {
        jsonResponse(['success' => false, 'message' => 'Database error: ' . $e->getMessage()], 500);
    }
}
