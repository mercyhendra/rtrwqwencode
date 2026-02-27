<?php
/**
 * Kartu Keluarga Page
 * VILLA BINTARO REGENCY RT/RW Digital
 */

require_once __DIR__ . '/api/config.php';
require_once __DIR__ . '/includes/auth.php';
requireLogin();

$user = getCurrentUser();
$isStaff = isStaff();

// Fetch KK data
try {
    $pdo = getDbConnection();
    
    // Get unique KK numbers
    $stmt = $pdo->query("
        SELECT DISTINCT no_kk 
        FROM users 
        WHERE no_kk IS NOT NULL AND role = 'warga'
        ORDER BY no_kk
    ");
    $kkList = $stmt->fetchAll();
    
    // Get selected KK detail
    $selectedKK = $_GET['kk'] ?? '';
    $kkDetail = [];
    $kkMembers = [];
    
    if (!empty($selectedKK)) {
        // Get head of family
        $stmt = $pdo->prepare("SELECT * FROM users WHERE no_kk = ? AND status = 'aktif' LIMIT 1");
        $stmt->execute([$selectedKK]);
        $kkDetail = $stmt->fetch();
        
        // Get family members from anggota_keluarga
        if ($kkDetail) {
            $stmt = $pdo->prepare("SELECT * FROM anggota_keluarga WHERE user_id = ? ORDER BY hubungan DESC");
            $stmt->execute([$kkDetail['id']]);
            $kkMembers = $stmt->fetchAll();
        }
    }
    
    // Count total KK
    $stmt = $pdo->query("SELECT COUNT(DISTINCT no_kk) as total FROM users WHERE no_kk IS NOT NULL AND role = 'warga'");
    $totalKK = $stmt->fetch()['total'] ?? 0;
    
} catch (PDOException $e) {
    $kkList = [];
    $kkDetail = [];
    $kkMembers = [];
    $totalKK = 0;
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
    <title>Kartu Keluarga - VILLA BINTARO REGENCY RT/RW Digital</title>
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
                <a href="kk.php" class="sidebar-menu-item active"><span class="sidebar-menu-icon">📋</span><span>Kartu Keluarga</span></a>
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
                    <h1 class="page-title">Kartu Keluarga</h1>
                </div>
                <div class="topbar-right">
                    <button class="btn btn-sm btn-outline" onclick="window.print()">🖨️ Cetak</button>
                </div>
            </header>

            <div class="content">
                <!-- Stats -->
                <div class="dashboard-stats">
                    <div class="stat-card">
                        <div class="stat-card-icon blue">📋</div>
                        <div class="stat-card-content">
                            <h3><?= $totalKK ?></h3>
                            <p>Total KK</p>
                        </div>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 300px 1fr; gap: 24px;">
                    <!-- KK List -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Daftar KK</h3>
                        </div>
                        <div class="card-body" style="padding: 0;">
                            <?php if (empty($kkList)): ?>
                                <div style="padding: var(--space-6); text-align: center; color: var(--slate-500);">
                                    <p>Belum ada data KK</p>
                                </div>
                            <?php else: ?>
                                <ul class="sidebar-menu" style="padding: var(--space-4);">
                                    <?php foreach ($kkList as $kk): ?>
                                    <li>
                                        <a href="?kk=<?= $kk['no_kk'] ?>" class="sidebar-menu-item <?= $selectedKK === $kk['no_kk'] ? 'active' : '' ?>">
                                            📋 <?= $kk['no_kk'] ?>
                                        </a>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- KK Detail -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Detail Kartu Keluarga</h3>
                        </div>
                        <div class="card-body">
                            <?php if (empty($kkDetail)): ?>
                                <div style="text-align: center; padding: var(--space-6); color: var(--slate-500);">
                                    <div style="font-size: 4rem; margin-bottom: 16px;">📋</div>
                                    <p>Pilih KK untuk melihat detail</p>
                                </div>
                            <?php else: ?>
                                <div id="kkDetailContent">
                                    <!-- Header KK -->
                                    <div style="background: var(--gray-100); padding: var(--space-4); border-radius: var(--radius); margin-bottom: var(--space-4);">
                                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                                            <div>
                                                <div style="font-size: 0.85rem; color: var(--gray-500);">No KK</div>
                                                <div style="font-weight: 600; font-size: 1.1rem;"><?= $kkDetail['no_kk'] ?></div>
                                            </div>
                                            <div>
                                                <div style="font-size: 0.85rem; color: var(--gray-500);">Kepala Keluarga</div>
                                                <div style="font-weight: 600;"><?= sanitize($kkDetail['nama']) ?></div>
                                            </div>
                                            <div>
                                                <div style="font-size: 0.85rem; color: var(--gray-500);">Alamat</div>
                                                <div>RT <?= $kkDetail['rt'] ?? '-' ?> / RW <?= $kkDetail['rw'] ?? '-' ?><?= $kkDetail['no_rumah'] ? ' No. ' . $kkDetail['no_rumah'] : '' ?></div>
                                            </div>
                                            <div>
                                                <div style="font-size: 0.85rem; color: var(--gray-500);">Jumlah Anggota</div>
                                                <div><?= count($kkMembers) + 1 ?> Orang</div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Family Members Table -->
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>Nama</th>
                                                <th>Hubungan</th>
                                                <th>NIK</th>
                                                <th>Jenis Kelamin</th>
                                                <th>Tanggal Lahir</th>
                                                <th>Pekerjaan</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Kepala Keluarga -->
                                            <tr>
                                                <td>1</td>
                                                <td>
                                                    <div style="display: flex; align-items: center; gap: 12px;">
                                                        <div style="width: 35px; height: 35px; background: var(--primary-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold;"><?= strtoupper(substr($kkDetail['nama'], 0, 1)) ?></div>
                                                        <div>
                                                            <div style="font-weight: 500;"><?= sanitize($kkDetail['nama']) ?></div>
                                                            <div style="font-size: 0.85rem; color: var(--gray-500);"><?= sanitize($kkDetail['email']) ?></div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>👨 Kepala Keluarga</td>
                                                <td><?= sanitize($kkDetail['nik']) ?></td>
                                                <td><?= $kkDetail['jenis_kelamin'] ?? '-' ?></td>
                                                <td><?= $kkDetail['tanggal_lahir'] ? formatTanggal($kkDetail['tanggal_lahir'], 'd M Y') : '-' ?></td>
                                                <td><?= $kkDetail['pekerjaan'] ?? '-' ?></td>
                                            </tr>
                                            
                                            <!-- Other Members -->
                                            <?php foreach ($kkMembers as $i => $member): 
                                                $hubungan = $hubunganConfig[$member['hubungan']] ?? ['label' => $member['hubungan'], 'icon' => '👤'];
                                            ?>
                                            <tr>
                                                <td><?= $i + 2 ?></td>
                                                <td>
                                                    <div style="display: flex; align-items: center; gap: 12px;">
                                                        <div style="width: 35px; height: 35px; background: var(--secondary-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold;"><?= strtoupper(substr($member['nama'], 0, 1)) ?></div>
                                                        <div style="font-weight: 500;"><?= sanitize($member['nama']) ?></div>
                                                    </div>
                                                </td>
                                                <td><?= $hubungan['icon'] ?> <?= $hubungan['label'] ?></td>
                                                <td><?= $member['nik'] ?? '-' ?></td>
                                                <td><?= $member['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan' ?></td>
                                                <td><?= $member['tanggal_lahir'] ? formatTanggal($member['tanggal_lahir'], 'd M Y') : '-' ?></td>
                                                <td><?= $member['pekerjaan'] ?? '-' ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
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
