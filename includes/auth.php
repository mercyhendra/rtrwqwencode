<?php
/**
 * Authentication & Session Management
 * VILLA BINTARO REGENCY RT/RW Digital
 */

// Error reporting for development (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors to users

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database configuration
if (file_exists(__DIR__ . '/../api/config.php')) {
    require_once __DIR__ . '/../api/config.php';
} else {
    die('Configuration file not found. Please ensure api/config.php exists.');
}

// User roles
define('ROLE_ADMIN', 'admin');
define('ROLE_RT', 'rt');
define('ROLE_RW', 'rw');
define('ROLE_WARGA', 'warga');

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Get current user data
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }

    try {
        $pdo = getDbConnection();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        return null;
    }
}

/**
 * Get current user ID
 */
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current user role
 */
function getCurrentUserRole() {
    return $_SESSION['user_role'] ?? null;
}

/**
 * Login user
 */
function login($email, $password) {
    try {
        $pdo = getDbConnection();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user) {
            return ['success' => false, 'message' => 'Email tidak ditemukan'];
        }

        if (!verifyPassword($password, $user['password'])) {
            return ['success' => false, 'message' => 'Password salah'];
        }

        // Set session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_name'] = $user['name'];

        return [
            'success' => true,
            'message' => 'Login berhasil',
            'user' => [
                'id' => $user['id'],
                'email' => $user['email'],
                'role' => $user['role'],
                'name' => $user['name']
            ]
        ];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()];
    }
}

/**
 * Logout user
 */
function logout() {
    session_unset();
    session_destroy();
    session_start(); // Start new session
}

/**
 * Check if user has required role
 */
function hasRole($allowedRoles) {
    if (!isLoggedIn()) {
        return false;
    }

    $currentRole = getCurrentUserRole();
    
    if (!is_array($allowedRoles)) {
        $allowedRoles = [$allowedRoles];
    }

    return in_array($currentRole, $allowedRoles);
}

/**
 * Require authentication and specific roles
 * Redirects to login if not authenticated
 * Redirects to unauthorized page if role not allowed
 */
function requireAuth($allowedRoles = null) {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }

    if ($allowedRoles !== null && !hasRole($allowedRoles)) {
        header('Location: unauthorized.php');
        exit;
    }
}

/**
 * Require authentication (any role)
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

/**
 * Check if user is admin/RT/RW (staff)
 */
function isStaff() {
    return hasRole([ROLE_ADMIN, ROLE_RT, ROLE_RW]);
}

/**
 * Get user avatar initial
 */
function getUserAvatar($name) {
    return strtoupper(substr($name, 0, 1));
}

/**
 * Get role display name
 */
function getRoleDisplayName($role) {
    $roles = [
        ROLE_ADMIN => 'Administrator',
        ROLE_RT => 'Ketua RT',
        ROLE_RW => 'Ketua RW',
        ROLE_WARGA => 'Warga'
    ];
    return $roles[$role] ?? 'Warga';
}

/**
 * Format currency (Rupiah)
 */
function formatRupiah($amount) {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

/**
 * Format date to Indonesian format
 */
function formatTanggal($date, $format = 'd/m/Y') {
    if (empty($date)) return '';
    return date($format, strtotime($date));
}

/**
 * Format datetime to Indonesian format
 */
function formatWaktu($datetime) {
    if (empty($datetime)) return '';
    return date('d/m/Y H:i', strtotime($datetime));
}

/**
 * Get time ago (e.g., "2 jam yang lalu")
 */
function timeAgo($datetime) {
    if (empty($datetime)) return '';
    
    $timestamp = strtotime($datetime);
    $diff = time() - $timestamp;
    
    if ($diff < 60) {
        return 'Baru saja';
    } elseif ($diff < 3600) {
        $minutes = floor($diff / 60);
        return $minutes . ' menit yang lalu';
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ' jam yang lalu';
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return $days . ' hari yang lalu';
    } else {
        return formatTanggal($datetime);
    }
}

/**
 * Generate CSRF token
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Set flash message
 */
function setFlash($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Get and clear flash message
 */
function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Get base URL
 */
function baseUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $basePath = dirname($_SERVER['SCRIPT_NAME']);
    return $protocol . '://' . $host . $basePath;
}

/**
 * Redirect to URL
 */
function redirect($url) {
    header('Location: ' . $url);
    exit;
}

/**
 * Sanitize input
 */
function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}
