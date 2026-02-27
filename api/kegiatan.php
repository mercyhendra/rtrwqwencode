<?php
/**
 * Kegiatan API Endpoint
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
        getKegiatan($pdo, $user, $isStaff);
        break;
    case 'POST':
        createOrJoinKegiatan($pdo, $user, $isStaff);
        break;
    case 'PUT':
        updateKegiatan($pdo, $user, $isStaff);
        break;
    case 'DELETE':
        deleteKegiatan($pdo, $user, $isStaff);
        break;
    default:
        jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

function getKegiatan($pdo, $user, $isStaff) {
    // Get detail kegiatan by ID
    if (isset($_GET['id'])) {
        $stmt = $pdo->prepare("
            SELECT k.*, u.nama as creator_name 
            FROM kegiatan k 
            LEFT JOIN users u ON k.dibuat_oleh = u.id 
            WHERE k.id = ?
        ");
        $stmt->execute([$_GET['id']]);
        $kegiatan = $stmt->fetch();
        
        if (!$kegiatan) {
            jsonResponse(['success' => false, 'message' => 'Kegiatan tidak ditemukan'], 404);
        }
        
        jsonResponse(['success' => true, 'data' => $kegiatan]);
        return;
    }
    
    // Get list kegiatan
    $filter = $_GET['filter'] ?? 'all';
    $whereClause = "1=1";
    
    if ($filter === 'upcoming') {
        $whereClause .= " AND tanggal_kegiatan >= CURDATE() AND status = 'aktif'";
    } elseif ($filter === 'past') {
        $whereClause .= " AND tanggal_kegiatan < CURDATE()";
    } elseif ($filter === 'active') {
        $whereClause .= " AND status = 'aktif'";
    }
    
    $stmt = $pdo->prepare("
        SELECT k.*, u.nama as creator_name 
        FROM kegiatan k 
        LEFT JOIN users u ON k.dibuat_oleh = u.id 
        WHERE $whereClause
        ORDER BY k.tanggal_kegiatan DESC
    ");
    $stmt->execute();
    $kegiatan = $stmt->fetchAll();
    jsonResponse(['success' => true, 'data' => $kegiatan]);
}

function createOrJoinKegiatan($pdo, $user, $isStaff) {
    $input = getJsonInput();
    
    // Join kegiatan
    if (isset($input['action']) && $input['action'] === 'join') {
        if (empty($input['kegiatan_id'])) {
            jsonResponse(['success' => false, 'message' => 'ID kegiatan diperlukan'], 400);
        }
        
        $kegiatanId = (int)$input['kegiatan_id'];
        
        // Check if already joined
        $stmt = $pdo->prepare("SELECT * FROM kegiatan_peserta WHERE kegiatan_id = ? AND user_id = ?");
        $stmt->execute([$kegiatanId, $user['id']]);
        if ($stmt->fetch()) {
            jsonResponse(['success' => false, 'message' => 'Anda sudah mendaftar kegiatan ini']);
        }
        
        // Insert peserta
        $stmt = $pdo->prepare("INSERT INTO kegiatan_peserta (kegiatan_id, user_id) VALUES (?, ?)");
        $stmt->execute([$kegiatanId, $user['id']]);
        
        // Update jumlah_peserta
        $stmt = $pdo->prepare("UPDATE kegiatan SET jumlah_peserta = jumlah_peserta + 1 WHERE id = ?");
        $stmt->execute([$kegiatanId]);
        
        jsonResponse(['success' => true, 'message' => 'Berhasil mendaftar kegiatan']);
    }
    
    // Create new kegiatan (staff only)
    if (!$isStaff) {
        jsonResponse(['success' => false, 'message' => 'Only staff can create kegiatan'], 403);
    }
    
    $validation = validateRequired($input, ['nama_kegiatan', 'kategori', 'tanggal_kegiatan']);
    if (!$validation['valid']) {
        jsonResponse(['success' => false, 'message' => $validation['message']], 400);
    }
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO kegiatan (nama_kegiatan, kategori, tanggal_kegiatan, lokasi, deskripsi, kuota_peserta, dibuat_oleh) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            sanitize($input['nama_kegiatan']),
            $input['kategori'],
            $input['tanggal_kegiatan'],
            $input['lokasi'] ?? null,
            $input['deskripsi'] ?? null,
            $input['kuota_peserta'] ?? null,
            $user['id']
        ]);
        
        jsonResponse(['success' => true, 'message' => 'Kegiatan berhasil dibuat']);
    } catch (PDOException $e) {
        jsonResponse(['success' => false, 'message' => 'Gagal membuat kegiatan'], 500);
    }
}

function updateKegiatan($pdo, $user, $isStaff) {
    if (!$isStaff) {
        jsonResponse(['success' => false, 'message' => 'Unauthorized'], 403);
    }
    
    $input = getJsonInput();
    if (empty($input['id'])) {
        jsonResponse(['success' => false, 'message' => 'ID diperlukan'], 400);
    }
    
    // Build update query
    $updateFields = [];
    $params = [];
    
    foreach (['nama_kegiatan', 'kategori', 'tanggal_kegiatan', 'lokasi', 'deskripsi', 'kuota_peserta', 'status'] as $field) {
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
        $sql = "UPDATE kegiatan SET " . implode(', ', $updateFields) . " WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        jsonResponse(['success' => true, 'message' => 'Kegiatan berhasil diupdate']);
    } catch (PDOException $e) {
        jsonResponse(['success' => false, 'message' => 'Gagal mengupdate kegiatan'], 500);
    }
}

function deleteKegiatan($pdo, $user, $isStaff) {
    if (!$isStaff) {
        jsonResponse(['success' => false, 'message' => 'Unauthorized'], 403);
    }
    
    $input = getJsonInput();
    if (empty($input['id'])) {
        jsonResponse(['success' => false, 'message' => 'ID diperlukan'], 400);
    }
    
    try {
        $stmt = $pdo->prepare("DELETE FROM kegiatan WHERE id = ?");
        $stmt->execute([(int)$input['id']]);
        jsonResponse(['success' => true, 'message' => 'Kegiatan berhasil dihapus']);
    } catch (PDOException $e) {
        jsonResponse(['success' => false, 'message' => 'Gagal menghapus kegiatan'], 500);
    }
}
