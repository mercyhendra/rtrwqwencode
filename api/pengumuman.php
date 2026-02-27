<?php
/**
 * Pengumuman API Endpoint
 * VILLA BINTARO REGENCY RT/RW Digital
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

// Check authentication
if (!isLoggedIn()) {
    jsonResponse(['success' => false, 'message' => 'Unauthorized. Please login.'], 401);
}

$user = getCurrentUser();
$isStaff = isStaff();
$pdo = getDbConnection();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        getPengumuman($pdo, $user);
        break;
    case 'POST':
        createPengumuman($pdo, $user, $isStaff);
        break;
    case 'PUT':
        updatePengumuman($pdo, $user, $isStaff);
        break;
    case 'DELETE':
        deletePengumuman($pdo, $user, $isStaff);
        break;
    default:
        jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

/**
 * GET - Fetch single or all announcements
 */
function getPengumuman($pdo, $user) {
    if (isset($_GET['id'])) {
        // Get single announcement
        $id = (int)$_GET['id'];
        
        $stmt = $pdo->prepare("
            SELECT p.*, u.nama as posted_by_name 
            FROM pengumuman p 
            LEFT JOIN users u ON p.dibuat_oleh = u.id 
            WHERE p.id = ?
        ");
        $stmt->execute([$id]);
        $pengumuman = $stmt->fetch();
        
        if (!$pengumuman) {
            jsonResponse(['success' => false, 'message' => 'Pengumuman tidak ditemukan'], 404);
        }
        
        // Map fields for frontend compatibility
        $pengumuman['tanggal_post'] = $pengumuman['created_at'];
        $pengumuman['tanggal_event'] = $pengumuman['tanggal_acara'];
        $pengumuman['lokasi'] = $pengumuman['lokasi_acara'];
        $pengumuman['diposting_oleh'] = $pengumuman['dibuat_oleh'];
        
        // Increment views
        $updateStmt = $pdo->prepare("UPDATE pengumuman SET views = views + 1 WHERE id = ?");
        $updateStmt->execute([$id]);
        
        jsonResponse(['success' => true, 'data' => $pengumuman]);
    } else {
        // Get all announcements
        $stmt = $pdo->prepare("
            SELECT p.*, u.nama as posted_by_name 
            FROM pengumuman p 
            LEFT JOIN users u ON p.dibuat_oleh = u.id 
            ORDER BY p.created_at DESC
        ");
        $stmt->execute();
        $pengumumanList = $stmt->fetchAll();
        
        // Map fields for frontend compatibility
        foreach ($pengumumanList as &$p) {
            $p['tanggal_post'] = $p['created_at'];
            $p['tanggal_event'] = $p['tanggal_acara'];
            $p['lokasi'] = $p['lokasi_acara'];
            $p['diposting_oleh'] = $p['dibuat_oleh'];
        }
        
        jsonResponse(['success' => true, 'data' => $pengumumanList]);
    }
}

/**
 * POST - Create new announcement
 */
function createPengumuman($pdo, $user, $isStaff) {
    if (!$isStaff) {
        jsonResponse(['success' => false, 'message' => 'Unauthorized. Only staff can create announcements.'], 403);
    }
    
    $input = getJsonInput();
    
    // Validate required fields
    $validation = validateRequired($input, ['judul', 'kategori', 'isi']);
    if (!$validation['valid']) {
        jsonResponse(['success' => false, 'message' => $validation['message']], 400);
    }
    
    // Validate kategori
    $allowedKategori = ['umum', 'penting', 'warga', 'darurat'];
    if (!in_array($input['kategori'], $allowedKategori)) {
        jsonResponse(['success' => false, 'message' => 'Kategori tidak valid'], 400);
    }
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO pengumuman (judul, kategori, isi, tanggal_acara, lokasi_acara, dibuat_oleh) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            sanitize($input['judul']),
            $input['kategori'],
            sanitize($input['isi']),
            !empty($input['tanggal_event']) ? $input['tanggal_event'] : null,
            !empty($input['lokasi']) ? sanitize($input['lokasi']) : null,
            $user['id']
        ]);
        
        $newId = $pdo->lastInsertId();
        
        // Fetch the created announcement
        $selectStmt = $pdo->prepare("
            SELECT p.*, u.nama as posted_by_name 
            FROM pengumuman p 
            LEFT JOIN users u ON p.dibuat_oleh = u.id 
            WHERE p.id = ?
        ");
        $selectStmt->execute([$newId]);
        $pengumuman = $selectStmt->fetch();
        
        // Map fields for frontend compatibility
        $pengumuman['tanggal_post'] = $pengumuman['created_at'];
        $pengumuman['tanggal_event'] = $pengumuman['tanggal_acara'];
        $pengumuman['lokasi'] = $pengumuman['lokasi_acara'];
        $pengumuman['diposting_oleh'] = $pengumuman['dibuat_oleh'];
        
        jsonResponse(['success' => true, 'message' => 'Pengumuman berhasil ditambahkan', 'data' => $pengumuman], 201);
    } catch (PDOException $e) {
        jsonResponse(['success' => false, 'message' => 'Gagal menambahkan pengumuman: ' . $e->getMessage()], 500);
    }
}

