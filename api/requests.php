<?php
/**
 * Requests API Endpoint
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
        getRequests($pdo, $user, $isStaff);
        break;
    case 'POST':
        createRequest($pdo, $user);
        break;
    case 'PUT':
        updateRequest($pdo, $user, $isStaff);
        break;
    case 'DELETE':
        deleteRequest($pdo, $user, $isStaff);
        break;
    default:
        jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

function getRequests($pdo, $user, $isStaff) {
    if (isset($_GET['id'])) {
        $stmt = $pdo->prepare("
            SELECT r.*, u.nama as user_name, u.email as user_email 
            FROM anggota_keluarga_request r 
            LEFT JOIN users u ON r.user_id = u.id 
            WHERE r.id = ?
        ");
        $stmt->execute([$_GET['id']]);
        $request = $stmt->fetch();
        
        if (!$request) {
            jsonResponse(['success' => false, 'message' => 'Data tidak ditemukan'], 404);
        }
        
        jsonResponse(['success' => true, 'data' => $request]);
    } else {
        $whereClause = $isStaff ? "1=1" : "r.user_id = ?";
        $params = $isStaff ? [] : [$user['id']];
        
        $stmt = $pdo->prepare("
            SELECT r.*, u.nama as user_name, u.email as user_email 
            FROM anggota_keluarga_request r 
            LEFT JOIN users u ON r.user_id = u.id 
            WHERE $whereClause
            ORDER BY r.created_at DESC
        ");
        $stmt->execute($params);
        $requests = $stmt->fetchAll();
        
        jsonResponse(['success' => true, 'data' => $requests]);
    }
}

function createRequest($pdo, $user) {
    $input = getJsonInput();
    
    $validation = validateRequired($input, ['nama', 'hubungan', 'jenis_kelamin']);
    if (!$validation['valid']) {
        jsonResponse(['success' => false, 'message' => $validation['message']], 400);
    }
    
    // Check duplicate NIK if provided
    if (!empty($input['nik'])) {
        $stmt = $pdo->prepare("SELECT id FROM anggota_keluarga WHERE nik = ? OR anggota_keluarga_request WHERE nik = ?");
        // Simple check
        $stmt = $pdo->prepare("SELECT id FROM anggota_keluarga WHERE nik = ?");
        $stmt->execute([$input['nik']]);
        if ($stmt->fetch()) {
            jsonResponse(['success' => false, 'message' => 'NIK sudah terdaftar'], 400);
        }
    }
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO anggota_keluarga_request 
            (user_id, nama, nik, hubungan, jenis_kelamin, tempat_lahir, tanggal_lahir, pekerjaan, status_perkawinan, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')
        ");
        $stmt->execute([
            $user['id'],
            sanitize($input['nama']),
            !empty($input['nik']) ? $input['nik'] : null,
            $input['hubungan'],
            $input['jenis_kelamin'],
            $input['tempat_lahir'] ?? null,
            !empty($input['tanggal_lahir']) ? $input['tanggal_lahir'] : null,
            $input['pekerjaan'] ?? null,
            $input['status_perkawinan'] ?? null
        ]);
        
        jsonResponse(['success' => true, 'message' => 'Request berhasil ditambahkan']);
    } catch (PDOException $e) {
        jsonResponse(['success' => false, 'message' => 'Gagal menambahkan request'], 500);
    }
}

function updateRequest($pdo, $user, $isStaff) {
    if (!$isStaff) {
        jsonResponse(['success' => false, 'message' => 'Unauthorized'], 403);
    }
    
    $input = getJsonInput();
    
    if (empty($input['id'])) {
        jsonResponse(['success' => false, 'message' => 'ID diperlukan'], 400);
    }
    
    // Approve
    if (isset($input['action']) && $input['action'] === 'approve') {
        try {
            // Get request data
            $stmt = $pdo->prepare("SELECT * FROM anggota_keluarga_request WHERE id = ?");
            $stmt->execute([$input['id']]);
            $request = $stmt->fetch();
            
            if (!$request) {
                jsonResponse(['success' => false, 'message' => 'Request tidak ditemukan'], 404);
            }
            
            // Insert to anggota_keluarga
            $stmt = $pdo->prepare("
                INSERT INTO anggota_keluarga (user_id, nama, nik, hubungan, jenis_kelamin, tempat_lahir, tanggal_lahir, pekerjaan, status_perkawinan)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $request['user_id'],
                $request['nama'],
                $request['nik'],
                $request['hubungan'],
                $request['jenis_kelamin'],
                $request['tempat_lahir'],
                $request['tanggal_lahir'],
                $request['pekerjaan'],
                $request['status_perkawinan']
            ]);
            
            // Update request status
            $stmt = $pdo->prepare("UPDATE anggota_keluarga_request SET status = 'approved', catatan_admin = ? WHERE id = ?");
            $stmt->execute([$input['catatan_admin'] ?? 'Disetujui', $input['id']]);
            
            jsonResponse(['success' => true, 'message' => 'Request disetujui']);
        } catch (PDOException $e) {
            jsonResponse(['success' => false, 'message' => 'Gagal menyetujui request'], 500);
        }
    }
    
    // Reject
    if (isset($input['action']) && $input['action'] === 'reject') {
        try {
            $stmt = $pdo->prepare("UPDATE anggota_keluarga_request SET status = 'rejected', catatan_admin = ? WHERE id = ?");
            $stmt->execute([$input['catatan_admin'] ?? 'Ditolak', $input['id']]);
            
            jsonResponse(['success' => true, 'message' => 'Request ditolak']);
        } catch (PDOException $e) {
            jsonResponse(['success' => false, 'message' => 'Gagal menolak request'], 500);
        }
    }
    
    // General update
    $updateFields = [];
    $params = [];
    
    $allowedFields = ['status', 'catatan_admin'];
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
        $sql = "UPDATE anggota_keluarga_request SET " . implode(', ', $updateFields) . " WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        jsonResponse(['success' => true, 'message' => 'Request berhasil diupdate']);
    } catch (PDOException $e) {
        jsonResponse(['success' => false, 'message' => 'Gagal mengupdate request'], 500);
    }
}

function deleteRequest($pdo, $user, $isStaff) {
    if (!$isStaff) {
        jsonResponse(['success' => false, 'message' => 'Unauthorized'], 403);
    }
    
    $input = getJsonInput();
    
    if (empty($input['id'])) {
        jsonResponse(['success' => false, 'message' => 'ID diperlukan'], 400);
    }
    
    try {
        $stmt = $pdo->prepare("DELETE FROM anggota_keluarga_request WHERE id = ?");
        $stmt->execute([(int)$input['id']]);
        
        jsonResponse(['success' => true, 'message' => 'Request berhasil dihapus']);
    } catch (PDOException $e) {
        jsonResponse(['success' => false, 'message' => 'Gagal menghapus request'], 500);
    }
}
