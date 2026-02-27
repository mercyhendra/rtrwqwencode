<?php
/**
 * Error Page (404, 500, etc.)
 * VILLA BINTARO REGENCY RT/RW Digital
 */

$statusCode = http_response_code();
$title = 'Error';
$message = 'Terjadi kesalahan pada server.';

if ($statusCode === 404) {
    $title = 'Halaman Tidak Ditemukan';
    $message = 'Maaf, halaman yang Anda cari tidak dapat ditemukan.';
} elseif ($statusCode === 403) {
    $title = 'Akses Ditolak';
    $message = 'Anda tidak memiliki izin untuk mengakses halaman ini.';
} elseif ($statusCode === 500) {
    $title = 'Kesalahan Server';
    $message = 'Terjadi kesalahan pada server. Silakan coba lagi nanti.';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?> - VILLA BINTARO REGENCY RT/RW Digital</title>
    <link rel="stylesheet" href="css/style-premium.css">
</head>
<body class="auth-page">
    <div class="auth-container" style="text-align: center;">
        <div class="auth-right" style="width: 100%;">
            <div style="font-size: 5rem; margin-bottom: var(--space-4);">⚠️</div>
            <h1 style="font-family: 'Playfair Display', serif; margin-bottom: var(--space-4);"><?= $title ?></h1>
            <p style="font-size: 1.1rem; color: var(--slate-600); margin-bottom: var(--space-6);">
                <?= $message ?>
            </p>
            <div style="display: flex; gap: var(--space-4); justify-content: center;">
                <a href="index.php" class="btn btn-primary">🏠 Kembali ke Beranda</a>
                <?php if (!isLoggedIn()): ?>
                    <a href="login.php" class="btn btn-outline">🔐 Login</a>
                <?php else: ?>
                    <a href="dashboard.php" class="btn btn-outline">📊 Dashboard</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
