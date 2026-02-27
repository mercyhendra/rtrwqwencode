<?php
/**
 * Laporan Page
 * VILLA BINTARO REGENCY RT/RW Digital
 */

require_once __DIR__ . '/api/config.php';
require_once __DIR__ . '/includes/auth.php';
requireAuth([ROLE_ADMIN, ROLE_RT, ROLE_RW]);

$user = getCurrentUser();
$isStaff = isStaff();

// Fetch report data
try {
    $pdo = getDbConnection();
    
    // Current month
    $currentMonth = date('Y-m');
    
    // Total iuran bulan ini
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(nominal), 0) as total 
        FROM iuran 
        WHERE bulan = ? AND status = 'lunas'
    ");
    $stmt->execute([$currentMonth]);
    $iuranBulanIni = $stmt->fetch()['total'] ?? 0;
    
    // Total iuran tahun ini
    $currentYear = date('Y');
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(nominal), 0) as total 
        FROM iuran 
        WHERE YEAR(bulan) = ? AND status = 'lunas'
    ");
    $stmt->execute([$currentYear]);
    $iuranTahunIni = $stmt->fetch()['total'] ?? 0;
    
    // Iuran per jenis
    $stmt = $pdo->query("
        SELECT jenis_iuran, SUM(nominal) as total, COUNT(*) as count 
        FROM iuran 
        WHERE status = 'lunas' 
        GROUP BY jenis_iuran
    ");
    $iuranPerJenis = $stmt->fetchAll();
    
    // Iuran per bulan (6 bulan terakhir)
    $stmt = $pdo->query("
        SELECT bulan, SUM(nominal) as total 
        FROM iuran 
        WHERE status = 'lunas' 
        GROUP BY bulan 
        ORDER BY bulan DESC 
        LIMIT 6
    ");
    $iuranPerBulan = $stmt->fetchAll();
    
    // Warga stats
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'warga' AND status = 'aktif'");
    $totalWarga = $stmt->fetch()['total'] ?? 0;
    
    $stmt = $pdo->query("SELECT COUNT(DISTINCT no_kk) as total FROM users WHERE role = 'warga' AND no_kk IS NOT NULL");
    $totalKK = $stmt->fetch()['total'] ?? 0;
    
    // Surat stats
    $stmt = $pdo->query("SELECT status, COUNT(*) as count FROM layanan_surat GROUP BY status");
    $suratStats = $stmt->fetchAll();
    
    // Kegiatan stats
    $stmt = $pdo->query("SELECT status, COUNT(*) as count FROM kegiatan GROUP BY status");
    $kegiatanStats = $stmt->fetchAll();
    
    // Recent transactions
    $stmt = $pdo->query("
        SELECT i.*, u.nama as user_name 
        FROM iuran i 
        LEFT JOIN users u ON i.user_id = u.id 
        WHERE i.status = 'lunas'
        ORDER BY i.tanggal_bayar DESC 
        LIMIT 10
    ");
    $recentTransactions = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $iuranBulanIni = 0;
    $iuranTahunIni = 0;
    $iuranPerJenis = [];
    $iuranPerBulan = [];
    $totalWarga = 0;
    $totalKK = 0;
    $suratStats = [];
    $kegiatanStats = [];
    $recentTransactions = [];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan - VILLA BINTARO REGENCY RT/RW Digital</title>
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
                <a href="notifikasi.php" class="sidebar-menu-item"><span class="sidebar-menu-icon">🔔</span><span>Notifikasi</span></a>
                <div style="margin: var(--space-4) var(--space-6); padding-top: var(--space-4); border-top: 1px solid rgba(255,255,255,0.05);">
                    <span style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.1em; color: var(--slate-500); font-weight: 600;">Layanan</span>
                </div>
                <a href="layanan.php" class="sidebar-menu-item"><span class="sidebar-menu-icon">📋</span><span>Layanan Surat</span></a>
                <a href="iuran.php" class="sidebar-menu-item"><span class="sidebar-menu-icon">💰</span><span>Kas & Iuran</span></a>
                <a href="kegiatan.php" class="sidebar-menu-item"><span class="sidebar-menu-icon">📅</span><span>Kegiatan</span></a>
                <a href="laporan.php" class="sidebar-menu-item active"><span class="sidebar-menu-icon">📈</span><span>Laporan</span></a>
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
                    <h1 class="page-title">Laporan & Statistik</h1>
                </div>
                <div class="topbar-right">
                    <button class="btn btn-sm btn-outline" onclick="window.print()">🖨️ Cetak</button>
                </div>
            </header>

            <div class="content">
                <!-- Summary Stats -->
                <div class="dashboard-stats">
                    <div class="stat-card">
                        <div class="stat-card-icon green">💰</div>
                        <div class="stat-card-content">
                            <h3><?= formatRupiah($iuranBulanIni) ?></h3>
                            <p>Kas Bulan Ini</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-card-icon blue">💵</div>
                        <div class="stat-card-content">
                            <h3><?= formatRupiah($iuranTahunIni) ?></h3>
                            <p>Kas Tahun <?= date('Y') ?></p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-card-icon green">👥</div>
                        <div class="stat-card-content">
                            <h3><?= $totalWarga ?></h3>
                            <p>Warga Aktif</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-card-icon blue">🏠</div>
                        <div class="stat-card-content">
                            <h3><?= $totalKK ?></h3>
                            <p>Kepala Keluarga</p>
                        </div>
                    </div>
                </div>

                <!-- Detailed Reports -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 24px;">
                    <!-- Iuran per Jenis -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Iuran per Jenis</h3>
                        </div>
                        <div class="card-body">
                            <?php if (empty($iuranPerJenis)): ?>
                                <p style="color: var(--gray-500); text-align: center;">Belum ada data</p>
                            <?php else: ?>
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Jenis</th>
                                            <th>Jumlah</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($iuranPerJenis as $j): ?>
                                        <tr>
                                            <td><?= ucfirst($j['jenis_iuran']) ?></td>
                                            <td><?= $j['count'] ?> warga</td>
                                            <td><?= formatRupiah($j['total']) ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Iuran per Bulan -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Iuran per Bulan (6 Terakhir)</h3>
                        </div>
                        <div class="card-body">
                            <?php if (empty($iuranPerBulan)): ?>
                                <p style="color: var(--gray-500); text-align: center;">Belum ada data</p>
                            <?php else: ?>
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Bulan</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach (array_reverse($iuranPerBulan) as $b): ?>
                                        <tr>
                                            <td><?= date('F Y', strtotime($b['bulan'] . '-01')) ?></td>
                                            <td><?= formatRupiah($b['total']) ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Surat Stats -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Statistik Layanan Surat</h3>
                        </div>
                        <div class="card-body">
                            <?php if (empty($suratStats)): ?>
                                <p style="color: var(--gray-500); text-align: center;">Belum ada data</p>
                            <?php else: ?>
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Status</th>
                                            <th>Jumlah</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($suratStats as $s): ?>
                                        <tr>
                                            <td>
                                                <?php if ($s['status'] === 'pending'): ?>
                                                    <span class="badge badge-warning">Pending</span>
                                                <?php elseif ($s['status'] === 'proses'): ?>
                                                    <span class="badge badge-info">Diproses</span>
                                                <?php elseif ($s['status'] === 'selesai'): ?>
                                                    <span class="badge badge-success">Selesai</span>
                                                <?php else: ?>
                                                    <span class="badge badge-danger">Ditolak</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= $s['count'] ?> surat</td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Kegiatan Stats -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Statistik Kegiatan</h3>
                        </div>
                        <div class="card-body">
                            <?php if (empty($kegiatanStats)): ?>
                                <p style="color: var(--gray-500); text-align: center;">Belum ada data</p>
                            <?php else: ?>
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Status</th>
                                            <th>Jumlah</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($kegiatanStats as $k): ?>
                                        <tr>
                                            <td>
                                                <?php if ($k['status'] === 'aktif'): ?>
                                                    <span class="badge badge-success">Aktif</span>
                                                <?php elseif ($k['status'] === 'selesai'): ?>
                                                    <span class="badge badge-secondary">Selesai</span>
                                                <?php else: ?>
                                                    <span class="badge badge-danger">Batal</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= $k['count'] ?> kegiatan</td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Recent Transactions -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Transaksi Terakhir</h3>
                    </div>
                    <div class="card-body">
                        <?php if (empty($recentTransactions)): ?>
                            <p style="color: var(--gray-500); text-align: center;">Belum ada transaksi</p>
                        <?php else: ?>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Warga</th>
                                        <th>Jenis</th>
                                        <th>Bulan</th>
                                        <th>Nominal</th>
                                        <th>Tanggal Bayar</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentTransactions as $t): ?>
                                    <tr>
                                        <td><?= sanitize($t['user_name']) ?></td>
                                        <td><?= ucfirst($t['jenis_iuran']) ?></td>
                                        <td><?= date('F Y', strtotime($t['bulan'] . '-01')) ?></td>
                                        <td><?= formatRupiah($t['nominal']) ?></td>
                                        <td><?= formatTanggal($t['tanggal_bayar'], 'd M Y') ?></td>
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
    </script>
</body>
</html>
