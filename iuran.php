<?php
/**
 * Iuran Page
 * VILLA BINTARO REGENCY RT/RW Digital
 */

require_once __DIR__ . '/api/config.php';
require_once __DIR__ . '/includes/auth.php';
requireLogin();

$user = getCurrentUser();
$isStaff = isStaff();

// Fetch iuran from database
try {
    $pdo = getDbConnection();
    
    $filter = $_GET['filter'] ?? 'all';
    $whereClause = "1=1";
    $params = [];
    
    if ($filter === 'lunas') {
        $whereClause .= " AND status = 'lunas'";
    } elseif ($filter === 'belum_bayar') {
        $whereClause .= " AND status = 'belum_bayar'";
    }
    
    // Get all iuran with user info
    $stmt = $pdo->prepare("
        SELECT i.*, u.nama as user_name 
        FROM iuran i 
        LEFT JOIN users u ON i.user_id = u.id 
        WHERE $whereClause
        ORDER BY i.created_at DESC
    ");
    $stmt->execute($params);
    $iuranList = $stmt->fetchAll();
    
    // Total per bulan
    $stmt = $pdo->query("
        SELECT bulan, SUM(nominal) as total 
        FROM iuran 
        WHERE status = 'lunas' 
        GROUP BY bulan 
        ORDER BY bulan DESC 
        LIMIT 6
    ");
    $totalPerBulan = $stmt->fetchAll();
    
    // Total by jenis
    $stmt = $pdo->query("
        SELECT jenis_iuran, SUM(nominal) as total 
        FROM iuran 
        WHERE status = 'lunas' 
        GROUP BY jenis_iuran
    ");
    $totalPerJenis = $stmt->fetchAll();
    
    // Current month total
    $currentMonth = date('Y-m');
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(nominal), 0) as total 
        FROM iuran 
        WHERE bulan = ? AND status = 'lunas'
    ");
    $stmt->execute([$currentMonth]);
    $currentMonthTotal = $stmt->fetch()['total'] ?? 0;
    
} catch (PDOException $e) {
    $iuranList = [];
    $totalPerBulan = [];
    $totalPerJenis = [];
    $currentMonthTotal = 0;
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
    <title>Kas & Iuran - VILLA BINTARO REGENCY RT/RW Digital</title>
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
                <a href="iuran.php" class="sidebar-menu-item active"><span class="sidebar-menu-icon">💰</span><span>Kas & Iuran</span></a>
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
                    <h1 class="page-title">Kas & Iuran</h1>
                </div>
                <div class="topbar-right">
                    <?php if ($isStaff): ?>
                    <button class="btn btn-sm btn-primary" onclick="openModal('addIuranModal')">➕ Catat Iuran</button>
                    <?php else: ?>
                    <button class="btn btn-sm btn-primary" onclick="openModal('bayarIuranModal')">💰 Bayar Iuran</button>
                    <?php endif; ?>
                </div>
            </header>

            <div class="content">
                <!-- Stats -->
                <div class="dashboard-stats">
                    <div class="stat-card">
                        <div class="stat-card-icon green">💰</div>
                        <div class="stat-card-content">
                            <h3><?= formatRupiah($currentMonthTotal) ?></h3>
                            <p>Kas Bulan Ini (<?= date('F Y') ?>)</p>
                        </div>
                    </div>
                    <?php foreach ($totalPerJenis as $j): 
                        $jenis = $jenisIuranConfig[$j['jenis_iuran']] ?? ['label' => $j['jenis_iuran'], 'icon' => '📌'];
                    ?>
                    <div class="stat-card">
                        <div class="stat-card-icon blue"><?= $jenis['icon'] ?></div>
                        <div class="stat-card-content">
                            <h3><?= formatRupiah($j['total']) ?></h3>
                            <p>Total <?= $jenis['label'] ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Table -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Riwayat Iuran</h3>
                        <div style="display: flex; gap: 12px;">
                            <select class="form-control" style="width: 150px;" onchange="window.location.href='?filter=' + this.value">
                                <option value="">Semua</option>
                                <option value="lunas" <?= $filter === 'lunas' ? 'selected' : '' ?>>Lunas</option>
                                <option value="belum_bayar" <?= $filter === 'belum_bayar' ? 'selected' : '' ?>>Belum Dibayar</option>
                            </select>
                        </div>
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
                                        <th>Warga</th>
                                        <th>Jenis Iuran</th>
                                        <th>Bulan</th>
                                        <th>Nominal</th>
                                        <th>Tanggal Bayar</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($iuranList as $i): 
                                        $jenis = $jenisIuranConfig[$i['jenis_iuran']] ?? ['label' => $i['jenis_iuran'], 'icon' => '📌'];
                                    ?>
                                    <tr>
                                        <td><?= sanitize($i['user_name']) ?></td>
                                        <td><?= $jenis['icon'] ?> <?= $jenis['label'] ?></td>
                                        <td><?= date('F Y', strtotime($i['bulan'] . '-01')) ?></td>
                                        <td><?= formatRupiah($i['nominal']) ?></td>
                                        <td><?= $i['tanggal_bayar'] ? formatTanggal($i['tanggal_bayar'], 'd M Y') : '-' ?></td>
                                        <td>
                                            <?php if ($i['status'] === 'lunas'): ?>
                                                <span class="badge badge-success">Lunas</span>
                                            <?php else: ?>
                                                <span class="badge badge-danger">Belum Dibayar</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($i['status'] === 'belum_bayar'): ?>
                                                <?php if ($isStaff): ?>
                                                <button class="btn btn-sm btn-success" onclick="konfirmasiBayar(<?= $i['id'] ?>)" title="Konfirmasi Pembayaran">✓</button>
                                                <?php else: ?>
                                                <button class="btn btn-sm btn-primary" onclick="bayarIuran(<?= $i['id'] ?>)">Bayar</button>
                                                <?php endif; ?>
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

    <!-- Add Iuran Modal (Staff Only) -->
    <div class="modal-overlay" id="addIuranModal">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title">Catat Iuran</h3>
                <button class="modal-close" onclick="closeModal('addIuranModal')">&times;</button>
            </div>
            <form id="addIuranForm" onsubmit="handleAdd(event)">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Warga</label>
                        <select class="form-control" name="user_id" required>
                            <option value="">Pilih Warga</option>
                            <?php
                            $stmt = $pdo->query("SELECT id, nama, no_rumah FROM users WHERE role = 'warga' AND status = 'aktif' ORDER BY nama");
                            while ($w = $stmt->fetch()) {
                                echo "<option value='{$w['id']}'>{$w['nama']} ({$w['no_rumah']})</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Jenis Iuran</label>
                        <select class="form-control" name="jenis_iuran" required>
                            <option value="">Pilih Jenis</option>
                            <option value="kebersihan">Kebersihan</option>
                            <option value="keamanan">Keamanan</option>
                            <option value="sampah">Sampah</option>
                            <option value="lainnya">Lainnya</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Nominal (Rp)</label>
                        <input type="number" class="form-control" name="nominal" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Bulan</label>
                        <input type="month" class="form-control" name="bulan" value="<?= date('Y-m') ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Keterangan</label>
                        <textarea class="form-control" name="keterangan" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('addIuranModal')">Batal</button>
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
                const response = await fetch('api/iuran.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                const result = await response.json();

                if (result.success) {
                    alert('Iuran berhasil dicatat');
                    closeModal('addIuranModal');
                    window.location.reload();
                } else {
                    alert('Gagal mencatat iuran: ' + result.message);
                }
            } catch (error) {
                alert('Terjadi kesalahan');
                console.error(error);
            }
        }

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
                        alert('✅ Pembayaran berhasil dikonfirmasi');
                        window.location.reload();
                    } else {
                        alert('❌ Gagal konfirmasi pembayaran: ' + result.message);
                    }
                } catch (error) {
                    alert('❌ Terjadi kesalahan');
                    console.error(error);
                }
            }
        }

        async function konfirmasiBayar(id) {
            if (confirm('✓ Konfirmasi pembayaran iuran ini sebagai LUNAS?')) {
                try {
                    const response = await fetch('api/iuran.php', {
                        method: 'PUT',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id, action: 'konfirmasi' })
                    });
                    const result = await response.json();

                    if (result.success) {
                        alert('✅ Pembayaran berhasil dikonfirmasi');
                        window.location.reload();
                    } else {
                        alert('❌ Gagal konfirmasi pembayaran: ' + result.message);
                    }
                } catch (error) {
                    alert('❌ Terjadi kesalahan');
                    console.error(error);
                }
            }
        }

        async function detailIuran(id) {
            try {
                const response = await fetch(`api/iuran.php?id=${id}`);
                const result = await response.json();
                
                if (result.success) {
                    const i = result.data;
                    alert(`Detail Iuran:\n\nWarga: ${i.user_name}\nJenis: ${i.jenis_iuran}\nNominal: Rp ${i.nominal.toLocaleString()}\nBulan: ${i.bulan}\nStatus: ${i.status}\nKeterangan: ${i.keterangan || '-'}`);
                }
            } catch (error) {
                alert('Gagal memuat detail');
            }
        }
    </script>
</body>
</html>
