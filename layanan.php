<?php
/**
 * Layanan Surat Page
 * VILLA BINTARO REGENCY RT/RW Digital
 */

require_once __DIR__ . '/api/config.php';
require_once __DIR__ . '/includes/auth.php';
requireLogin();

$user = getCurrentUser();
$isStaff = isStaff();

// Fetch layanan surat from database
try {
    $pdo = getDbConnection();
    
    $filter = $_GET['filter'] ?? 'all';
    $whereClause = "1=1";
    $params = [];
    
    if (!$isStaff) {
        $whereClause .= " AND ls.user_id = ?";
        $params[] = $user['id'];
    }
    
    if ($filter === 'pending') {
        $whereClause .= " AND ls.status = 'pending'";
    } elseif ($filter === 'proses') {
        $whereClause .= " AND ls.status = 'proses'";
    } elseif ($filter === 'selesai') {
        $whereClause .= " AND ls.status = 'selesai'";
    }
    
    $stmt = $pdo->prepare("
        SELECT ls.*, u.nama as user_name 
        FROM layanan_surat ls 
        LEFT JOIN users u ON ls.user_id = u.id 
        WHERE $whereClause
        ORDER BY ls.created_at DESC
    ");
    $stmt->execute($params);
    $layananList = $stmt->fetchAll();
    
    // Count by status
    $stmt = $pdo->prepare("
        SELECT status, COUNT(*) as count 
        FROM layanan_surat 
        WHERE user_id = ? 
        GROUP BY status
    ");
    $stmt->execute([$user['id']]);
    $statusCounts = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $layananList = [];
    $statusCounts = [];
}

$jenisSuratConfig = [
    'domisili' => 'Surat Domisili',
    'ktp' => 'KTP',
    'kelahiran' => 'Surat Kelahiran',
    'kematian' => 'Surat Kematian',
    'nikah' => 'Surat Nikah',
    'usaha' => 'Surat Usaha'
];

$statusConfig = [
    'pending' => ['label' => 'Pending', 'color' => 'yellow'],
    'proses' => ['label' => 'Diproses', 'color' => 'blue'],
    'selesai' => ['label' => 'Selesai', 'color' => 'green'],
    'ditolak' => ['label' => 'Ditolak', 'color' => 'red']
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Layanan Surat - VILLA BINTARO REGENCY RT/RW Digital</title>
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
                <a href="layanan.php" class="sidebar-menu-item active"><span class="sidebar-menu-icon">📋</span><span>Layanan Surat</span></a>
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
                    <h1 class="page-title">Layanan Surat</h1>
                </div>
                <div class="topbar-right">
                    <?php if ($isStaff): ?>
                    <button class="btn btn-sm btn-primary" onclick="openModal('addLayananModal')">➕ Buat Surat</button>
                    <?php else: ?>
                    <button class="btn btn-sm btn-primary" onclick="openModal('addLayananModal')">➕ Ajukan Surat</button>
                    <?php endif; ?>
                </div>
            </header>

            <div class="content">
                <!-- Stats -->
                <div class="dashboard-stats">
                    <?php 
                    $pendingCount = 0;
                    $prosesCount = 0;
                    $selesaiCount = 0;
                    foreach ($statusCounts as $s) {
                        if ($s['status'] === 'pending') $pendingCount = $s['count'];
                        if ($s['status'] === 'proses') $prosesCount = $s['count'];
                        if ($s['status'] === 'selesai') $selesaiCount = $s['count'];
                    }
                    ?>
                    <div class="stat-card">
                        <div class="stat-card-icon yellow">⏳</div>
                        <div class="stat-card-content">
                            <h3><?= $pendingCount ?></h3>
                            <p>Pending</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-card-icon blue">🔄</div>
                        <div class="stat-card-content">
                            <h3><?= $prosesCount ?></h3>
                            <p>Diproses</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-card-icon green">✅</div>
                        <div class="stat-card-content">
                            <h3><?= $selesaiCount ?></h3>
                            <p>Selesai</p>
                        </div>
                    </div>
                </div>

                <!-- Table -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Daftar Pengajuan Surat</h3>
                        <div style="display: flex; gap: 12px;">
                            <select class="form-control" style="width: 150px;" onchange="window.location.href='?filter=' + this.value">
                                <option value="">Semua</option>
                                <option value="pending" <?= $filter === 'pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="proses" <?= $filter === 'proses' ? 'selected' : '' ?>>Diproses</option>
                                <option value="selesai" <?= $filter === 'selesai' ? 'selected' : '' ?>>Selesai</option>
                            </select>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if (empty($layananList)): ?>
                            <div style="text-align: center; padding: var(--space-6); color: var(--slate-500);">
                                <div style="font-size: 4rem; margin-bottom: 16px;">📋</div>
                                <p>Belum ada pengajuan surat</p>
                            </div>
                        <?php else: ?>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>No Surat</th>
                                        <th>Jenis Surat</th>
                                        <th>Pemohon</th>
                                        <th>Keperluan</th>
                                        <th>Tanggal</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($layananList as $l): 
                                        $status = $statusConfig[$l['status']] ?? $statusConfig['pending'];
                                    ?>
                                    <tr>
                                        <td><?= $l['no_surat'] ?? '-' ?></td>
                                        <td><?= $jenisSuratConfig[$l['jenis_surat']] ?? $l['jenis_surat'] ?></td>
                                        <td><?= sanitize($l['user_name']) ?></td>
                                        <td><?= sanitize($l['keperluan']) ?></td>
                                        <td><?= formatTanggal($l['created_at'], 'd M Y') ?></td>
                                        <td><span class="badge badge-<?= $status['color'] ?>"><?= $status['label'] ?></span></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline" onclick="viewLayanan(<?= $l['id'] ?>)">👁️</button>
                                            <?php if ($isStaff && $l['status'] === 'pending'): ?>
                                            <button class="btn btn-sm btn-primary" onclick="prosesLayanan(<?= $l['id'] ?>)">Proses</button>
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

    <!-- Add Layanan Modal -->
    <div class="modal-overlay" id="addLayananModal">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title">Ajukan Surat</h3>
                <button class="modal-close" onclick="closeModal('addLayananModal')">&times;</button>
            </div>
            <form id="addLayananForm" onsubmit="handleAdd(event)">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Jenis Surat</label>
                        <select class="form-control" name="jenis_surat" required>
                            <option value="">Pilih Jenis Surat</option>
                            <option value="domisili">Surat Domisili</option>
                            <option value="ktp">KTP</option>
                            <option value="kelahiran">Surat Kelahiran</option>
                            <option value="kematian">Surat Kematian</option>
                            <option value="nikah">Surat Nikah</option>
                            <option value="usaha">Surat Usaha</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Keperluan</label>
                        <textarea class="form-control" name="keperluan" rows="4" placeholder="Jelaskan keperluan surat..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('addLayananModal')">Batal</button>
                    <button type="submit" class="btn btn-primary">Ajukan</button>
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

        async function handleAdd(e) {
            e.preventDefault();
            const form = e.target;
            const formData = new FormData(form);
            const data = Object.fromEntries(formData);

            try {
                const response = await fetch('api/layanan.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                const result = await response.json();

                if (result.success) {
                    alert('Pengajuan surat berhasil ditambahkan');
                    closeModal('addLayananModal');
                    window.location.reload();
                } else {
                    alert('Gagal menambahkan pengajuan: ' + result.message);
                }
            } catch (error) {
                alert('Terjadi kesalahan');
                console.error(error);
            }
        }

        async function viewLayanan(id) {
            try {
                const response = await fetch(`api/layanan.php?id=${id}`);
                const result = await response.json();
                
                if (result.success) {
                    alert('Detail: ' + result.data.jenis_surat + '\nKeperluan: ' + result.data.keperluan + '\nStatus: ' + result.data.status);
                }
            } catch (error) {
                alert('Gagal memuat detail');
            }
        }

        async function prosesLayanan(id) {
            if (confirm('Proses pengajuan surat ini?')) {
                try {
                    const response = await fetch('api/layanan.php', {
                        method: 'PUT',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id, status: 'proses' })
                    });
                    const result = await response.json();
                    
                    if (result.success) {
                        alert('Pengajuan sedang diproses');
                        window.location.reload();
                    }
                } catch (error) {
                    alert('Gagal memproses');
                }
            }
        }
    </script>
</body>
</html>