/**
 * PUT - Update announcement
 */
function updatePengumuman($pdo, $user, $isStaff) {
    if (!$isStaff) {
        jsonResponse(['success' => false, 'message' => 'Unauthorized. Only staff can update announcements.'], 403);
    }
    
    $input = getJsonInput();
    
    // Validate required fields
    if (empty($input['id'])) {
        jsonResponse(['success' => false, 'message' => 'ID pengumuman diperlukan'], 400);
    }
    
    $id = (int)$input['id'];
    
    // Check if announcement exists
    $checkStmt = $pdo->prepare("SELECT * FROM pengumuman WHERE id = ?");
    $checkStmt->execute([$id]);
    $pengumuman = $checkStmt->fetch();
    
    if (!$pengumuman) {
        jsonResponse(['success' => false, 'message' => 'Pengumuman tidak ditemukan'], 404);
    }
    
    // Build update query dynamically
    $updateFields = [];
    $params = [];
    
    if (isset($input['judul'])) {
        $updateFields[] = "judul = ?";
        $params[] = sanitize($input['judul']);
    }
    
    if (isset($input['kategori'])) {
        $allowedKategori = ['umum', 'penting', 'warga', 'darurat'];
        if (!in_array($input['kategori'], $allowedKategori)) {
            jsonResponse(['success' => false, 'message' => 'Kategori tidak valid'], 400);
        }
        $updateFields[] = "kategori = ?";
        $params[] = $input['kategori'];
    }
    
    if (isset($input['isi'])) {
        $updateFields[] = "isi = ?";
        $params[] = sanitize($input['isi']);
    }
    
    if (array_key_exists('tanggal_event', $input)) {
        $updateFields[] = "tanggal_acara = ?";
        $params[] = !empty($input['tanggal_event']) ? $input['tanggal_event'] : null;
    }
    
    if (array_key_exists('lokasi', $input)) {
        $updateFields[] = "lokasi_acara = ?";
        $params[] = !empty($input['lokasi']) ? sanitize($input['lokasi']) : null;
    }
    
    if (empty($updateFields)) {
        jsonResponse(['success' => false, 'message' => 'Tidak ada data untuk diupdate'], 400);
    }
    
    $params[] = $id;
    
    try {
        $sql = "UPDATE pengumuman SET " . implode(', ', $updateFields) . " WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        // Fetch updated announcement
        $selectStmt = $pdo->prepare("
            SELECT p.*, u.nama as posted_by_name 
            FROM pengumuman p 
            LEFT JOIN users u ON p.dibuat_oleh = u.id 
            WHERE p.id = ?
        ");
        $selectStmt->execute([$id]);
        $pengumuman = $selectStmt->fetch();
        
        // Map fields for frontend compatibility
        $pengumuman['tanggal_post'] = $pengumuman['created_at'];
        $pengumuman['tanggal_event'] = $pengumuman['tanggal_acara'];
        $pengumuman['lokasi'] = $pengumuman['lokasi_acara'];
        $pengumuman['diposting_oleh'] = $pengumuman['dibuat_oleh'];
        
        jsonResponse(['success' => true, 'message' => 'Pengumuman berhasil diupdate', 'data' => $pengumuman]);
    } catch (PDOException $e) {
        jsonResponse(['success' => false, 'message' => 'Gagal mengupdate pengumuman: ' . $e->getMessage()], 500);
    }
}

/**
 * DELETE - Delete announcement
 */
function deletePengumuman($pdo, $user, $isStaff) {
    if (!$isStaff) {
        jsonResponse(['success' => false, 'message' => 'Unauthorized. Only staff can delete announcements.'], 403);
    }
    
    $input = getJsonInput();
    
    if (empty($input['id'])) {
        jsonResponse(['success' => false, 'message' => 'ID pengumuman diperlukan'], 400);
    }
    
    $id = (int)$input['id'];
    
    // Check if announcement exists
    $checkStmt = $pdo->prepare("SELECT * FROM pengumuman WHERE id = ?");
    $checkStmt->execute([$id]);
    $pengumuman = $checkStmt->fetch();
    
    if (!$pengumuman) {
        jsonResponse(['success' => false, 'message' => 'Pengumuman tidak ditemukan'], 404);
    }
    
    try {
        $stmt = $pdo->prepare("DELETE FROM pengumuman WHERE id = ?");
        $stmt->execute([$id]);
        
        jsonResponse(['success' => true, 'message' => 'Pengumuman berhasil dihapus']);
    } catch (PDOException $e) {
        jsonResponse(['success' => false, 'message' => 'Gagal menghapus pengumuman: ' . $e->getMessage()], 500);
    }
}
