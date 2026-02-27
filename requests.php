<?php
/**
 * Request Anggota Keluarga Page
 * VILLA BINTARO REGENCY RT/RW Digital
 */

require_once __DIR__ . '/api/config.php';
require_once __DIR__ . '/includes/auth.php';
requireLogin();

$user = getCurrentUser();
$isStaff = isStaff();

// Fetch requests from database
try {
    $pdo = getDbConnection();
    
    $filter = $_GET['filter'] ?? 'all';
    $whereClause = "1=1";
    $params = [];
    
    if ($filter === 'pending') {
        $whereClause .= " AND status = 'pending'";
    } elseif ($filter === 'approved') {
        $whereClause .= " AND status = 'approved'";
    } elseif ($filter === 'rejected') {
        $whereClause .= " AND status = 'rejected'";
    }
    
    // Get all requests
    $stmt = $pdo->prepare("
        SELECT r.*, u.nama as user_name, u.email as user_email, u.no_kk 
        FROM anggota_keluarga_request r 
        LEFT JOIN users u ON r.user_id = u.id 
        WHERE $whereClause
        ORDER BY r.created_at DESC
    ");
    $stmt->execute($params);
    $requestList = $stmt->fetchAll();
    
    // Count by status
    $stmt = $pdo->prepare("
        SELECT status, COUNT(*) as count 
        FROM anggota_keluarga_request 
        WHERE user_id = ? 
        GROUP BY status
    ");
    $stmt->execute([$user['id']]);
    $statusCounts = $stmt->fetchAll();
    
    // Get pending count for badge
    $pendingCount = 0;
    foreach ($statusCounts as $s) {
        if ($s['status'] === 'pending') $pendingCount = $s['count'];
    }
    
} catch (PDOException $e) {
    $requestList = [];
    $statusCounts = [];
    $pendingCount = 0;
}

$hubunganConfig = [
    'kepala_keluarga' => ['label' => 'Kepala Keluarga', 'icon' => '👨'],
    'istri' => ['label' => 'Istri', 'icon' => '👩'],
    'suami' => ['label' => 'Suami', 'icon' => '👨'],
    'anak' => ['label' => 'Anak', 'icon' => '👶'],
    'lainnya' => ['label' => 'Lainnya', 'icon' => '👤']
];

$statusConfig = [
    'pending' => ['label' => 'Pending', 'color' => 'yellow'],
    'approved' => ['label' => 'Disetujui', 'color' => 'green'],
    'rejected' => ['label' => 'Ditolak', 'color' => 'red']
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Anggota Keluarga - VILLA BINTARO REGENCY RT/RW Digital</title>
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
                <a href="requests.php" class="sidebar-menu-item active"><span class="sidebar-menu-icon">⏳</span><span>Request Anggota</span></a>
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
                    <h1 class="page-title">Request Anggota Keluarga</h1>
                </div>
                <div class="topbar-right">
                    <button class="btn btn-sm btn-primary" onclick="openModal('addRequestModal')">➕ Tambah Request</button>
                </div>
            </header>

            <div class="content">
                <!-- Stats -->
                <div class="dashboard-stats">
                    <?php
                    $pendingCount = 0;
                    $approvedCount = 0;
                    $rejectedCount = 0;
                    foreach ($statusCounts as $s) {
                        if ($s['status'] === 'pending') $pendingCount = $s['count'];
                        if ($s['status'] === 'approved') $approvedCount = $s['count'];
                        if ($s['status'] === 'rejected') $rejectedCount = $s['count'];
                    }
                    ?>
                    <div class="stat-card">
                        <div class="stat-card-icon orange">⏳</div>
                        <div class="stat-card-content">
                            <h3><?= $pendingCount ?></h3>
                            <p>Pending</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-card-icon green">✅</div>
                        <div class="stat-card-content">
                            <h3><?= $approvedCount ?></h3>
                            <p>Disetujui</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-card-icon red">❌</div>
                        <div class="stat-card-content">
                            <h3><?= $rejectedCount ?></h3>
                            <p>Ditolak</p>
                        </div>
                    </div>
                </div>

                <!-- Filter -->
                <div class="card">
                    <div class="card-body">
                        <div style="display: flex; gap: 12px;">
                            <select class="form-control" style="width: 200px;" onchange="window.location.href='?filter=' + this.value">
                                <option value="">Semua</option>
                                <option value="pending" <?= $filter === 'pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="approved" <?= $filter === 'approved' ? 'selected' : '' ?>>Disetujui</option>
                                <option value="rejected" <?= $filter === 'rejected' ? 'selected' : '' ?>>Ditolak</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Table -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Daftar Request</h3>
                    </div>
                    <div class="card-body">
                        <?php if (empty($requestList)): ?>
                            <div style="text-align: center; padding: var(--space-6); color: var(--slate-500);">
                                <div style="font-size: 4rem; margin-bottom: 16px;">⏳</div>
                                <p>Belum ada request</p>
                            </div>
                        <?php else: ?>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Nama</th>
                                        <th>Hubungan</th>
                                        <th>NIK</th>
                                        <th>Jenis Kelamin</th>
                                        <th>Tanggal Lahir</th>
                                        <th>Pemohon</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($requestList as $r): 
                                        $hubungan = $hubunganConfig[$r['hubungan']] ?? ['label' => $r['hubungan'], 'icon' => '👤'];
                                        $status = $statusConfig[$r['status']] ?? $statusConfig['pending'];
                                    ?>
                                    <tr>
                                        <td><?= sanitize($r['nama']) ?></td>
                                        <td><?= $hubungan['icon'] ?> <?= $hubungan['label'] ?></td>
                                        <td><?= $r['nik'] ?? '-' ?></td>
                                        <td><?= $r['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan' ?></td>
                                        <td><?= $r['tanggal_lahir'] ? formatTanggal($r['tanggal_lahir'], 'd M Y') : '-' ?></td>
                                        <td>
                                            <div><?= sanitize($r['user_name']) ?></div>
                                            <div style="font-size: 0.85rem; color: var(--gray-500);"><?= sanitize($r['user_email']) ?></div>
                                        </td>
                                        <td><span class="badge badge-<?= $status['color'] ?>"><?= $status['label'] ?></span></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline" onclick="viewRequest(<?= $r['id'] ?>)">👁️</button>
                                            <?php if ($isStaff && $r['status'] === 'pending'): ?>
                                            <button class="btn btn-sm btn-success" onclick="approveRequest(<?= $r['id'] ?>)">✓</button>
                                            <button class="btn btn-sm btn-danger" onclick="rejectRequest(<?= $r['id'] ?>)">✗</button>
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

    <!-- Add Request Modal -->
    <div class="modal-overlay" id="addRequestModal">
        <div class="modal modal-lg">
            <div class="modal-header">
                <h3 class="modal-title">Tambah Anggota Keluarga</h3>
                <button class="modal-close" onclick="closeModal('addRequestModal')">&times;</button>
            </div>
            <form id="addRequestForm" onsubmit="handleAdd(event)">
                <div class="modal-body">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div class="form-group">
                            <label class="form-label">Nama Lengkap *</label>
                            <input type="text" class="form-control" name="nama" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Hubungan *</label>
                            <select class="form-control" name="hubungan" required>
                                <option value="">Pilih Hubungan</option>
                                <option value="kepala_keluarga">Kepala Keluarga</option>
                                <option value="istri">Istri</option>
                                <option value="suami">Suami</option>
                                <option value="anak">Anak</option>
                                <option value="lainnya">Lainnya</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">NIK</label>
                            <input type="text" class="form-control" name="nik" maxlength="16">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Jenis Kelamin *</label>
                            <select class="form-control" name="jenis_kelamin" required>
                                <option value="">Pilih</option>
                                <option value="L">Laki-laki</option>
                                <option value="P">Perempuan</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Tempat Lahir</label>
                            <input type="text" class="form-control" name="tempat_lahir">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Tanggal Lahir</label>
                            <input type="date" class="form-control" name="tanggal_lahir">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Pekerjaan</label>
                            <input type="text" class="form-control" name="pekerjaan">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Status Perkawinan</label>
                            <select class="form-control" name="status_perkawinan">
                                <option value="">Pilih</option>
                                <option value="belum_kawin">Belum Kawin</option>
                                <option value="kawin">Kawin</option>
                                <option value="cerai_hidup">Cerai Hidup</option>
                                <option value="cerai_mati">Cerai Mati</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('addRequestModal')">Batal</button>
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
                const response = await fetch('api/requests.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                const result = await response.json();

                if (result.success) {
                    alert('Request berhasil ditambahkan');
                    closeModal('addRequestModal');
                    window.location.reload();
                } else {
                    alert('Gagal menambahkan request: ' + result.message);
                }
            } catch (error) {
                alert('Terjadi kesalahan');
                console.error(error);
            }
        }

        async function viewRequest(id) {
            try {
                const response = await fetch(`api/requests.php?id=${id}`);
                const result = await response.json();
                
                if (result.success) {
                    const r = result.data;
                    alert(`Detail Request:\n\nNama: ${r.nama}\nHubungan: ${r.hubungan}\nNIK: ${r.nik || '-'}\nJenis Kelamin: ${r.jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan'}\nTempat Lahir: ${r.tempat_lahir || '-'}\nTanggal Lahir: ${r.tanggal_lahir || '-'}\nPekerjaan: ${r.pekerjaan || '-'}\nStatus: ${r.status}\nCatatan: ${r.catatan_admin || '-'}`);
                }
            } catch (error) {
                alert('Gagal memuat detail');
            }
        }

        async function approveRequest(id) {
            if (confirm('Setujui request ini?')) {
                try {
                    const response = await fetch('api/requests.php', {
                        method: 'PUT',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id, action: 'approve' })
                    });
                    const result = await response.json();
                    
                    if (result.success) {
                        alert('Request disetujui');
                        window.location.reload();
                    } else {
                        alert('Gagal menyetujui request: ' + result.message);
                    }
                } catch (error) {
                    alert('Terjadi kesalahan');
                }
            }
        }

        async function rejectRequest(id) {
            const catatan = prompt('Alasan penolakan (opsional):');
            try {
                const response = await fetch('api/requests.php', {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id, action: 'reject', catatan_admin: catatan || 'Ditolak' })
                });
                const result = await response.json();
                
                if (result.success) {
                    alert('Request ditolak');
                    window.location.reload();
                } else {
                    alert('Gagal menolak request: ' + result.message);
                }
            } catch (error) {
                alert('Terjadi kesalahan');
            }
        }
    </script>
</body>
</html>
