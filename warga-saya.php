<?php
/**
 * Warga Saya Page - Profile warga untuk user biasa
 * VILLA BINTARO REGENCY RT/RW Digital
 */

require_once __DIR__ . '/api/config.php';
require_once __DIR__ . '/includes/auth.php';
requireLogin();

$user = getCurrentUser();

// Fetch user data
try {
    $pdo = getDbConnection();
    
    // Get user's family members
    $stmt = $pdo->prepare("SELECT * FROM anggota_keluarga WHERE user_id = ? ORDER BY hubungan DESC");
    $stmt->execute([$user['id']]);
    $familyMembers = $stmt->fetchAll();
    
    // Get user's iuran
    $stmt = $pdo->prepare("
        SELECT * FROM iuran WHERE user_id = ? 
        ORDER BY created_at DESC 
        LIMIT 10
    ");
    $stmt->execute([$user['id']]);
    $iuranList = $stmt->fetchAll();
    
    // Get user's letters
    $stmt = $pdo->prepare("
        SELECT * FROM layanan_surat WHERE user_id = ? 
        ORDER BY created_at DESC 
        LIMIT 10
    ");
    $stmt->execute([$user['id']]);
    $letters = $stmt->fetchAll();
    
    // Count totals
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM iuran WHERE user_id = ? AND status = 'lunas'");
    $stmt->execute([$user['id']]);
    $totalPaid = $stmt->fetch()['total'] ?? 0;
    
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM iuran WHERE user_id = ? AND status = 'belum_bayar'");
    $stmt->execute([$user['id']]);
    $totalUnpaid = $stmt->fetch()['total'] ?? 0;
    
} catch (PDOException $e) {
    $familyMembers = [];
    $iuranList = [];
    $letters = [];
    $totalPaid = 0;
    $totalUnpaid = 0;
}

$hubunganConfig = [
    'kepala_keluarga' => ['label' => 'Kepala Keluarga', 'icon' => '👨'],
    'istri' => ['label' => 'Istri', 'icon' => '👩'],
    'suami' => ['label' => 'Suami', 'icon' => '👨'],
    'anak' => ['label' => 'Anak', 'icon' => '👶'],
    'lainnya' => ['label' => 'Lainnya', 'icon' => '👤']
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Warga Saya - VILLA BINTARO REGENCY RT/RW Digital</title>
    <link rel="stylesheet" href="css/style-premium.css">
</head>
<body>
    <div class="dashboard">
        <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="sidebar-brand">
                    <div class="sidebar-brand-icon">🏘️</div>
                    <span>VILLA BINTARO REGENCY</span>
                </div>
            </div>
            <nav class="sidebar-menu">
                <a href="dashboard.php" class="sidebar-menu-item"><span class="sidebar-menu-icon">📊</span><span>Dashboard</span></a>
                <div style="margin: var(--space-4) var(--space-6); padding-top: var(--space-4); border-top: 1px solid rgba(255,255,255,0.05);">
                    <span style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.1em; color: var(--slate-500); font-weight: 600;">Akun Saya</span>
                </div>
                <a href="warga-saya.php" class="sidebar-menu-item active"><span class="sidebar-menu-icon">👤</span><span>Warga Saya</span></a>
                <a href="keluarga-saya.php" class="sidebar-menu-item"><span class="sidebar-menu-icon">👨‍👩‍👧‍👦</span><span>Keluarga Saya</span></a>
                <a href="iuran-saya.php" class="sidebar-menu-item"><span class="sidebar-menu-icon">💰</span><span>Iuran Saya</span></a>
                <div style="margin: var(--space-4) var(--space-6); padding-top: var(--space-4); border-top: 1px solid rgba(255,255,255,0.05);">
                    <span style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.1em; color: var(--slate-500); font-weight: 600;">Layanan</span>
                </div>
                <a href="layanan.php" class="sidebar-menu-item"><span class="sidebar-menu-icon">📋</span><span>Layanan Surat</span></a>
                <a href="kegiatan.php" class="sidebar-menu-item"><span class="sidebar-menu-icon">📅</span><span>Kegiatan</span></a>
                <a href="pengumuman.php" class="sidebar-menu-item"><span class="sidebar-menu-icon">📢</span><span>Pengumuman</span></a>
                <a href="notifikasi.php" class="sidebar-menu-item"><span class="sidebar-menu-icon">🔔</span><span>Notifikasi</span></a>
            </nav>
            <div class="sidebar-user">
                <div class="sidebar-user-avatar"><?= getUserAvatar($user['nama']) ?></div>
                <div class="sidebar-user-info">
                    <div class="sidebar-user-name"><?= sanitize($user['nama']) ?></div>
                    <div class="sidebar-user-role"><?= getRoleDisplayName($user['role']) ?></div>
                </div>
                <button onclick="logout()" class="btn btn-ghost btn-icon" title="Logout">🚪</button>
            </div>
        </aside>

        <main class="main-content">
            <header class="topbar">
                <div class="topbar-left">
                    <button class="menu-toggle" id="menuToggle">☰</button>
                    <h1 class="page-title">Profil Saya</h1>
                </div>
                <div class="topbar-right">
                    <button class="btn btn-sm btn-primary" onclick="openModal('editProfileModal')">✏️ Edit Profil</button>
                </div>
            </header>

            <div class="content">
                <!-- Profile Card -->
                <div class="card">
                    <div class="card-body">
                        <div style="display: flex; gap: 24px; align-items: flex-start;">
                            <div style="width: 100px; height: 100px; background: var(--primary-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 2.5rem; font-weight: bold;"><?= strtoupper(substr($user['nama'], 0, 1)) ?></div>
                            <div style="flex: 1;">
                                <h2 style="font-size: 1.5rem; margin-bottom: 8px;"><?= sanitize($user['nama']) ?></h2>
                                <p style="color: var(--gray-500); margin-bottom: 16px;"><?= sanitize($user['email']) ?></p>
                                
                                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
                                    <div>
                                        <div style="font-size: 0.85rem; color: var(--gray-500);">NIK</div>
                                        <div style="font-weight: 500;"><?= sanitize($user['nik']) ?></div>
                                    </div>
                                    <div>
                                        <div style="font-size: 0.85rem; color: var(--gray-500);">No KK</div>
                                        <div style="font-weight: 500;"><?= sanitize($user['no_kk']) ?></div>
                                    </div>
                                    <div>
                                        <div style="font-size: 0.85rem; color: var(--gray-500);">RT/RW</div>
                                        <div style="font-weight: 500;">RT <?= $user['rt'] ?? '-' ?> / RW <?= $user['rw'] ?? '-' ?></div>
                                    </div>
                                    <div>
                                        <div style="font-size: 0.85rem; color: var(--gray-500);">No Rumah</div>
                                        <div style="font-weight: 500;"><?= $user['no_rumah'] ?? '-' ?></div>
                                    </div>
                                    <div>
                                        <div style="font-size: 0.85rem; color: var(--gray-500);">WhatsApp</div>
                                        <div style="font-weight: 500;"><?= $user['whatsapp'] ?? '-' ?></div>
                                    </div>
                                    <div>
                                        <div style="font-size: 0.85rem; color: var(--gray-500);">Pekerjaan</div>
                                        <div style="font-weight: 500;"><?= $user['pekerjaan'] ?? '-' ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stats -->
                <div class="dashboard-stats">
                    <div class="stat-card">
                        <div class="stat-card-icon green">✅</div>
                        <div class="stat-card-content">
                            <h3><?= $totalPaid ?></h3>
                            <p>Iuran Lunas</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-card-icon red">⏳</div>
                        <div class="stat-card-content">
                            <h3><?= $totalUnpaid ?></h3>
                            <p>Belum Dibayar</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-card-icon blue">👨‍👩‍👧‍👦</div>
                        <div class="stat-card-content">
                            <h3><?= count($familyMembers) + 1 ?></h3>
                            <p>Anggota Keluarga</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-card-icon orange">📋</div>
                        <div class="stat-card-content">
                            <h3><?= count($letters) ?></h3>
                            <p>Riwayat Surat</p>
                        </div>
                    </div>
                </div>

                <!-- Recent Iuran -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Iuran Terakhir</h3>
                        <a href="iuran-saya.php" class="btn btn-sm btn-outline">Lihat Semua</a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($iuranList)): ?>
                            <p style="color: var(--gray-500); text-align: center;">Belum ada data iuran</p>
                        <?php else: ?>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Jenis</th>
                                        <th>Bulan</th>
                                        <th>Nominal</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach (array_slice($iuranList, 0, 5) as $i): ?>
                                    <tr>
                                        <td><?= ucfirst($i['jenis_iuran']) ?></td>
                                        <td><?= date('F Y', strtotime($i['bulan'] . '-01')) ?></td>
                                        <td>Rp <?= number_format($i['nominal'], 0, ',', '.') ?></td>
                                        <td>
                                            <?php if ($i['status'] === 'lunas'): ?>
                                                <span class="badge badge-success">Lunas</span>
                                            <?php else: ?>
                                                <span class="badge badge-danger">Belum Dibayar</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Edit Profile Modal -->
    <div class="modal-overlay" id="editProfileModal">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title">Edit Profil</h3>
                <button class="modal-close" onclick="closeModal('editProfileModal')">&times;</button>
            </div>
            <form id="editProfileForm" onsubmit="handleEdit(event)">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">WhatsApp</label>
                        <input type="text" class="form-control" name="whatsapp" value="<?= sanitize($user['whatsapp'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Pekerjaan</label>
                        <input type="text" class="form-control" name="pekerjaan" value="<?= sanitize($user['pekerjaan'] ?? '') ?>">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('editProfileModal')">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <script src="js/main.js"></script>
    <script>
        function logout() {
            if (confirm('Apakah Anda yakin ingin logout?')) {
                window.location.href = 'logout.php';
            }
            return false;
        }

        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
        }

        document.getElementById('menuToggle')?.addEventListener('click', toggleSidebar);

        async function handleEdit(e) {
            e.preventDefault();
            const form = e.target;
            const formData = new FormData(form);
            const data = Object.fromEntries(formData);

            try {
                const response = await fetch('api/warga.php', {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ ...data, id: <?= $user['id'] ?> })
                });
                const result = await response.json();

                if (result.success) {
                    alert('Profil berhasil diupdate');
                    closeModal('editProfileModal');
                    window.location.reload();
                } else {
                    alert('Gagal mengupdate profil: ' + result.message);
                }
            } catch (error) {
                alert('Terjadi kesalahan');
                console.error(error);
            }
        }
    </script>
</body>
</html>
