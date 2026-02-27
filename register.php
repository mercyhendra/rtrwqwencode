<?php
/**
 * Register Page
 * VILLA BINTARO REGENCY RT/RW Digital
 */

require_once __DIR__ . '/includes/auth.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('dashboard.php');
}

// Handle POST request
$error = '';
$success = '';
$formData = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData = [
        'nama' => filter_input(INPUT_POST, 'nama', FILTER_SANITIZE_SPECIAL_CHARS),
        'no_kk' => filter_input(INPUT_POST, 'no_kk', FILTER_SANITIZE_SPECIAL_CHARS),
        'nik' => filter_input(INPUT_POST, 'nik', FILTER_SANITIZE_SPECIAL_CHARS),
        'rt' => filter_input(INPUT_POST, 'rt', FILTER_SANITIZE_SPECIAL_CHARS),
        'rw' => filter_input(INPUT_POST, 'rw', FILTER_SANITIZE_SPECIAL_CHARS),
        'no_rumah' => filter_input(INPUT_POST, 'no_rumah', FILTER_SANITIZE_SPECIAL_CHARS),
        'whatsapp' => filter_input(INPUT_POST, 'whatsapp', FILTER_SANITIZE_SPECIAL_CHARS),
        'pekerjaan' => filter_input(INPUT_POST, 'pekerjaan', FILTER_SANITIZE_SPECIAL_CHARS),
        'email' => filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL),
    ];

    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $agree = isset($_POST['agree']);

    // Validation
    if (!$agree) {
        $error = 'Anda harus menyetujui Syarat & Ketentuan!';
    } elseif ($password !== $confirmPassword) {
        $error = 'Password tidak cocok!';
    } elseif (strlen($password) < 8) {
        $error = 'Password minimal 8 karakter!';
    } elseif (strlen($formData['no_kk']) !== 16 || !ctype_digit($formData['no_kk'])) {
        $error = 'Nomor KK harus 16 digit angka!';
    } elseif (strlen($formData['nik']) !== 16 || !ctype_digit($formData['nik'])) {
        $error = 'NIK harus 16 digit angka!';
    } else {
        try {
            $pdo = getDbConnection();
            
            // Check if email already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$formData['email']]);
            if ($stmt->fetch()) {
                $error = 'Email sudah terdaftar!';
            }
            
            // Check if KK already exists
            if (!$error) {
                $stmt = $pdo->prepare("SELECT id FROM users WHERE no_kk = ?");
                $stmt->execute([$formData['no_kk']]);
                if ($stmt->fetch()) {
                    $error = 'Nomor KK sudah terdaftar! Setiap KK hanya bisa mendaftar 1 kali.';
                }
            }

            // Check if NIK already exists
            if (!$error) {
                $stmt = $pdo->prepare("SELECT id FROM users WHERE nik = ?");
                $stmt->execute([$formData['nik']]);
                if ($stmt->fetch()) {
                    $error = 'NIK sudah terdaftar!';
                }
            }

            // If no errors, proceed with registration
            if (!$error) {
                $pdo->beginTransaction();

                // Create user
                $hashedPassword = hashPassword($password);

                $stmt = $pdo->prepare("
                    INSERT INTO users (uuid, email, password, role, nama, nik, no_kk, rt, rw, no_rumah, whatsapp, pekerjaan, status)
                    VALUES (UUID(), ?, ?, 'warga', ?, ?, ?, ?, ?, ?, ?, ?, 'aktif')
                ");
                $stmt->execute([
                    $formData['email'],
                    $hashedPassword,
                    sanitize($formData['nama']),
                    $formData['nik'],
                    $formData['no_kk'],
                    $formData['rt'],
                    $formData['rw'],
                    $formData['no_rumah'],
                    $formData['whatsapp'],
                    $formData['pekerjaan']
                ]);

                $pdo->commit();

                setFlash('success', '✅ Registrasi berhasil! Silakan login dengan akun Anda.');
                redirect('login.php');
            }
        } catch (PDOException $e) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $error = 'Terjadi kesalahan: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - VILLA BINTARO REGENCY RT/RW Digital</title>
    <link rel="stylesheet" href="css/style-premium.css">
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-left">
            <h1>Bergabunglah Bersama Kami!</h1>
            <p>Daftar untuk mengakses sistem VILLA BINTARO REGENCY RT/RW Digital</p>
            <ul class="auth-features">
                <li>
                    <div class="auth-feature-icon">✓</div>
                    <span>Akses informasi warga terbaru</span>
                </li>
                <li>
                    <div class="auth-feature-icon">✓</div>
                    <span>Terima pengumuman secara real-time</span>
                </li>
                <li>
                    <div class="auth-feature-icon">✓</div>
                    <span>Urus administrasi dengan mudah</span>
                </li>
                <li>
                    <div class="auth-feature-icon">✓</div>
                    <span>Partisipasi dalam kegiatan lingkungan</span>
                </li>
            </ul>
        </div>
        <div class="auth-right">
            <div class="auth-header">
                <h2>Daftar Akun Baru</h2>
                <p>Lengkapi form di bawah untuk membuat akun</p>
            </div>

            <?php if ($error): ?>
                <div style="padding: var(--space-3); background: #fee; border: 1px solid #fcc; border-radius: var(--radius-md); color: #c00; margin-bottom: var(--space-4);">
                    <?= sanitize($error) ?>
                </div>
            <?php endif; ?>

            <form class="auth-form" method="POST" action="">
                <div class="form-group">
                    <label class="form-label" for="nama">Nama Lengkap (Kepala Keluarga)</label>
                    <input type="text" id="nama" name="nama" class="form-control" 
                           placeholder="John Doe" value="<?= sanitize($formData['nama'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="no_kk">Nomor Kartu Keluarga (KK)</label>
                    <input type="text" id="no_kk" name="no_kk" class="form-control" 
                           placeholder="16 digit No. KK" pattern="[0-9]{16}" 
                           value="<?= sanitize($formData['no_kk'] ?? '') ?>" required>
                    <small style="color: var(--gray-500); font-size: 0.85rem;">Setiap KK hanya bisa mendaftar 1 kali</small>
                </div>
                <div class="form-group">
                    <label class="form-label" for="nik">NIK Kepala Keluarga</label>
                    <input type="text" id="nik" name="nik" class="form-control" 
                           placeholder="16 digit NIK" pattern="[0-9]{16}" 
                           value="<?= sanitize($formData['nik'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="rt">RT/RW</label>
                    <select id="rt" name="rt" class="form-control" required>
                        <option value="">Pilih RT/RW</option>
                        <option value="001/001" <?= ($formData['rt'] ?? '') === '001/001' ? 'selected' : '' ?>>RT 001 / RW 001</option>
                        <option value="002/001" <?= ($formData['rt'] ?? '') === '002/001' ? 'selected' : '' ?>>RT 002 / RW 001</option>
                        <option value="003/001" <?= ($formData['rt'] ?? '') === '003/001' ? 'selected' : '' ?>>RT 003 / RW 001</option>
                        <option value="004/002" <?= ($formData['rt'] ?? '') === '004/002' ? 'selected' : '' ?>>RT 004 / RW 002</option>
                        <option value="005/002" <?= ($formData['rt'] ?? '') === '005/002' ? 'selected' : '' ?>>RT 005 / RW 002</option>
                        <option value="006/002" <?= ($formData['rt'] ?? '') === '006/002' ? 'selected' : '' ?>>RT 006 / RW 002</option>
                    </select>
                    <input type="hidden" name="rw" value="<?= sanitize(explode('/', $formData['rt'] ?? '')[1] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label" for="no_rumah">Nomor Rumah</label>
                    <input type="text" id="no_rumah" name="no_rumah" class="form-control" 
                           placeholder="Contoh: A-12" value="<?= sanitize($formData['no_rumah'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="whatsapp">Nomor WhatsApp</label>
                    <input type="tel" id="whatsapp" name="whatsapp" class="form-control" 
                           placeholder="08xxxxxxxxxx (format: 628xxx)" pattern="[0-9]{10,13}" 
                           value="<?= sanitize($formData['whatsapp'] ?? '') ?>" required>
                    <small style="color: var(--gray-500); font-size: 0.85rem;">Untuk notifikasi penting</small>
                </div>
                <div class="form-group">
                    <label class="form-label" for="pekerjaan">Pekerjaan</label>
                    <input type="text" id="pekerjaan" name="pekerjaan" class="form-control" 
                           placeholder="Contoh: Wiraswasta" value="<?= sanitize($formData['pekerjaan'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-control" 
                           placeholder="nama@email.com" value="<?= sanitize($formData['email'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control" 
                           placeholder="Minimal 8 karakter" minlength="8" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="confirm_password">Konfirmasi Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" 
                           placeholder="Ulangi password" required>
                </div>
                <div class="form-group">
                    <label style="display: flex; align-items: flex-start; gap: 8px; cursor: pointer;">
                        <input type="checkbox" id="agree" name="agree" required>
                        <span style="font-size: 0.9rem; color: var(--gray-600);">Saya setuju dengan <a href="#" style="color: var(--primary-color);">Syarat & Ketentuan</a> yang berlaku</span>
                    </label>
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%;">Daftar</button>
            </form>
            <div class="auth-footer">
                <p>Sudah punya akun? <a href="login.php">Login sekarang</a></p>
                <p style="margin-top: 16px;"><a href="index.php">← Kembali ke beranda</a></p>
            </div>
        </div>
    </div>

    <script>
        // Update RW hidden field when RT/RW changes
        document.getElementById('rt')?.addEventListener('change', function() {
            const parts = this.value.split('/');
            const rw = parts[1] || '';
            const rwInput = document.querySelector('input[name="rw"]');
            if (rwInput) {
                rwInput.value = rw;
            }
        });
    </script>
</body>
</html>
