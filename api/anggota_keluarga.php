<?php
/**
 * Anggota Keluarga Request API
 * Handle requests for adding family members with admin approval
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
    case 'create':
        createRequest($pdo);
        break;
    
    case 'get_all':
        getAllRequests($pdo);
        break;
    
    case 'get_by_user':
        getRequestsByUser($pdo);
        break;
    
    case 'approve':
        approveRequest($pdo);
        break;
    
    case 'reject':
        rejectRequest($pdo);
        break;
    
    case 'delete':
        deleteRequest($pdo);
        break;
    
    default:
        jsonResponse(['success' => false, 'message' => 'Invalid action'], 400);
}

/**
 * Create new request
 */
function createRequest($pdo) {
    checkMethod('POST');
    
    $data = getJsonInput();
    
    // Validate required fields
    $validation = validateRequired($data, ['user_id', 'no_kk', 'nama_lengkap', 'hubungan', 'jenis_kelamin']);
    if (!$validation['valid']) {
        jsonResponse(['success' => false, 'message' => $validation['message']], 400);
    }
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO anggota_keluarga_request 
            (user_id, no_kk, nama_lengkap, nik, hubungan, jenis_kelamin, tempat_lahir, tanggal_lahir, 
             pekerjaan, status_perkawinan, alasan, dibuat_oleh, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')
        ");
        
        $stmt->execute([
            $data['user_id'],
            $data['no_kk'],
            $data['nama_lengkap'],
            $data['nik'] ?? null,
            $data['hubungan'],
            $data['jenis_kelamin'],
            $data['tempat_lahir'] ?? null,
            $data['tanggal_lahir'] ?? null,
            $data['pekerjaan'] ?? null,
            $data['status_perkawinan'] ?? null,
            $data['alasan'] ?? null,
            $data['dibuat_oleh'] ?? $data['user_id']
        ]);
        
        $requestId = $pdo->lastInsertId();
        
        jsonResponse([
            'success' => true,
            'message' => 'Request berhasil diajukan. Menunggu persetujuan admin.',
            'request_id' => $requestId
        ]);
        
    } catch (PDOException $e) {
        jsonResponse(['success' => false, 'message' => 'Database error: ' . $e->getMessage()], 500);
    }
}

/**
 * Get all requests (for admin)
 */
