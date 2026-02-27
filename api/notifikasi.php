<?php
/**
 * Notifikasi API Endpoint
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, PUT, OPTIONS');
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
$pdo = getDbConnection();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // Get notifications
    $stmt = $pdo->prepare("SELECT * FROM notifikasi WHERE user_id = ? ORDER BY created_at DESC LIMIT 50");
    $stmt->execute([$user['id']]);
    $notifikasi = $stmt->fetchAll();
    jsonResponse(['success' => true, 'data' => $notifikasi]);
    
} elseif ($method === 'PUT') {
    $input = getJsonInput();
    
    if (isset($input['id'])) {
        // Mark single as read
        $stmt = $pdo->prepare("UPDATE notifikasi SET sudah_dibaca = 1 WHERE id = ? AND user_id = ?");
        $stmt->execute([$input['id'], $user['id']]);
        jsonResponse(['success' => true, 'message' => 'Notifikasi ditandai sebagai dibaca']);
        
    } elseif (isset($input['action']) && $input['action'] === 'mark_all_read') {
        // Mark all as read
        $stmt = $pdo->prepare("UPDATE notifikasi SET sudah_dibaca = 1 WHERE user_id = ?");
        $stmt->execute([$user['id']]);
        jsonResponse(['success' => true, 'message' => 'Semua notifikasi ditandai sebagai dibaca']);
    }
    
    jsonResponse(['success' => false, 'message' => 'Invalid request']);
}
