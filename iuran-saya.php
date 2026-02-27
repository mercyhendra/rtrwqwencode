<?php
/**
 * Iuran Saya Page
 * VILLA BINTARO REGENCY RT/RW Digital
 */

require_once __DIR__ . '/api/config.php';
require_once __DIR__ . '/includes/auth.php';
requireLogin();

$user = getCurrentUser();

// Fetch user's iuran
try {
    $pdo = getDbConnection();
    
    $filter = $_GET['filter'] ?? 'all';
    $whereClause = "user_id = ?";
    $params = [$user['id']];
    
    if ($filter === 'lunas') {
        $whereClause .= " AND status = 'lunas'";
    } elseif ($filter === 'belum_bayar') {
        $whereClause .= " AND status = 'belum_bayar'";
    }
    
    $stmt = $pdo->prepare("
        SELECT * FROM iuran 
        WHERE $whereClause 
        ORDER BY created_at DESC
    ");
    $stmt->execute($params);
    $iuranList = $stmt->fetchAll();
    
    // Total paid
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(nominal), 0) as total 
        FROM iuran 
        WHERE user_id = ? AND status = 'lunas'
    ");
    $stmt->execute([$user['id']]);
    $totalPaid = $stmt->fetch()['total'] ?? 0;
    
    // Total unpaid
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(nominal), 0) as total 
        FROM iuran 
        WHERE user_id = ? AND status = 'belum_bayar'
    ");
    $stmt->execute([$user['id']]);
    $totalUnpaid = $stmt->fetch()['total'] ?? 0;
    
    // Count by status
    $stmt = $pdo->prepare("SELECT status, COUNT(*) as count FROM iuran WHERE user_id = ? GROUP BY status");
    $stmt->execute([$user['id']]);
    $statusCounts = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $iuranList = [];
    $totalPaid = 0;
    $totalUnpaid = 0;
    $statusCounts = [];
}

$jenisIuranConfig = [
    'kebersihan' => ['label' => 'Kebersihan', 'icon' => '🧹'],
    'keamanan' => ['label' => 'Keamanan', 'icon' => '🛡️'],
    'sampah' => ['label' => 'Sampah', 'icon' => '🗑️'],
    'lainnya' => ['label' => 'Lainnya', 'icon' => '📌']
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iuran Saya - VILLA BINTARO REGENCY RT/RW Digital</title>
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
                <a href="warga-saya.php" class="sidebar-menu-item"><span class="sidebar-menu-icon">👤</span><span>Warga Saya</span></a>
                <a href="keluarga-saya.php" class="sidebar-menu-item"><span class="sidebar-menu-icon">👨‍👩‍👧‍👦</span><span>Keluarga Saya</span></a>
                <a href="iuran-saya.php" class="sidebar-menu-item active"><span class="sidebar-menu-icon">💰</span><span>Iuran Saya</span></a>
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
                    <h1 class="page-title">Iuran Saya</h1>
                </div>
            </header>

            <div class="content">
                <!-- Stats -->
                <div class="dashboard-stats">
                    <div class="stat-card">
                        <div class="stat-card-icon green">✅</div>
                        <div class="stat-card-content">
                            <h3><?= formatRupiah($totalPaid) ?></h3>
                            <p>Total Lunas</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-card-icon red">⏳</div>
                        <div class="stat-card-content">
                            <h3><?= formatRupiah($totalUnpaid) ?></h3>
                            <p>Belum Dibayar</p>
                        </div>
                    </div>
                    <?php foreach ($statusCounts as $s): ?>
                    <div class="stat-card">
                        <div class="stat-card-icon blue">📋</div>
                        <div class="stat-card-content">
                            <h3><?= $s['count'] ?></h3>
                            <p><?= ucfirst($s['status']) ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Filter -->
                <div class="card">
                    <div class="card-body">
                        <div style="display: flex; gap: 12px;">
                            <select class="form-control" style="width: 200px;" onchange="window.location.href='?filter=' + this.value">
                                <option value="">Semua</option>
                                <option value="lunas" <?= $filter === 'lunas' ? 'selected' : '' ?>>Lunas</option>
                                <option value="belum_bayar" <?= $filter === 'belum_bayar' ? 'selected' : '' ?>>Belum Dibayar</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Iuran List -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Riwayat Iuran</h3>
                    </div>
                    <div class="card-body">
                        <?php if (empty($iuranList)): ?>
                            <div style="text-align: center; padding: var(--space-6); color: var(--slate-500);">
                                <div style="font-size: 4rem; margin-bottom: 16px;">💰</div>
                                <p>Belum ada data iuran</p>
                            </div>
                        <?php else: ?>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Jenis Iuran</th>
                                        <th>Bulan</th>
                                        <th>Nominal</th>
                                        <th>Tanggal Bayar</th>
                                        <th>Keterangan</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($iuranList as $i): 
                                        $jenis = $jenisIuranConfig[$i['jenis_iuran']] ?? ['label' => $i['jenis_iuran'], 'icon' => '📌'];
                                    ?>
                                    <tr>
                                        <td><?= $jenis['icon'] ?> <?= $jenis['label'] ?></td>
                                        <td><?= date('F Y', strtotime($i['bulan'] . '-01')) ?></td>
                                        <td>Rp <?= number_format($i['nominal'], 0, ',', '.') ?></td>
                                        <td><?= $i['tanggal_bayar'] ? formatTanggal($i['tanggal_bayar'], 'd M Y') : '-' ?></td>
                                        <td><?= $i['keterangan'] ?? '-' ?></td>
                                        <td>
                                            <?php if ($i['status'] === 'lunas'): ?>
                                                <span class="badge badge-success">Lunas</span>
                                            <?php else: ?>
                                                <span class="badge badge-danger">Belum Dibayar</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($i['status'] === 'belum_bayar'): ?>
                                            <button class="btn btn-sm btn-primary" onclick="bayarIuran(<?= $i['id'] ?>)">Bayar</button>
                                            <?php endif; ?>
                                            <button class="btn btn-sm btn-outline" onclick="detailIuran(<?= $i['id'] ?>)">👁️</button>
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

        async function bayarIuran(id) {
            if (confirm('Konfirmasi pembayaran iuran ini?')) {
                try {
                    const response = await fetch('api/iuran.php', {
                        method: 'PUT',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id, action: 'bayar' })
                    });
                    const result = await response.json();

                    if (result.success) {
                        alert('Pembayaran berhasil dikonfirmasi');
                        window.location.reload();
                    } else {
                        alert('Gagal konfirmasi pembayaran: ' + result.message);
                    }
                } catch (error) {
                    alert('Terjadi kesalahan');
                }
            }
        }

        async function detailIuran(id) {
            try {
                const response = await fetch(`api/iuran.php?id=${id}`);
                const result = await response.json();
                
                if (result.success) {
                    const i = result.data;
                    alert(`Detail Iuran:\n\nJenis: ${i.jenis_iuran}\nBulan: ${i.bulan}\nNominal: Rp ${i.nominal.toLocaleString()}\nStatus: ${i.status}\nKeterangan: ${i.keterangan || '-'}`);
                }
            } catch (error) {
                alert('Gagal memuat detail');
            }
        }
    </script>
</body>
</html>
