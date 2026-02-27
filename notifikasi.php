<?php
/**
 * Notifikasi Page
 * VILLA BINTARO REGENCY RT/RW Digital
 */

require_once __DIR__ . '/api/config.php';
require_once __DIR__ . '/includes/auth.php';
requireLogin();

$user = getCurrentUser();

// Fetch notifikasi from database
try {
    $pdo = getDbConnection();
    
    $filter = $_GET['filter'] ?? 'all';
    $whereClause = "user_id = ?";
    $params = [$user['id']];
    
    if ($filter === 'unread') {
        $whereClause .= " AND sudah_dibaca = 0";
    } elseif ($filter === 'read') {
        $whereClause .= " AND sudah_dibaca = 1";
    }
    
    $stmt = $pdo->prepare("
        SELECT * FROM notifikasi 
        WHERE $whereClause 
        ORDER BY created_at DESC 
        LIMIT 50
    ");
    $stmt->execute($params);
    $notifikasiList = $stmt->fetchAll();
    
    // Count unread
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM notifikasi WHERE user_id = ? AND sudah_dibaca = 0");
    $stmt->execute([$user['id']]);
    $unreadCount = $stmt->fetch()['total'] ?? 0;
    
} catch (PDOException $e) {
    $notifikasiList = [];
    $unreadCount = 0;
}

$tipeConfig = [
    'info' => ['color' => 'blue', 'icon' => 'ℹ️'],
    'warning' => ['color' => 'yellow', 'icon' => '⚠️'],
    'success' => ['color' => 'green', 'icon' => '✅'],
    'danger' => ['color' => 'red', 'icon' => '❌']
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifikasi - VILLA BINTARO REGENCY RT/RW Digital</title>
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
                <a href="warga.php" class="sidebar-menu-item"><span class="sidebar-menu-icon">👥</span><span>Data Warga</span></a>
                <a href="kk.php" class="sidebar-menu-item"><span class="sidebar-menu-icon">📋</span><span>Kartu Keluarga</span></a>
                <a href="requests.php" class="sidebar-menu-item"><span class="sidebar-menu-icon">⏳</span><span>Request Anggota</span></a>
                <div style="margin: var(--space-4) var(--space-6); padding-top: var(--space-4); border-top: 1px solid rgba(255,255,255,0.05);">
                    <span style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.1em; color: var(--slate-500); font-weight: 600;">Komunikasi</span>
                </div>
                <a href="pengumuman.php" class="sidebar-menu-item"><span class="sidebar-menu-icon">📢</span><span>Pengumuman</span></a>
                <a href="notifikasi.php" class="sidebar-menu-item active"><span class="sidebar-menu-icon">🔔</span><span>Notifikasi</span></a>
                <div style="margin: var(--space-4) var(--space-6); padding-top: var(--space-4); border-top: 1px solid rgba(255,255,255,0.05);">
                    <span style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.1em; color: var(--slate-500); font-weight: 600;">Layanan</span>
                </div>
                <a href="layanan.php" class="sidebar-menu-item"><span class="sidebar-menu-icon">📋</span><span>Layanan Surat</span></a>
                <a href="iuran.php" class="sidebar-menu-item"><span class="sidebar-menu-icon">💰</span><span>Kas & Iuran</span></a>
                <a href="kegiatan.php" class="sidebar-menu-item"><span class="sidebar-menu-icon">📅</span><span>Kegiatan</span></a>
                <a href="laporan.php" class="sidebar-menu-item"><span class="sidebar-menu-icon">📈</span><span>Laporan</span></a>
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
                    <h1 class="page-title">Notifikasi</h1>
                </div>
                <div class="topbar-right">
                    <button class="btn btn-sm btn-outline" onclick="markAllAsRead()">✓ Tandai Semua Dibaca</button>
                </div>
            </header>

            <div class="content">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Semua Notifikasi</h3>
                        <div style="display: flex; gap: 12px;">
                            <select class="form-control" style="width: 150px;" onchange="window.location.href='?filter=' + this.value">
                                <option value="">Semua</option>
                                <option value="unread" <?= $filter === 'unread' ? 'selected' : '' ?>>Belum Dibaca</option>
                                <option value="read" <?= $filter === 'read' ? 'selected' : '' ?>>Sudah Dibaca</option>
                            </select>
                        </div>
                    </div>
                    <div class="card-body" style="padding: 0;">
                        <?php if (empty($notifikasiList)): ?>
                            <div style="text-align: center; padding: var(--space-6); color: var(--slate-500);">
                                <div style="font-size: 4rem; margin-bottom: 16px;">🔔</div>
                                <p>Tidak ada notifikasi</p>
                            </div>
                        <?php else: ?>
                            <ul class="activity-list">
                                <?php foreach ($notifikasiList as $n): 
                                    $tipe = $tipeConfig[$n['tipe']] ?? $tipeConfig['info'];
                                ?>
                                <li class="activity-item" style="<?= $n['sudah_dibaca'] ? 'opacity: 0.6;' : 'background: var(--gray-50);' ?>">
                                    <div class="activity-icon <?= $tipe['color'] ?>"><?= $tipe['icon'] ?></div>
                                    <div class="activity-content">
                                        <h4><?= sanitize($n['judul']) ?></h4>
                                        <p><?= sanitize($n['isi']) ?></p>
                                        <span class="activity-time"><?= timeAgo($n['created_at']) ?></span>
                                    </div>
                                    <?php if (!$n['sudah_dibaca']): ?>
                                    <button class="btn btn-sm btn-outline" onclick="markAsRead(<?= $n['id'] ?>)">✓</button>
                                    <?php endif; ?>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
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

        async function markAsRead(id) {
            try {
                await fetch('api/notifikasi.php', {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id, action: 'mark_read' })
                });
                window.location.reload();
            } catch (error) {
                alert('Gagal menandai notifikasi');
            }
        }

        async function markAllAsRead() {
            try {
                await fetch('api/notifikasi.php', {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'mark_all_read' })
                });
                window.location.reload();
            } catch (error) {
                alert('Gagal menandai semua notifikasi');
            }
        }
    </script>
</body>
</html>
