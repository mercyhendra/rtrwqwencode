<?php
/**
 * Dashboard Page
 * VILLA BINTARO REGENCY RT/RW Digital
 */

require_once __DIR__ . '/api/config.php';
require_once __DIR__ . '/includes/auth.php';

// Require authentication for admin/staff only
requireAuth([ROLE_ADMIN, ROLE_RT, ROLE_RW]);

$user = getCurrentUser();
$isStaff = isStaff();

// Fetch statistics from database
try {
    $pdo = getDbConnection();

    // Total warga (from users table)
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'warga' AND status = 'aktif'");
    $totalWarga = $stmt->fetch()['total'] ?? 0;

    // Total KK (unique no_kk from users)
    $stmt = $pdo->query("SELECT COUNT(DISTINCT no_kk) as total FROM users WHERE no_kk IS NOT NULL");
    $totalKK = $stmt->fetch()['total'] ?? 0;

    // Total pengumuman
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM pengumuman");
    $totalPengumuman = $stmt->fetch()['total'] ?? 0;

    // Total iuran bulan ini
    $stmt = $pdo->query("SELECT COALESCE(SUM(nominal), 0) as total FROM iuran WHERE bulan = DATE_FORMAT(CURRENT_DATE, '%Y-%m') AND status = 'lunas'");
    $totalIuran = $stmt->fetch()['total'] ?? 0;

    // Recent announcements
    $stmt = $pdo->prepare("
        SELECT p.*, u.nama as posted_by_name 
        FROM pengumuman p 
        LEFT JOIN users u ON p.dibuat_oleh = u.id 
        ORDER BY p.created_at DESC 
        LIMIT 3
    ");
    $stmt->execute();
    $recentAnnouncements = $stmt->fetchAll();

    // Pending requests
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM anggota_keluarga_request WHERE status = 'pending'");
    $pendingRequests = $stmt->fetch()['total'] ?? 0;

    // Pending letters
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM layanan_surat WHERE status = 'pending'");
    $pendingLetters = $stmt->fetch()['total'] ?? 0;

    // Upcoming activities
    $stmt = $pdo->prepare("
        SELECT * FROM kegiatan 
        WHERE tanggal_kegiatan >= CURRENT_DATE 
        ORDER BY tanggal_kegiatan ASC 
        LIMIT 3
    ");
    $stmt->execute();
    $upcomingActivities = $stmt->fetchAll();

    // Unread notifications for current user
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM notifikasi WHERE user_id = ? AND sudah_dibaca = 0");
    $stmt->execute([$user['id']]);
    $unreadNotifications = $stmt->fetch()['total'] ?? 0;

} catch (PDOException $e) {
    $totalWarga = 0;
    $totalKK = 0;
    $totalPengumuman = 0;
    $totalIuran = 0;
    $recentAnnouncements = [];
    $pendingRequests = 0;
    $pendingLetters = 0;
    $upcomingActivities = [];
    $unreadNotifications = 0;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - VILLA BINTARO REGENCY RT/RW Digital</title>
    <link rel="stylesheet" href="css/style-premium.css">
</head>
<body>
    <div class="dashboard">
        <!-- Sidebar Overlay (Mobile) -->
        <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="sidebar-brand">
                    <div class="sidebar-brand-icon">🏘️</div>
                    <span>VILLA BINTARO<br><span style="font-size: 0.75em; font-weight: 500; opacity: 0.8;">REGENCY</span></span>
                </div>
            </div>
            <nav class="sidebar-menu">
                <a href="dashboard.php" class="sidebar-menu-item active">
                    <span class="sidebar-menu-icon">📊</span>
                    <span>Dashboard</span>
                </a>
                <a href="warga.php" class="sidebar-menu-item">
                    <span class="sidebar-menu-icon">👥</span>
                    <span>Data Warga</span>
                </a>
                <a href="kk.php" class="sidebar-menu-item">
                    <span class="sidebar-menu-icon">📋</span>
                    <span>Kartu Keluarga</span>
                </a>
                <a href="requests.php" class="sidebar-menu-item">
                    <span class="sidebar-menu-icon">⏳</span>
                    <span>Request Anggota</span>
                </a>
                <div style="margin: var(--space-4) var(--space-6); padding-top: var(--space-4); border-top: 1px solid rgba(255,255,255,0.05);">
                    <span style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.1em; color: var(--slate-500); font-weight: 600;">Komunikasi</span>
                </div>
                <a href="pengumuman.php" class="sidebar-menu-item">
                    <span class="sidebar-menu-icon">📢</span>
                    <span>Pengumuman</span>
                </a>
                <a href="notifikasi.php" class="sidebar-menu-item">
                    <span class="sidebar-menu-icon">🔔</span>
                    <span>Notifikasi</span>
                </a>
                <div style="margin: var(--space-4) var(--space-6); padding-top: var(--space-4); border-top: 1px solid rgba(255,255,255,0.05);">
                    <span style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.1em; color: var(--slate-500); font-weight: 600;">Layanan</span>
                </div>
                <a href="layanan.php" class="sidebar-menu-item">
                    <span class="sidebar-menu-icon">📋</span>
                    <span>Layanan Surat</span>
                </a>
                <a href="iuran.php" class="sidebar-menu-item">
                    <span class="sidebar-menu-icon">💰</span>
                    <span>Kas & Iuran</span>
                </a>
                <a href="kegiatan.php" class="sidebar-menu-item">
                    <span class="sidebar-menu-icon">📅</span>
                    <span>Kegiatan</span>
                </a>
                <a href="laporan.php" class="sidebar-menu-item">
                    <span class="sidebar-menu-icon">📈</span>
                    <span>Laporan</span>
                </a>
            </nav>
            <div class="sidebar-user">
                <div class="sidebar-user-avatar" id="userAvatar"><?= getUserAvatar($user['nama']) ?></div>
                <div class="sidebar-user-info">
                    <div class="sidebar-user-name" id="userName"><?= sanitize($user['nama']) ?></div>
                    <div class="sidebar-user-role" id="userRole"><?= getRoleDisplayName($user['role']) ?></div>
                </div>
                <button onclick="logout()" class="btn btn-ghost btn-icon" title="Logout">🚪</button>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Topbar -->
            <header class="topbar">
                <div class="topbar-left">
                    <button class="menu-toggle" id="menuToggle">☰</button>
                    <div>
                        <h1 class="page-title">Dashboard</h1>
                        <p class="card-subtitle">Selamat datang di sistem manajemen RT/RW Digital</p>
                    </div>
                </div>
                <div class="topbar-right">
                    <button class="notification-btn" onclick="window.location.href='notifikasi.php'">
                        🔔
                        <span class="notification-badge"><?= $unreadNotifications > 0 ? min($unreadNotifications, 99) : '' ?></span>
                    </button>
                </div>
            </header>

            <!-- Content -->
            <div class="content">
                <!-- Stats Cards -->
                <div class="dashboard-stats fade-in-up">
                    <div class="stat-card">
                        <div class="stat-card-icon blue">👥</div>
                        <div class="stat-card-content">
                            <h3 id="totalWarga"><?= number_format($totalWarga, 0, ',', '.') ?></h3>
                            <p>Total Warga</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-card-icon green">🏠</div>
                        <div class="stat-card-content">
                            <h3 id="totalKK"><?= number_format($totalKK, 0, ',', '.') ?></h3>
                            <p>Kepala Keluarga</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-card-icon orange">📢</div>
                        <div class="stat-card-content">
                            <h3 id="totalPengumuman"><?= number_format($totalPengumuman, 0, ',', '.') ?></h3>
                            <p>Pengumuman</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-card-icon red">💰</div>
                        <div class="stat-card-content">
                            <h3 id="totalIuran"><?= formatRupiah($totalIuran) ?></h3>
                            <p>Kas Bulan Ini</p>
                        </div>
                    </div>
                </div>

                <!-- Recent Activities & Announcements -->
                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: var(--space-6);" class="fade-in-up">
                    <!-- Recent Announcements -->
                    <div class="card">
                        <div class="card-header">
                            <div>
                                <h3 class="card-title">Pengumuman Terbaru</h3>
                                <p class="card-subtitle">Informasi terkini untuk warga</p>
                            </div>
                            <a href="pengumuman.php" class="btn btn-sm btn-outline">Lihat Semua</a>
                        </div>
                        <div class="card-body">
                            <?php if (empty($recentAnnouncements)): ?>
                                <div style="text-align: center; padding: var(--space-6); color: var(--slate-500);">
                                    <div style="font-size: 3rem; margin-bottom: var(--space-4);">📢</div>
                                    <p>Belum ada pengumuman</p>
                                </div>
                            <?php else: ?>
                                <ul class="activity-list" id="recentAnnouncements">
                                    <?php foreach ($recentAnnouncements as $announcement): ?>
                                        <li class="activity-item">
                                            <div class="activity-icon blue">📢</div>
                                            <div class="activity-content">
                                                <h4><?= sanitize($announcement['judul']) ?></h4>
                                                <p><?= sanitize(substr($announcement['isi'], 0, 100)) ?>...</p>
                                                <span class="activity-time"><?= timeAgo($announcement['created_at']) ?></span>
                                            </div>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="card">
                        <div class="card-header">
                            <div>
                                <h3 class="card-title">Aksi Cepat</h3>
                                <p class="card-subtitle">Akses cepat fitur</p>
                            </div>
                        </div>
                        <div class="card-body">
                            <div style="display: flex; flex-direction: column; gap: var(--space-3);">
                                <a href="warga.php" class="btn btn-primary">➕ Tambah Warga</a>
                                <a href="pengumuman.php" class="btn btn-secondary">📢 Buat Pengumuman</a>
                                <a href="layanan.php" class="btn btn-outline">📋 Ajukan Surat</a>
                                <a href="iuran.php" class="btn btn-outline">💰 Catat Iuran</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="js/auth.js"></script>
    <script src="js/main.js"></script>
    <script>
        // Logout function
        function logout() {
            if (confirm('Apakah Anda yakin ingin logout?')) {
                window.location.href = 'logout.php';
            }
            return false;
        }

        // Toggle sidebar for mobile
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');

            // Prevent body scroll when sidebar is open on mobile
            if (window.innerWidth <= 1024) {
                document.body.style.overflow = sidebar.classList.contains('active') ? 'hidden' : '';
            }
        }

        // Menu toggle click handler
        document.getElementById('menuToggle')?.addEventListener('click', toggleSidebar);

        // Close sidebar when clicking on a menu item (mobile)
        document.querySelectorAll('.sidebar-menu-item').forEach(item => {
            item.addEventListener('click', () => {
                if (window.innerWidth <= 1024) {
                    toggleSidebar();
                }
            });
        });

        // Handle window resize
        let resizeTimer;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(() => {
                if (window.innerWidth > 1024) {
                    document.getElementById('sidebar')?.classList.remove('active');
                    document.getElementById('sidebarOverlay')?.classList.remove('active');
                    document.body.style.overflow = '';
                }
            }, 250);
        });
    </script>
</body>
</html>
