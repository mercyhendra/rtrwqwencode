<?php
/**
 * Login Page
 * VILLA BINTARO REGENCY RT/RW Digital
 */

require_once __DIR__ . '/includes/auth.php';

// Redirect if already logged in
if (isLoggedIn()) {
    $user = getCurrentUser();
    if ($user['role'] === ROLE_WARGA) {
        redirect('warga-saya.php');
    } else {
        redirect('dashboard.php');
    }
}

// Handle POST request
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);

    $result = login($email, $password);

    if ($result['success']) {
        // Set remember me cookie
        if ($remember) {
            setcookie('rememberedEmail', $email, time() + (30 * 24 * 60 * 60), '/');
        } else {
            setcookie('rememberedEmail', '', time() - 3600, '/');
        }

        // Redirect based on role
        $userRole = getCurrentUserRole();
        if ($userRole === ROLE_WARGA) {
            redirect('warga-saya.php');
        } else {
            redirect('dashboard.php');
        }
    } else {
        $error = $result['message'];
    }
}

// Get remembered email from cookie
$rememberedEmail = $_COOKIE['rememberedEmail'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - VILLA BINTARO REGENCY RT/RW Digital</title>
    <link rel="stylesheet" href="css/style-premium.css">
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-left">
            <h1 style="font-family: 'Playfair Display', serif;">Selamat Datang Kembali!</h1>
            <p>Masuk untuk mengelola sistem VILLA BINTARO REGENCY RT/RW Digital Anda</p>
            <ul class="auth-features">
                <li>
                    <div class="auth-feature-icon">✓</div>
                    <span>Kelola data warga dengan mudah</span>
                </li>
                <li>
                    <div class="auth-feature-icon">✓</div>
                    <span>Buat pengumuman untuk warga</span>
                </li>
                <li>
                    <div class="auth-feature-icon">✓</div>
                    <span>Pantau iuran warga secara transparan</span>
                </li>
                <li>
                    <div class="auth-feature-icon">✓</div>
                    <span>Kelola kegiatan lingkungan</span>
                </li>
            </ul>
        </div>
        <div class="auth-right">
            <div class="auth-header">
                <h2 style="font-family: 'Playfair Display', serif;">Login</h2>
                <p>Masukkan kredensial Anda untuk masuk</p>
            </div>

            <?php if ($error): ?>
                <div style="padding: var(--space-3); background: #fee; border: 1px solid #fcc; border-radius: var(--radius-md); color: #c00; margin-bottom: var(--space-4);">
                    <?= sanitize($error) ?>
                </div>
            <?php endif; ?>

            <form class="auth-form" method="POST" action="">
                <div class="form-group">
                    <label class="form-label" for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-control" 
                           placeholder="nama@email.com" value="<?= sanitize($rememberedEmail) ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control" 
                           placeholder="••••••••" required>
                </div>
                <div class="form-group" style="display: flex; justify-content: space-between; align-items: center;">
                    <label style="display: flex; align-items: center; gap: var(--space-2); cursor: pointer;">
                        <input type="checkbox" id="remember" name="remember" <?= $rememberedEmail ? 'checked' : '' ?>>
                        <span style="font-size: 0.9rem; color: var(--slate-600);">Ingat saya</span>
                    </label>
                    <a href="reset_password.php" style="color: var(--primary-color); font-size: 0.9rem; font-weight: 500;">Lupa password?</a>
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%; padding: var(--space-4); font-size: 1rem;">Masuk</button>
            </form>
            <div class="auth-footer">
                <p>Belum punya akun? <a href="register.php">Daftar sekarang</a></p>
                <p style="margin-top: var(--space-4);"><a href="index.php">← Kembali ke beranda</a></p>
                <div style="margin-top: var(--space-6); padding: var(--space-4); background: var(--slate-50); border-radius: var(--radius-md); font-size: 0.85rem; border: 1px solid var(--slate-100);">
                    <strong>📝 Demo Login:</strong><br>
                    <div style="margin-top: var(--space-2); color: var(--slate-600);">
                        <strong>Admin:</strong> admin@rtrw.com / admin123<br>
                        <strong>RT:</strong> rt@rtrw.com / rt123<br>
                        <strong>Warga:</strong> warga@rtrw.com / warga123
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
