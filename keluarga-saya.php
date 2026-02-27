<?php
/**
 * Keluarga Saya Page
 * VILLA BINTARO REGENCY RT/RW Digital
 */

require_once __DIR__ . '/api/config.php';
require_once __DIR__ . '/includes/auth.php';
requireLogin();

$user = getCurrentUser();

// Fetch family members
try {
    $pdo = getDbConnection();
    
    $stmt = $pdo->prepare("SELECT * FROM anggota_keluarga WHERE user_id = ? ORDER BY hubungan DESC, nama ASC");
    $stmt->execute([$user['id']]);
    $familyMembers = $stmt->fetchAll();
    
    // Count by relationship
    $stmt = $pdo->prepare("SELECT hubungan, COUNT(*) as count FROM anggota_keluarga WHERE user_id = ? GROUP BY hubungan");
    $stmt->execute([$user['id']]);
    $relationshipCounts = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $familyMembers = [];
    $relationshipCounts = [];
}

$hubunganConfig = [
    'kepala_keluarga' => ['label' => 'Kepala Keluarga', 'icon' => '👨', 'color' => 'blue'],
    'istri' => ['label' => 'Istri', 'icon' => '👩', 'color' => 'pink'],
    'suami' => ['label' => 'Suami', 'icon' => '👨', 'color' => 'blue'],
    'anak' => ['label' => 'Anak', 'icon' => '👶', 'color' => 'green'],
    'lainnya' => ['label' => 'Lainnya', 'icon' => '👤', 'color' => 'gray']
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keluarga Saya - VILLA BINTARO REGENCY RT/RW Digital</title>
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
                <a href="keluarga-saya.php" class="sidebar-menu-item active"><span class="sidebar-menu-icon">👨‍👩‍👧‍👦</span><span>Keluarga Saya</span></a>
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
                    <h1 class="page-title">Keluarga Saya</h1>
                </div>
                <div class="topbar-right">
                    <button class="btn btn-sm btn-primary" onclick="openModal('addMemberModal')">➕ Tambah Anggota</button>
                </div>
            </header>

            <div class="content">
                <!-- Stats -->
                <div class="dashboard-stats">
                    <?php
                    $anakCount = 0;
                    $istriCount = 0;
                    $suamiCount = 0;
                    $lainnyaCount = 0;
                    foreach ($relationshipCounts as $r) {
                        if ($r['hubungan'] === 'anak') $anakCount = $r['count'];
                        if ($r['hubungan'] === 'istri') $istriCount = $r['count'];
                        if ($r['hubungan'] === 'suami') $suamiCount = $r['count'];
                        if ($r['hubungan'] === 'lainnya') $lainnyaCount = $r['count'];
                    }
                    ?>
                    <div class="stat-card">
                        <div class="stat-card-icon blue">👨</div>
                        <div class="stat-card-content">
                            <h3><?= $suamiCount ?></h3>
                            <p>Suami</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-card-icon pink">👩</div>
                        <div class="stat-card-content">
                            <h3><?= $istriCount ?></h3>
                            <p>Istri</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-card-icon green">👶</div>
                        <div class="stat-card-content">
                            <h3><?= $anakCount ?></h3>
                            <p>Anak</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-card-icon gray">👤</div>
                        <div class="stat-card-content">
                            <h3><?= $lainnyaCount ?></h3>
                            <p>Lainnya</p>
                        </div>
                    </div>
                </div>

                <!-- Family Members -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Anggota Keluarga</h3>
                    </div>
                    <div class="card-body">
                        <?php if (empty($familyMembers)): ?>
                            <div style="text-align: center; padding: var(--space-6); color: var(--slate-500);">
                                <div style="font-size: 4rem; margin-bottom: 16px;">👨‍👩‍👧‍👦</div>
                                <p>Belum ada anggota keluarga</p>
                                <button class="btn btn-primary" style="margin-top: 16px;" onclick="openModal('addMemberModal')">Tambah Anggota Pertama</button>
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
                                        <th>Pekerjaan</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($familyMembers as $member): 
                                        $hubungan = $hubunganConfig[$member['hubungan']] ?? ['label' => $member['hubungan'], 'icon' => '👤', 'color' => 'gray'];
                                    ?>
                                    <tr>
                                        <td>
                                            <div style="display: flex; align-items: center; gap: 12px;">
                                                <div style="width: 40px; height: 40px; background: var(--<?= $hubungan['color'] ?>-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold;"><?= strtoupper(substr($member['nama'], 0, 1)) ?></div>
                                                <div style="font-weight: 500;"><?= sanitize($member['nama']) ?></div>
                                            </div>
                                        </td>
                                        <td><?= $hubungan['icon'] ?> <?= $hubungan['label'] ?></td>
                                        <td><?= $member['nik'] ?? '-' ?></td>
                                        <td><?= $member['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan' ?></td>
                                        <td><?= $member['tanggal_lahir'] ? formatTanggal($member['tanggal_lahir'], 'd M Y') : '-' ?></td>
                                        <td><?= $member['pekerjaan'] ?? '-' ?></td>
                                        <td>
                                            <?php if ($member['status'] === 'approved' || empty($member['status'])): ?>
                                                <span class="badge badge-success">Disetujui</span>
                                            <?php elseif ($member['status'] === 'pending'): ?>
                                                <span class="badge badge-warning">Pending</span>
                                            <?php else: ?>
                                                <span class="badge badge-danger">Ditolak</span>
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

    <!-- Add Member Modal -->
    <div class="modal-overlay" id="addMemberModal">
        <div class="modal modal-lg">
            <div class="modal-header">
                <h3 class="modal-title">Tambah Anggota Keluarga</h3>
                <button class="modal-close" onclick="closeModal('addMemberModal')">&times;</button>
            </div>
            <form id="addMemberForm" onsubmit="handleAdd(event)">
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
                    <button type="button" class="btn btn-outline" onclick="closeModal('addMemberModal')">Batal</button>
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
                    alert('Request anggota keluarga berhasil ditambahkan');
                    closeModal('addMemberModal');
                    window.location.reload();
                } else {
                    alert('Gagal menambahkan request: ' + result.message);
                }
            } catch (error) {
                alert('Terjadi kesalahan');
                console.error(error);
            }
        }
    </script>
</body>
</html>
