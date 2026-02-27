<?php
/**
 * Warga API Endpoint
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
$pdo = getDbConnection();
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        getWarga($pdo, $user, $isStaff);
        break;
    case 'POST':
        createWarga($pdo, $user, $isStaff);
        break;
    case 'PUT':
        updateWarga($pdo, $user, $isStaff);
        break;
    case 'DELETE':
        deleteWarga($pdo, $user, $isStaff);
        break;
    default:
        jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

function getWarga($pdo, $user, $isStaff) {
    if (isset($_GET['id'])) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        $warga = $stmt->fetch();
        
        if (!$warga) {
            jsonResponse(['success' => false, 'message' => 'Data tidak ditemukan'], 404);
        }
        
        unset($warga['password']);
        jsonResponse(['success' => true, 'data' => $warga]);
    } else {
        $whereClause = "role = 'warga'";
        $params = [];
        
        if (!$isStaff) {
            $whereClause .= " AND id = ?";
            $params[] = $user['id'];
        }
        
        $stmt = $pdo->prepare("SELECT * FROM users WHERE $whereClause ORDER BY nama ASC");
        $stmt->execute($params);
        $warga = $stmt->fetchAll();
        
        foreach ($warga as &$w) {
            unset($w['password']);
        }
        
        jsonResponse(['success' => true, 'data' => $warga]);
    }
}

function createWarga($pdo, $user, $isStaff) {
    if (!$isStaff) {
        jsonResponse(['success' => false, 'message' => 'Only staff can add warga'], 403);
    }
    
    $input = getJsonInput();
    
    $validation = validateRequired($input, ['nama', 'email', 'nik', 'no_kk', 'rt', 'rw', 'password']);
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
            INSERT INTO users (uuid, email, password, role, nama, nik, no_kk, rt, rw, no_rumah, whatsapp, pekerjaan) 
            VALUES (UUID(), ?, ?, 'warga', ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $input['email'],
            hashPassword($input['password']),
            sanitize($input['nama']),
            $input['nik'],
            $input['no_kk'],
            $input['rt'],
            $input['rw'],
            $input['no_rumah'] ?? null,
            $input['whatsapp'] ?? null,
            $input['pekerjaan'] ?? null
        ]);
        
        jsonResponse(['success' => true, 'message' => 'Warga berhasil ditambahkan']);
    } catch (PDOException $e) {
        jsonResponse(['success' => false, 'message' => 'Gagal menambahkan warga: ' . $e->getMessage()], 500);
    }
}

function updateWarga($pdo, $user, $isStaff) {
    $input = getJsonInput();
    
    if (empty($input['id'])) {
        jsonResponse(['success' => false, 'message' => 'ID diperlukan'], 400);
    }
    
    // Check permission
    if (!$isStaff && $input['id'] != $user['id']) {
        jsonResponse(['success' => false, 'message' => 'Unauthorized'], 403);
    }
    
    $updateFields = [];
    $params = [];
    
    $allowedFields = ['nama', 'whatsapp', 'pekerjaan', 'no_rumah', 'status'];
    foreach ($allowedFields as $field) {
        if (isset($input[$field])) {
            $updateFields[] = "$field = ?";
            $params[] = $input[$field];
        }
    }
    
    // Staff can update more fields
    if ($isStaff) {
        $staffFields = ['rt', 'rw', 'no_kk'];
        foreach ($staffFields as $field) {
            if (isset($input[$field])) {
                $updateFields[] = "$field = ?";
                $params[] = $input[$field];
            }
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
        
        jsonResponse(['success' => true, 'message' => 'Data berhasil diupdate']);
    } catch (PDOException $e) {
        jsonResponse(['success' => false, 'message' => 'Gagal mengupdate data'], 500);
    }
}

function deleteWarga($pdo, $user, $isStaff) {
    if (!$isStaff) {
        jsonResponse(['success' => false, 'message' => 'Unauthorized'], 403);
    }
    
    $input = getJsonInput();
    
    if (empty($input['id'])) {
        jsonResponse(['success' => false, 'message' => 'ID diperlukan'], 400);
    }
    
    try {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'warga'");
        $stmt->execute([(int)$input['id']]);
        
        jsonResponse(['success' => true, 'message' => 'Warga berhasil dihapus']);
    } catch (PDOException $e) {
        jsonResponse(['success' => false, 'message' => 'Gagal menghapus warga'], 500);
    }
}
