<?php
/**
 * Kegiatan Galeri API Endpoint
 * Upload, view, delete foto kegiatan
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
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

// Upload directory - use absolute path
$uploadDir = dirname(__DIR__) . '/uploads/kegiatan/';

// Ensure upload directory exists
if (!file_exists($uploadDir)) {
    if (!mkdir($uploadDir, 0755, true)) {
        jsonResponse(['success' => false, 'message' => 'Failed to create upload directory']);
    }
}

switch ($method) {
    case 'GET':
        getGaleri($pdo, $user);
        break;
    case 'POST':
        uploadFoto($pdo, $user, $isStaff, $uploadDir);
        break;
    case 'DELETE':
        deleteFoto($pdo, $user, $isStaff, $uploadDir);
        break;
    default:
        jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

function getGaleri($pdo, $user) {
    if (!isset($_GET['kegiatan_id'])) {
        jsonResponse(['success' => false, 'message' => 'kegiatan_id required'], 400);
    }
    
    $kegiatanId = (int)$_GET['kegiatan_id'];
    
    $stmt = $pdo->prepare("
        SELECT g.*, u.nama as uploader_name 
        FROM kegiatan_galeri g 
        LEFT JOIN users u ON g.diupload_oleh = u.id 
        WHERE g.kegiatan_id = ? 
        ORDER BY g.created_at DESC
    ");
    $stmt->execute([$kegiatanId]);
    $galeri = $stmt->fetchAll();
    
    jsonResponse(['success' => true, 'data' => $galeri]);
}

function uploadFoto($pdo, $user, $isStaff, $uploadDir) {
    if (!$isStaff) {
        jsonResponse(['success' => false, 'message' => 'Only staff can upload photos'], 403);
    }
    
    // Check if file was uploaded
    if (!isset($_FILES['foto'])) {
        jsonResponse(['success' => false, 'message' => 'No file uploaded. Check if form has enctype="multipart/form-data"']);
    }
    
    if (empty($_POST['kegiatan_id'])) {
        jsonResponse(['success' => false, 'message' => 'kegiatan_id required'], 400);
    }
    
    $file = $_FILES['foto'];
    $kegiatanId = (int)$_POST['kegiatan_id'];
    $keterangan = $_POST['keterangan'] ?? '';
    
    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors = [
            UPLOAD_ERR_INI_SIZE => 'File exceeds server limit (upload_max_filesize)',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds form limit (MAX_FILE_SIZE)',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Server missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
        ];
        $errorMsg = $errors[$file['error']] ?? 'Unknown upload error (code: ' . $file['error'] . ')';
        jsonResponse(['success' => false, 'message' => $errorMsg]);
    }
    
    // Validate file type
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $allowedTypes)) {
        jsonResponse(['success' => false, 'message' => 'Invalid file type. Allowed: JPG, PNG, GIF, WEBP. Your file type: ' . $file['type']]);
    }
    
    // Validate file size (20MB)
    $maxSize = 20 * 1024 * 1024; // 20MB
    if ($file['size'] > $maxSize) {
        $sizeMB = round($file['size'] / 1024 / 1024, 2);
        jsonResponse(['success' => false, 'message' => 'File too large (' . $sizeMB . 'MB). Max 20MB']);
    }
    
    // Check if directory is writable
    if (!is_writable($uploadDir)) {
        jsonResponse(['success' => false, 'message' => 'Upload directory is not writable. Check permissions.']);
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $extension = strtolower($extension);
    $filename = 'kegiatan_' . $kegiatanId . '_' . time() . '_' . uniqid() . '.' . $extension;
    $filepath = $uploadDir . $filename;
    
    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        jsonResponse(['success' => false, 'message' => 'Failed to save uploaded file. Check directory permissions.']);
    }
    
    // Set proper permissions
    chmod($filepath, 0644);
    
    // Save to database
    try {
        $stmt = $pdo->prepare("
            INSERT INTO kegiatan_galeri (kegiatan_id, nama_file, file_path, file_size, mime_type, keterangan, diupload_oleh) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $kegiatanId,
            $file['name'],
            $filename,
            $file['size'],
            $file['type'],
            $keterangan,
            $user['id']
        ]);
        
        $fotoId = $pdo->lastInsertId();
        
        jsonResponse([
            'success' => true, 
            'message' => 'Foto berhasil diupload',
            'data' => [
                'id' => $fotoId,
                'nama_file' => $file['name'],
                'file_path' => $filename,
                'url' => 'uploads/kegiatan/' . $filename
            ]
        ]);
    } catch (PDOException $e) {
        // Delete uploaded file if database insert fails
        if (file_exists($filepath)) {
            unlink($filepath);
        }
        jsonResponse(['success' => false, 'message' => 'Database error: ' . $e->getMessage()], 500);
    }
}

function deleteFoto($pdo, $user, $isStaff, $uploadDir) {
    if (!$isStaff) {
        jsonResponse(['success' => false, 'message' => 'Unauthorized'], 403);
    }
    
    $input = getJsonInput();
    
    if (empty($input['id'])) {
        jsonResponse(['success' => false, 'message' => 'Foto ID required'], 400);
    }
    
    $fotoId = (int)$input['id'];
    
    try {
        // Get file info
        $stmt = $pdo->prepare("SELECT file_path FROM kegiatan_galeri WHERE id = ?");
        $stmt->execute([$fotoId]);
        $foto = $stmt->fetch();
        
        if (!$foto) {
            jsonResponse(['success' => false, 'message' => 'Foto not found'], 404);
        }
        
        // Delete file
        $filepath = $uploadDir . $foto['file_path'];
        if (file_exists($filepath)) {
            unlink($filepath);
        }
        
        // Delete from database
        $stmt = $pdo->prepare("DELETE FROM kegiatan_galeri WHERE id = ?");
        $stmt->execute([$fotoId]);
        
        jsonResponse(['success' => true, 'message' => 'Foto berhasil dihapus']);
    } catch (PDOException $e) {
        jsonResponse(['success' => false, 'message' => 'Failed to delete foto'], 500);
    }
}
