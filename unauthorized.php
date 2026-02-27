<?php
/**
 * Unauthorized Access Page
 * VILLA BINTARO REGENCY RT/RW Digital
 */

require_once __DIR__ . '/includes/auth.php';

// Check if logged in
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user = getCurrentUser();

// If user is null for some reason, redirect to login
if (!$user) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akses Ditolak - VILLA BINTARO REGENCY RT/RW Digital</title>
    <link rel="stylesheet" href="css/style-premium.css">
</head>
<body class="auth-page">
    <div class="auth-container" style="text-align: center;">
        <div class="auth-right" style="width: 100%;">
            <div style="font-size: 5rem; margin-bottom: var(--space-4);">🚫</div>
            <h1 style="font-family: 'Playfair Display', serif; margin-bottom: var(--space-4);">Akses Ditolak</h1>
            <p style="font-size: 1.1rem; color: var(--slate-600); margin-bottom: var(--space-6);">
                Maaf, Anda tidak memiliki izin untuk mengakses halaman ini.
            </p>
            <div style="padding: var(--space-4); background: var(--slate-50); border-radius: var(--radius-md); margin-bottom: var(--space-6);">
                <p style="margin-bottom: var(--space-2);"><strong>Role Anda:</strong> <?= getRoleDisplayName($user['role']) ?></p>
                <p style="margin-bottom: var(--space-2);"><strong>Email:</strong> <?= sanitize($user['email']) ?></p>
            </div>
            <div style="display: flex; gap: var(--space-4); justify-content: center;">
                <a href="dashboard.php" class="btn btn-primary">🏠 Kembali ke Dashboard</a>
                <a href="logout.php" class="btn btn-outline">🚪 Logout</a>
            </div>
        </div>
    </div>
</body>
</html>
