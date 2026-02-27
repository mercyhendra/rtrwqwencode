<?php
/**
 * Data Warga Page
 * VILLA BINTARO REGENCY RT/RW Digital
 */

require_once __DIR__ . '/api/config.php';
require_once __DIR__ . '/includes/auth.php';
requireAuth([ROLE_ADMIN, ROLE_RT, ROLE_RW]);

$user = getCurrentUser();
$isStaff = isStaff();

// Fetch warga from database
try {
    $pdo = getDbConnection();
    
    $search = $_GET['search'] ?? '';
    $filterRT = $_GET['rt'] ?? '';
    $filterStatus = $_GET['status'] ?? 'aktif';
    
    $whereClause = "1=1";
    $params = [];
    
    if (!empty($search)) {
        $whereClause .= " AND (nama LIKE ? OR nik LIKE ? OR email LIKE ?)";
        $searchTerm = "%$search%";
        $params = [$searchTerm, $searchTerm, $searchTerm];
    }
    
    if (!empty($filterRT)) {
        $whereClause .= " AND rt = ?";
        $params[] = $filterRT;
    }
    
    if ($filterStatus) {
        $whereClause .= " AND status = ?";
        $params[] = $filterStatus;
    }
    
    $stmt = $pdo->prepare("
        SELECT * FROM users 
        WHERE role = 'warga' AND $whereClause
        ORDER BY nama ASC
    ");
    $stmt->execute($params);
    $wargaList = $stmt->fetchAll();
    
    // Count by RT
    $stmt = $pdo->query("
        SELECT rt, COUNT(*) as count 
        FROM users 
        WHERE role = 'warga' AND status = 'aktif'
        GROUP BY rt 
        ORDER BY rt
    ");
    $rtCounts = $stmt->fetchAll();
    
    // Total counts
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'warga' AND status = 'aktif'");
    $totalWarga = $stmt->fetch()['total'] ?? 0;
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'warga' AND status = 'pindah'");
    $totalPindah = $stmt->fetch()['total'] ?? 0;
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'warga' AND status = 'meninggal'");
    $totalMeninggal = $stmt->fetch()['total'] ?? 0;
    
} catch (PDOException $e) {
    $wargaList = [];
    $rtCounts = [];
    $totalWarga = 0;
    $totalPindah = 0;
    $totalMeninggal = 0;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Warga - VILLA BINTARO REGENCY RT/RW Digital</title>
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
                <a href="warga.php" class="sidebar-menu-item active"><span class="sidebar-menu-icon">👥</span><span>Data Warga</span></a>
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
                    <h1 class="page-title">Data Warga</h1>
                </div>
                <div class="topbar-right">
                    <button class="btn btn-sm btn-primary" onclick="openModal('addWargaModal')">➕ Tambah Warga</button>
                </div>
            </header>

            <div class="content">
                <!-- Stats -->
                <div class="dashboard-stats">
                    <div class="stat-card">
                        <div class="stat-card-icon green">👥</div>
                        <div class="stat-card-content">
                            <h3><?= $totalWarga ?></h3>
                            <p>Warga Aktif</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-card-icon orange">🏠</div>
                        <div class="stat-card-content">
                            <h3><?= $totalPindah ?></h3>
                            <p>Pindah</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-card-icon red">🕊️</div>
                        <div class="stat-card-content">
                            <h3><?= $totalMeninggal ?></h3>
                            <p>Meninggal</p>
                        </div>
                    </div>
                </div>

                <!-- Filter & Search -->
                <div class="card">
                    <div class="card-body">
                        <form method="GET" class="filter-form" style="display: flex; gap: 12px; flex-wrap: wrap;">
                            <input type="text" name="search" class="form-control" placeholder="🔍 Cari nama, NIK, atau email..." value="<?= sanitize($search) ?>" style="flex: 1; min-width: 250px;">
                            <select name="rt" class="form-control" style="width: 150px;">
                                <option value="">Semua RT</option>
                                <?php foreach ($rtCounts as $rt): ?>
                                <option value="<?= $rt['rt'] ?>" <?= $filterRT === $rt['rt'] ? 'selected' : '' ?>>RT <?= $rt['rt'] ?> (<?= $rt['count'] ?>)</option>
                                <?php endforeach; ?>
                            </select>
                            <select name="status" class="form-control" style="width: 150px;">
                                <option value="aktif" <?= $filterStatus === 'aktif' ? 'selected' : '' ?>>Aktif</option>
                                <option value="pindah" <?= $filterStatus === 'pindah' ? 'selected' : '' ?>>Pindah</option>
                                <option value="meninggal" <?= $filterStatus === 'meninggal' ? 'selected' : '' ?>>Meninggal</option>
                                <option value="" <?= $filterStatus === '' ? 'selected' : '' ?>>Semua</option>
                            </select>
                            <button type="submit" class="btn btn-primary">Filter</button>
                            <a href="warga.php" class="btn btn-outline">Reset</a>
                        </form>
                    </div>
                </div>

                <!-- Table -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Daftar Warga</h3>
                    </div>
                    <div class="card-body">
                        <?php if (empty($wargaList)): ?>
                            <div style="text-align: center; padding: var(--space-6); color: var(--slate-500);">
                                <div style="font-size: 4rem; margin-bottom: 16px;">👥</div>
                                <p>Belum ada data warga</p>
                            </div>
                        <?php else: ?>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Nama</th>
                                        <th>NIK</th>
                                        <th>No KK</th>
                                        <th>RT/RW</th>
                                        <th>Rumah</th>
                                        <th>Kontak</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($wargaList as $w): ?>
                                    <tr>
                                        <td>
                                            <div style="display: flex; align-items: center; gap: 12px;">
                                                <div style="width: 40px; height: 40px; background: var(--primary-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold;"><?= strtoupper(substr($w['nama'], 0, 1)) ?></div>
                                                <div>
                                                    <div style="font-weight: 500;"><?= sanitize($w['nama']) ?></div>
                                                    <div style="font-size: 0.85rem; color: var(--gray-500);"><?= sanitize($w['email']) ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?= sanitize($w['nik']) ?></td>
                                        <td><?= sanitize($w['no_kk']) ?></td>
                                        <td>RT <?= $w['rt'] ?> / RW <?= $w['rw'] ?></td>
                                        <td><?= $w['no_rumah'] ?? '-' ?></td>
                                        <td>
                                            <div><?= $w['whatsapp'] ?? '-' ?></div>
                                            <div style="font-size: 0.85rem; color: var(--gray-500);"><?= $w['pekerjaan'] ?? '-' ?></div>
                                        </td>
                                        <td>
                                            <?php if ($w['status'] === 'aktif'): ?>
                                                <span class="badge badge-success">Aktif</span>
                                            <?php elseif ($w['status'] === 'pindah'): ?>
                                                <span class="badge badge-warning">Pindah</span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary">Meninggal</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline" onclick="viewWarga(<?= $w['id'] ?>)">👁️</button>
                                            <button class="btn btn-sm btn-primary" onclick="editWarga(<?= $w['id'] ?>)">✏️</button>
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

    <!-- Add Warga Modal -->
    <div class="modal-overlay" id="addWargaModal">
        <div class="modal modal-lg">
            <div class="modal-header">
                <h3 class="modal-title">Tambah Warga</h3>
                <button class="modal-close" onclick="closeModal('addWargaModal')">&times;</button>
            </div>
            <form id="addWargaForm" onsubmit="handleAdd(event)">
                <div class="modal-body">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div class="form-group">
                            <label class="form-label">Nama Lengkap *</label>
                            <input type="text" class="form-control" name="nama" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Email *</label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">NIK *</label>
                            <input type="text" class="form-control" name="nik" maxlength="16" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">No KK *</label>
                            <input type="text" class="form-control" name="no_kk" maxlength="16" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">RT *</label>
                            <input type="text" class="form-control" name="rt" maxlength="3" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">RW *</label>
                            <input type="text" class="form-control" name="rw" maxlength="3" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">No Rumah</label>
                            <input type="text" class="form-control" name="no_rumah">
                        </div>
                        <div class="form-group">
                            <label class="form-label">WhatsApp</label>
                            <input type="text" class="form-control" name="whatsapp">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Pekerjaan</label>
                            <input type="text" class="form-control" name="pekerjaan">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Password *</label>
                            <input type="password" class="form-control" name="password" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('addWargaModal')">Batal</button>
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

        async function handleAdd(e) {
            e.preventDefault();
            const form = e.target;
            const formData = new FormData(form);
            const data = Object.fromEntries(formData);

            try {
                const response = await fetch('api/warga.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                const result = await response.json();

                if (result.success) {
                    alert('Warga berhasil ditambahkan');
                    closeModal('addWargaModal');
                    window.location.reload();
                } else {
                    alert('Gagal menambahkan warga: ' + result.message);
                }
            } catch (error) {
                alert('Terjadi kesalahan');
                console.error(error);
            }
        }

        async function viewWarga(id) {
            try {
                const response = await fetch(`api/warga.php?id=${id}`);
                const result = await response.json();
                
                if (result.success) {
                    const w = result.data;
                    alert(`Detail Warga:\n\nNama: ${w.nama}\nNIK: ${w.nik}\nNo KK: ${w.no_kk}\nRT/RW: ${w.rt}/${w.rw}\nRumah: ${w.no_rumah}\nEmail: ${w.email}\nWhatsApp: ${w.whatsapp}\nPekerjaan: ${w.pekerjaan}\nStatus: ${w.status}`);
                }
            } catch (error) {
                alert('Gagal memuat detail');
            }
        }

        function editWarga(id) {
            alert('Fitur edit akan segera hadir');
        }
    </script>
</body>
</html>
