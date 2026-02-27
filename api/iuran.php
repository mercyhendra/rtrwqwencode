<?php
/**
 * Iuran API Endpoint
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, OPTIONS');
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
        getIuran($pdo, $user, $isStaff);
        break;
    case 'POST':
        createIuran($pdo, $user, $isStaff);
        break;
    case 'PUT':
        updateIuran($pdo, $user, $isStaff);
        break;
    default:
        jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

function getIuran($pdo, $user, $isStaff) {
    if (isset($_GET['id'])) {
        $stmt = $pdo->prepare("
            SELECT i.*, u.nama as user_name 
            FROM iuran i 
            LEFT JOIN users u ON i.user_id = u.id 
            WHERE i.id = ?
        ");
        $stmt->execute([$_GET['id']]);
        $iuran = $stmt->fetch();
        
        if (!$iuran) {
            jsonResponse(['success' => false, 'message' => 'Data tidak ditemukan'], 404);
        }
        
        jsonResponse(['success' => true, 'data' => $iuran]);
    } else {
        $whereClause = $isStaff ? "1=1" : "i.user_id = ?";
        $params = $isStaff ? [] : [$user['id']];
        
        $stmt = $pdo->prepare("
            SELECT i.*, u.nama as user_name 
            FROM iuran i 
            LEFT JOIN users u ON i.user_id = u.id 
            WHERE $whereClause
            ORDER BY i.created_at DESC
        ");
        $stmt->execute($params);
        $iuran = $stmt->fetchAll();
        
        jsonResponse(['success' => true, 'data' => $iuran]);
    }
}

function createIuran($pdo, $user, $isStaff) {
    if (!$isStaff) {
        jsonResponse(['success' => false, 'message' => 'Only staff can create iuran records'], 403);
    }
    
    $input = getJsonInput();
    
    $validation = validateRequired($input, ['user_id', 'jenis_iuran', 'nominal', 'bulan']);
    if (!$validation['valid']) {
        jsonResponse(['success' => false, 'message' => $validation['message']], 400);
    }
    
    $allowedJenis = ['kebersihan', 'keamanan', 'sampah', 'lainnya'];
    if (!in_array($input['jenis_iuran'], $allowedJenis)) {
        jsonResponse(['success' => false, 'message' => 'Jenis iuran tidak valid'], 400);
    }
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO iuran (user_id, jenis_iuran, nominal, bulan, status, keterangan, dibuat_oleh) 
            VALUES (?, ?, ?, ?, 'belum_bayar', ?, ?)
        ");
        $stmt->execute([
            (int)$input['user_id'],
            $input['jenis_iuran'],
            (float)$input['nominal'],
            $input['bulan'],
            $input['keterangan'] ?? null,
            $user['id']
        ]);
        
        jsonResponse(['success' => true, 'message' => 'Iuran berhasil dicatat']);
    } catch (PDOException $e) {
        jsonResponse(['success' => false, 'message' => 'Gagal mencatat iuran'], 500);
    }
}

function updateIuran($pdo, $user, $isStaff) {
    $input = getJsonInput();
    
    if (empty($input['id'])) {
        jsonResponse(['success' => false, 'message' => 'ID diperlukan'], 400);
    }
    
    // Bayar iuran (for warga - self service)
    if (isset($input['action']) && $input['action'] === 'bayar') {
        try {
            // Verify ownership
            $stmt = $pdo->prepare("SELECT * FROM iuran WHERE id = ? AND user_id = ?");
            $stmt->execute([(int)$input['id'], $user['id']]);
            $iuran = $stmt->fetch();
            
            if (!$iuran) {
                jsonResponse(['success' => false, 'message' => 'Data tidak ditemukan'], 404);
            }
            
            if ($iuran['status'] === 'lunas') {
                jsonResponse(['success' => false, 'message' => 'Iuran sudah lunas']);
            }
            
            $stmt = $pdo->prepare("
                UPDATE iuran 
                SET status = 'lunas', tanggal_bayar = NOW() 
                WHERE id = ? AND user_id = ?
            ");
            $stmt->execute([(int)$input['id'], $user['id']]);
            
            jsonResponse(['success' => true, 'message' => 'Pembayaran berhasil dikonfirmasi']);
        } catch (PDOException $e) {
            jsonResponse(['success' => false, 'message' => 'Gagal konfirmasi pembayaran'], 500);
        }
    }
    
    // Konfirmasi bayar (for staff)
    if (isset($input['action']) && $input['action'] === 'konfirmasi') {
        if (!$isStaff) {
            jsonResponse(['success' => false, 'message' => 'Unauthorized'], 403);
        }
        
        try {
            $stmt = $pdo->prepare("
                UPDATE iuran 
                SET status = 'lunas', tanggal_bayar = NOW() 
                WHERE id = ?
            ");
            $stmt->execute([(int)$input['id']]);
            
            jsonResponse(['success' => true, 'message' => 'Pembayaran berhasil dikonfirmasi']);
        } catch (PDOException $e) {
            jsonResponse(['success' => false, 'message' => 'Gagal konfirmasi pembayaran'], 500);
        }
    }
    
    // Update by staff
    if (!$isStaff) {
        jsonResponse(['success' => false, 'message' => 'Unauthorized'], 403);
    }
    
    $updateFields = [];
    $params = [];
    
    $allowedFields = ['status', 'nominal', 'keterangan'];
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
        $sql = "UPDATE iuran SET " . implode(', ', $updateFields) . " WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        jsonResponse(['success' => true, 'message' => 'Iuran berhasil diupdate']);
    } catch (PDOException $e) {
        jsonResponse(['success' => false, 'message' => 'Gagal mengupdate iuran'], 500);
    }
}