function getAllRequests($pdo) {
    checkMethod('GET');
    
    try {
        $stmt = $pdo->prepare("
            SELECT 
                r.id,
                r.user_id,
                r.no_kk,
                r.nama_lengkap,
                r.nik,
                r.hubungan,
                r.jenis_kelamin,
                r.tempat_lahir,
                r.tanggal_lahir,
                r.pekerjaan,
                r.status_perkawinan,
                r.alasan,
                r.status,
                r.catatan_admin,
                r.created_at,
                r.updated_at,
                u.nama as pemohon_nama,
                u.email as pemohon_email,
                u.whatsapp as pemohon_whatsapp
            FROM anggota_keluarga_request r
            LEFT JOIN users u ON r.user_id = u.id
            ORDER BY r.created_at DESC
        ");
        $stmt->execute();
        $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Debug log
        error_log('Requests found: ' . count($requests));
        
        jsonResponse([
            'success' => true,
            'data' => $requests
        ]);
        
    } catch (PDOException $e) {
        error_log('Database error: ' . $e->getMessage());
        jsonResponse(['success' => false, 'message' => 'Database error: ' . $e->getMessage()], 500);
    }
}

/**
 * Get requests by user ID
 */
function getRequestsByUser($pdo) {
    checkMethod('GET');
    
    $userId = $_GET['user_id'] ?? '';
    
    if (empty($userId)) {
        jsonResponse(['success' => false, 'message' => 'User ID required'], 400);
    }
    
    try {
        $stmt = $pdo->prepare("
            SELECT * FROM anggota_keluarga_request 
            WHERE user_id = ? 
            ORDER BY created_at DESC
        ");
        $stmt->execute([$userId]);
        $requests = $stmt->fetchAll();
        
        jsonResponse([
            'success' => true,
            'data' => $requests
        ]);
        
    } catch (PDOException $e) {
        jsonResponse(['success' => false, 'message' => 'Database error: ' . $e->getMessage()], 500);
    }
}

/**
 * Approve request
 */
function approveRequest($pdo) {
    checkMethod('POST');
    
    $data = getJsonInput();
    
    if (empty($data['id'])) {
        jsonResponse(['success' => false, 'message' => 'Request ID required'], 400);
    }
    
    try {
        // Get request details
        $stmt = $pdo->prepare("SELECT * FROM anggota_keluarga_request WHERE id = ?");
        $stmt->execute([$data['id']]);
        $request = $stmt->fetch();
        
        if (!$request) {
            jsonResponse(['success' => false, 'message' => 'Request not found'], 404);
        }
        
        // Update request status
        $stmt = $pdo->prepare("
            UPDATE anggota_keluarga_request 
            SET status = 'approved', catatan_admin = ?, disetujui_oleh = ?, updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([
            $data['catatan'] ?? 'Disetujui',
            $data['disetujui_oleh'] ?? null,
            $data['id']
        ]);
        
        // Optionally, add the family member to users table
        if ($data['add_to_users'] ?? false) {
            $uuid = generateUUID();
            $hashedPassword = password_hash('password123', PASSWORD_DEFAULT); // Default password

            // Get user data to copy RT/RW info
            $stmt = $pdo->prepare("SELECT rt, rw, no_rumah, whatsapp, no_kk FROM users WHERE id = ?");
            $stmt->execute([$request['user_id']]);
            $userData = $stmt->fetch();

            if ($userData) {
                // Check if NIK already exists
                if ($request['nik']) {
                    $checkStmt = $pdo->prepare("SELECT id FROM users WHERE nik = ?");
                    $checkStmt->execute([$request['nik']]);
                    if ($checkStmt->fetch()) {
                        // NIK already exists, skip adding
                        error_log('NIK already exists: ' . $request['nik']);
                    } else {
                        $stmt = $pdo->prepare("
                            INSERT INTO users (uuid, email, password, role, nama, nik, no_kk, rt, rw, no_rumah, whatsapp, pekerjaan, status, status_keluarga)
                            VALUES (?, ?, ?, 'warga', ?, ?, ?, ?, ?, ?, ?, ?, 'aktif', 'anggota')
                        ");
                        $stmt->execute([
                            $uuid,
                            strtolower($request['nama_lengkap']) . '@temp.local',
                            $hashedPassword,
                            $request['nama_lengkap'],
                            $request['nik'],
                            $request['no_kk'] ?: $userData['no_kk'],
                            $userData['rt'],
                            $userData['rw'],
                            $userData['no_rumah'],
                            $userData['whatsapp'],
                            $request['pekerjaan'] ?? '-'
                        ]);
                        error_log('User added with ID: ' . $pdo->lastInsertId() . ' as ANGGOTA');
                    }
                } else {
                    // No NIK, create user without NIK
                    $stmt = $pdo->prepare("
                        INSERT INTO users (uuid, email, password, role, nama, no_kk, rt, rw, no_rumah, whatsapp, pekerjaan, status, status_keluarga)
                        VALUES (?, ?, ?, 'warga', ?, ?, ?, ?, ?, ?, ?, 'aktif', 'anggota')
                    ");
                    $stmt->execute([
                        $uuid,
                        strtolower($request['nama_lengkap']) . '@temp.local',
                        $hashedPassword,
                        $request['nama_lengkap'],
                        $request['no_kk'] ?: $userData['no_kk'],
                        $userData['rt'],
                        $userData['rw'],
                        $userData['no_rumah'],
                        $userData['whatsapp'],
                        $request['pekerjaan'] ?? '-'
                    ]);
                    error_log('User added (no NIK) with ID: ' . $pdo->lastInsertId() . ' as ANGGOTA');
                }
            } else {
                error_log('User data not found for ID: ' . $request['user_id']);
            }
        } else {
            error_log('add_to_users is false or not set');
        }
        
        jsonResponse([
            'success' => true,
            'message' => 'Request disetujui'
        ]);
        
    } catch (PDOException $e) {
        jsonResponse(['success' => false, 'message' => 'Database error: ' . $e->getMessage()], 500);
    }
}

/**
 * Reject request
 */
function rejectRequest($pdo) {
    checkMethod('POST');
    
    $data = getJsonInput();
    
    if (empty($data['id'])) {
        jsonResponse(['success' => false, 'message' => 'Request ID required'], 400);
    }
    
    try {
        $stmt = $pdo->prepare("
            UPDATE anggota_keluarga_request 
            SET status = 'rejected', catatan_admin = ?, updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([
            $data['catatan'] ?? 'Ditolak',
            $data['id']
        ]);
        
        jsonResponse([
            'success' => true,
            'message' => 'Request ditolak'
        ]);
        
    } catch (PDOException $e) {
        jsonResponse(['success' => false, 'message' => 'Database error: ' . $e->getMessage()], 500);
    }
}

/**
 * Delete request
 */
function deleteRequest($pdo) {
    checkMethod('DELETE');
    
    $id = $_GET['id'] ?? '';
    
    if (empty($id)) {
        jsonResponse(['success' => false, 'message' => 'Request ID required'], 400);
    }
    
    try {
        $stmt = $pdo->prepare("DELETE FROM anggota_keluarga_request WHERE id = ?");
        $stmt->execute([$id]);
        
        if ($stmt->rowCount() === 0) {
            jsonResponse(['success' => false, 'message' => 'Request not found'], 404);
        }
        
        jsonResponse([
            'success' => true,
            'message' => 'Request dihapus'
        ]);
        
    } catch (PDOException $e) {
        jsonResponse(['success' => false, 'message' => 'Database error: ' . $e->getMessage()], 500);
    }
}
