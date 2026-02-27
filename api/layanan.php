<?php
/**
 * Layanan Surat API Endpoint
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
        getLayanan($pdo, $user, $isStaff);
        break;
    case 'POST':
        createLayanan($pdo, $user);
        break;
    case 'PUT':
        updateLayanan($pdo, $user, $isStaff);
        break;
    case 'DELETE':
        deleteLayanan($pdo, $user, $isStaff);
        break;
    default:
        jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

function getLayanan($pdo, $user, $isStaff) {
    if (isset($_GET['id'])) {
        $stmt = $pdo->prepare("
            SELECT ls.*, u.nama as user_name 
            FROM layanan_surat ls 
            LEFT JOIN users u ON ls.user_id = u.id 
            WHERE ls.id = ?
        ");
        $stmt->execute([$_GET['id']]);
        $layanan = $stmt->fetch();
        
        if (!$layanan) {
            jsonResponse(['success' => false, 'message' => 'Data tidak ditemukan'], 404);
        }
        
        jsonResponse(['success' => true, 'data' => $layanan]);
    } else {
        $whereClause = $isStaff ? "1=1" : "ls.user_id = ?";
        $params = $isStaff ? [] : [$user['id']];
        
        $stmt = $pdo->prepare("
            SELECT ls.*, u.nama as user_name 
            FROM layanan_surat ls 
            LEFT JOIN users u ON ls.user_id = u.id 
            WHERE $whereClause
            ORDER BY ls.created_at DESC
        ");
        $stmt->execute($params);
        $layanan = $stmt->fetchAll();
        
        jsonResponse(['success' => true, 'data' => $layanan]);
    }
}

function createLayanan($pdo, $user) {
    $input = getJsonInput();
    
    $validation = validateRequired($input, ['jenis_surat', 'keperluan']);
    if (!$validation['valid']) {
        jsonResponse(['success' => false, 'message' => $validation['message']], 400);
    }
    
    $allowedJenis = ['domisili', 'ktp', 'kelahiran', 'kematian', 'nikah', 'usaha'];
    if (!in_array($input['jenis_surat'], $allowedJenis)) {
        jsonResponse(['success' => false, 'message' => 'Jenis surat tidak valid'], 400);
    }
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO layanan_surat (user_id, jenis_surat, keperluan, status, dibuat_oleh) 
            VALUES (?, ?, ?, 'pending', ?)
        ");
        $stmt->execute([
            $user['id'],
            $input['jenis_surat'],
            sanitize($input['keperluan']),
            $user['id']
        ]);
        
        jsonResponse(['success' => true, 'message' => 'Pengajuan surat berhasil ditambahkan']);
    } catch (PDOException $e) {
        jsonResponse(['success' => false, 'message' => 'Gagal menambahkan pengajuan'], 500);
    }
}

function updateLayanan($pdo, $user, $isStaff) {
    if (!$isStaff) {
        jsonResponse(['success' => false, 'message' => 'Unauthorized'], 403);
    }
    
    $input = getJsonInput();
    
    if (empty($input['id'])) {
        jsonResponse(['success' => false, 'message' => 'ID diperlukan'], 400);
    }
    
    $updateFields = [];
    $params = [];
    
    $allowedFields = ['status', 'no_surat', 'catatan_admin'];
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
        $sql = "UPDATE layanan_surat SET " . implode(', ', $updateFields) . " WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        jsonResponse(['success' => true, 'message' => 'Pengajuan berhasil diupdate']);
    } catch (PDOException $e) {
        jsonResponse(['success' => false, 'message' => 'Gagal mengupdate pengajuan'], 500);
    }
}

function deleteLayanan($pdo, $user, $isStaff) {
    if (!$isStaff) {
        jsonResponse(['success' => false, 'message' => 'Unauthorized'], 403);
    }
    
    $input = getJsonInput();
    
    if (empty($input['id'])) {
        jsonResponse(['success' => false, 'message' => 'ID diperlukan'], 400);
    }
    
    try {
        $stmt = $pdo->prepare("DELETE FROM layanan_surat WHERE id = ?");
        $stmt->execute([(int)$input['id']]);
        
        jsonResponse(['success' => true, 'message' => 'Pengajuan berhasil dihapus']);
    } catch (PDOException $e) {
        jsonResponse(['success' => false, 'message' => 'Gagal menghapus pengajuan'], 500);
    }
}
