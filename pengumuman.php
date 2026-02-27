<?php
/**
 * Pengumuman Page
 * VILLA BINTARO REGENCY RT/RW Digital
 */

require_once __DIR__ . '/api/config.php';
require_once __DIR__ . '/includes/auth.php';
requireLogin();

$user = getCurrentUser();
$isStaff = isStaff();

// Fetch announcements from database
try {
    $pdo = getDbConnection();
    $stmt = $pdo->prepare("
        SELECT p.*, u.nama as posted_by_name 
        FROM pengumuman p 
        LEFT JOIN users u ON p.dibuat_oleh = u.id 
        ORDER BY COALESCE(p.created_at, p.tanggal_post) DESC
    ");
    $stmt->execute();
    $pengumumanList = $stmt->fetchAll();
} catch (PDOException $e) {
    $pengumumanList = [];
    $error = $e->getMessage();
}

// Category configuration
$kategoriConfig = [
    'umum' => ['label' => 'Umum', 'icon' => '📢', 'color' => 'rgba(37, 99, 235, 0.1)'],
    'penting' => ['label' => 'Penting', 'icon' => '💰', 'color' => 'rgba(16, 185, 129, 0.1)'],
    'warga' => ['label' => 'Warga', 'icon' => '📅', 'color' => 'rgba(245, 158, 11, 0.1)'],
    'darurat' => ['label' => 'Darurat', 'icon' => '⚠️', 'color' => 'rgba(239, 68, 68, 0.1)']
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengumuman - VILLA BINTARO REGENCY RT/RW Digital</title>
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
                <a href="pengumuman.php" class="sidebar-menu-item active"><span class="sidebar-menu-icon">📢</span><span>Pengumuman</span></a>
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
                <div class="sidebar-user-avatar"><?= getUserAvatar($user['name']) ?></div>
                <div class="sidebar-user-info">
                    <div class="sidebar-user-name"><?= sanitize($user['name']) ?></div>
                    <div class="sidebar-user-role"><?= getRoleDisplayName($user['role']) ?></div>
                </div>
                <button onclick="logout()" class="btn btn-ghost btn-icon" title="Logout">🚪</button>
            </div>
        </aside>
        <main class="main-content">
            <header class="topbar">
                <div class="topbar-left">
                    <button class="menu-toggle" id="menuToggle">☰</button>
                    <h1 class="page-title">Pengumuman</h1>
                </div>
                <div class="topbar-right">
                    <button class="notification-btn" onclick="window.location.href='notifikasi.php'">🔔<span class="notification-badge">5</span></button>
                </div>
            </header>
            <div class="content">
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 24px;">
                    <?php if (empty($pengumumanList)): ?>
                        <!-- Empty State -->
                        <div style="grid-column: 1 / -1; text-align: center; padding: 60px 20px;">
                            <div style="font-size: 4rem; margin-bottom: 16px;">📢</div>
                            <h3 style="font-size: 1.25rem; margin-bottom: 8px;">Belum Ada Pengumuman</h3>
                            <p style="color: var(--gray-500); margin-bottom: 24px;">Pengumuman akan muncul di sini ketika ada pengumuman baru dari pengurus RT/RW.</p>
                            <?php if ($isStaff): ?>
                                <button class="btn btn-primary" onclick="openModal('addPengumumanModal')">Tambah Pengumuman Pertama</button>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <?php foreach ($pengumumanList as $pengumuman): 
                            $kategori = $kategoriConfig[$pengumuman['kategori']] ?? $kategoriConfig['umum'];
                            // Map fields for display (handle both old and new column names)
                            $tanggalPost = $pengumuman['created_at'] ?? $pengumuman['tanggal_post'];
                            $tanggalEvent = $pengumuman['tanggal_acara'] ?? $pengumuman['tanggal_event'] ?? null;
                            $lokasi = $pengumuman['lokasi_acara'] ?? $pengumuman['lokasi'] ?? null;
                        ?>
                        <!-- Announcement Card -->
                        <div class="card">
                            <div class="card-body">
                                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px;">
                                    <div style="display: flex; align-items: center; gap: 12px;">
                                        <div style="width: 50px; height: 50px; background: <?= $kategori['color'] ?>; border-radius: var(--radius); display: flex; align-items: center; justify-content: center; font-size: 1.5rem;"><?= $kategori['icon'] ?></div>
                                        <div>
                                            <h3 style="font-size: 1.1rem; margin-bottom: 4px;"><?= sanitize($pengumuman['judul']) ?></h3>
                                            <span class="badge badge-<?= $pengumuman['kategori'] == 'penting' ? 'success' : ($pengumuman['kategori'] == 'darurat' ? 'danger' : ($pengumuman['kategori'] == 'warga' ? 'warning' : 'info')) ?>"><?= $kategori['label'] ?></span>
                                        </div>
                                    </div>
                                    <div style="text-align: right;">
                                        <div style="font-size: 0.85rem; color: var(--gray-500);"><?= formatTanggal($tanggalPost, 'd M Y') ?></div>
                                        <div style="font-size: 0.8rem; color: var(--gray-400);"><?= date('H:i', strtotime($tanggalPost)) ?> WIB</div>
                                    </div>
                                </div>
                                <p style="color: var(--gray-600); margin-bottom: 16px; line-height: 1.7;">
                                    <?= nl2br(sanitize(substr($pengumuman['isi'], 0, 150))) ?><?= strlen($pengumuman['isi']) > 150 ? '...' : '' ?>
                                    <?php if (!empty($tanggalEvent)): ?>
                                    <br><br>
                                    📅 <?= formatTanggal($tanggalEvent, 'd M Y') ?><br>
                                    ⏰ <?= date('H:i', strtotime($tanggalEvent)) ?> WIB<br>
                                    <?php endif; ?>
                                    <?php if (!empty($lokasi)): ?>
                                    📍 <?= sanitize($lokasi) ?>
                                    <?php endif; ?>
                                </p>
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <div style="display: flex; align-items: center; gap: 8px; color: var(--gray-500); font-size: 0.9rem;">
                                        <span>👁️</span>
                                        <span><?= number_format($pengumuman['views'], 0, ',', '.') ?> views</span>
                                    </div>
                                    <div style="display: flex; gap: 8px;">
                                        <button class="btn btn-sm btn-outline" onclick="viewPengumuman(<?= $pengumuman['id'] ?>)">👁️ Lihat</button>
                                        <?php if ($isStaff): ?>
                                        <button class="btn btn-sm btn-outline" onclick="editPengumuman(<?= $pengumuman['id'] ?>)">✏️</button>
                                        <button class="btn btn-sm btn-danger" onclick="confirmDelete(<?= $pengumuman['id'] ?>)">🗑️</button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <?php if ($isStaff): ?>
                <button class="btn btn-primary btn-lg" onclick="openModal('addPengumumanModal')" style="position: fixed; bottom: 32px; right: 32px; border-radius: 50%; width: 60px; height: 60px; padding: 0; font-size: 1.5rem; box-shadow: var(--shadow-xl);">+</button>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Add Pengumuman Modal -->
    <div class="modal-overlay" id="addPengumumanModal">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title">Tambah Pengumuman</h3>
                <button class="modal-close" onclick="closeModal('addPengumumanModal')">&times;</button>
            </div>
            <form id="addPengumumanForm" onsubmit="handleAdd(event)">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Judul Pengumuman</label>
                        <input type="text" class="form-control" name="judul" placeholder="Masukkan judul pengumuman" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Kategori</label>
                        <select class="form-control" name="kategori" required>
                            <option value="">Pilih Kategori</option>
                            <option value="umum">Umum</option>
                            <option value="penting">Penting</option>
                            <option value="warga">Warga</option>
                            <option value="darurat">Darurat</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Isi Pengumuman</label>
                        <textarea class="form-control" name="isi" rows="6" placeholder="Tulis isi pengumuman..." required></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Tanggal & Waktu Acara (Opsional)</label>
                        <input type="datetime-local" class="form-control" name="tanggal_event">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Lokasi (Opsional)</label>
                        <input type="text" class="form-control" name="lokasi" placeholder="Contoh: Balai Warga">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('addPengumumanModal')">Batal</button>
                    <button type="submit" class="btn btn-primary">Terbitkan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- View Pengumuman Modal -->
    <div class="modal-overlay" id="viewPengumumanModal">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title">Detail Pengumuman</h3>
                <button class="modal-close" onclick="closeModal('viewPengumumanModal')">&times;</button>
            </div>
            <div class="modal-body" id="viewPengumumanContent">
                <!-- Content will be loaded dynamically -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('viewPengumumanModal')">Tutup</button>
                <button type="button" class="btn btn-primary" onclick="sharePengumuman()">📤 Bagikan</button>
            </div>
        </div>
    </div>

    <!-- Edit Pengumuman Modal -->
    <div class="modal-overlay" id="editPengumumanModal">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title">Edit Pengumuman</h3>
                <button class="modal-close" onclick="closeModal('editPengumumanModal')">&times;</button>
            </div>
            <form id="editPengumumanForm" onsubmit="handleEdit(event)">
                <input type="hidden" name="id" id="editId">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Judul Pengumuman</label>
                        <input type="text" class="form-control" name="judul" id="editJudul" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Kategori</label>
                        <select class="form-control" name="kategori" id="editKategori" required>
                            <option value="umum">Umum</option>
                            <option value="penting">Penting</option>
                            <option value="warga">Warga</option>
                            <option value="darurat">Darurat</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Isi Pengumuman</label>
                        <textarea class="form-control" name="isi" id="editIsi" rows="6" required></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Tanggal & Waktu Acara (Opsional)</label>
                        <input type="datetime-local" class="form-control" name="tanggal_event" id="editTanggalEvent">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Lokasi (Opsional)</label>
                        <input type="text" class="form-control" name="lokasi" id="editLokasi">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('editPengumumanModal')">Batal</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>

    <script src="js/main.js"></script>
    <script>
        const currentUserId = <?= $user['id'] ?>;
        const isStaff = <?= $isStaff ? 'true' : 'false' ?>;

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

        // View Pengumuman Detail
        async function viewPengumuman(id) {
            try {
                const response = await fetch(`api/pengumuman.php?id=${id}`);
                const result = await response.json();
                
                if (result.success) {
                    const p = result.data;
                    const kategoriConfig = {
                        'umum': { label: 'Umum', icon: '📢', color: 'rgba(37, 99, 235, 0.1)' },
                        'penting': { label: 'Penting', icon: '💰', color: 'rgba(16, 185, 129, 0.1)' },
                        'warga': { label: 'Warga', icon: '📅', color: 'rgba(245, 158, 11, 0.1)' },
                        'darurat': { label: 'Darurat', icon: '⚠️', color: 'rgba(239, 68, 68, 0.1)' }
                    };
                    const kat = kategoriConfig[p.kategori] || kategoriConfig['umum'];
                    
                    document.getElementById('viewPengumumanContent').innerHTML = `
                        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 20px;">
                            <div style="width: 50px; height: 50px; background: ${kat.color}; border-radius: var(--radius); display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">${kat.icon}</div>
                            <div>
                                <h3 style="font-size: 1.2rem; margin-bottom: 4px;">${escapeHtml(p.judul)}</h3>
                                <span class="badge badge-${p.kategori === 'penting' ? 'success' : (p.kategori === 'darurat' ? 'danger' : (p.kategori === 'warga' ? 'warning' : 'info'))}">${kat.label}</span>
                            </div>
                        </div>
                        <div style="display: flex; gap: 24px; margin-bottom: 20px; padding: 16px; background: var(--gray-100); border-radius: var(--radius);">
                            <div>
                                <div style="font-size: 0.85rem; color: var(--gray-500);">Diposting</div>
                                <div style="font-weight: 500;">${formatDate(p.tanggal_post)}</div>
                            </div>
                            <div>
                                <div style="font-size: 0.85rem; color: var(--gray-500);">Oleh</div>
                                <div style="font-weight: 500;">${escapeHtml(p.posted_by_name || 'Admin')}</div>
                            </div>
                            <div>
                                <div style="font-size: 0.85rem; color: var(--gray-500);">Views</div>
                                <div style="font-weight: 500;">${p.views}</div>
                            </div>
                        </div>
                        <div style="line-height: 1.8; color: var(--gray-700);">
                            ${escapeHtml(p.isi).replace(/\n/g, '<br>')}
                            ${p.tanggal_event ? `<br><br><strong>📅 Tanggal:</strong> ${formatDate(p.tanggal_event)}<br><strong>⏰ Waktu:</strong> ${formatTime(p.tanggal_event)}` : ''}
                            ${p.lokasi ? `<br><strong>📍 Lokasi:</strong> ${escapeHtml(p.lokasi)}` : ''}
                        </div>
                    `;
                    openModal('viewPengumumanModal');
                } else {
                    alert('Gagal memuat detail pengumuman: ' + result.message);
                }
            } catch (error) {
                alert('Terjadi kesalahan saat memuat detail pengumuman');
                console.error(error);
            }
        }

        // Edit Pengumuman
        async function editPengumuman(id) {
            try {
                const response = await fetch(`api/pengumuman.php?id=${id}`);
                const result = await response.json();
                
                if (result.success) {
                    const p = result.data;
                    document.getElementById('editId').value = p.id;
                    document.getElementById('editJudul').value = p.judul;
                    document.getElementById('editKategori').value = p.kategori;
                    document.getElementById('editIsi').value = p.isi;
                    
                    if (p.tanggal_event) {
                        const date = new Date(p.tanggal_event);
                        document.getElementById('editTanggalEvent').value = date.toISOString().slice(0, 16);
                    }
                    
                    document.getElementById('editLokasi').value = p.lokasi || '';
                    openModal('editPengumumanModal');
                } else {
                    alert('Gagal memuat data pengumuman: ' + result.message);
                }
            } catch (error) {
                alert('Terjadi kesalahan saat memuat data pengumuman');
                console.error(error);
            }
        }

        // Handle Add
        async function handleAdd(e) {
            e.preventDefault();
            const form = e.target;
            const formData = new FormData(form);
            const data = Object.fromEntries(formData);

            try {
                const response = await fetch('api/pengumuman.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                const result = await response.json();

                if (result.success) {
                    alert('Pengumuman berhasil ditambahkan');
                    closeModal('addPengumumanModal');
                    window.location.reload();
                } else {
                    alert('Gagal menambahkan pengumuman: ' + result.message);
                }
            } catch (error) {
                alert('Terjadi kesalahan saat menambahkan pengumuman');
                console.error(error);
            }
        }

        // Handle Edit
        async function handleEdit(e) {
            e.preventDefault();
            const form = e.target;
            const formData = new FormData(form);
            const data = Object.fromEntries(formData);

            try {
                const response = await fetch('api/pengumuman.php', {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                const result = await response.json();

                if (result.success) {
                    alert('Pengumuman berhasil diupdate');
                    closeModal('editPengumumanModal');
                    window.location.reload();
                } else {
                    alert('Gagal mengupdate pengumuman: ' + result.message);
                }
            } catch (error) {
                alert('Terjadi kesalahan saat mengupdate pengumuman');
                console.error(error);
            }
        }

        // Confirm Delete
        function confirmDelete(id) {
            if (confirm('Apakah Anda yakin ingin menghapus pengumuman ini?')) {
                deletePengumuman(id);
            }
        }

        // Delete Pengumuman
        async function deletePengumuman(id) {
            try {
                const response = await fetch('api/pengumuman.php', {
                    method: 'DELETE',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id })
                });
                const result = await response.json();

                if (result.success) {
                    alert('Pengumuman berhasil dihapus');
                    window.location.reload();
                } else {
                    alert('Gagal menghapus pengumuman: ' + result.message);
                }
            } catch (error) {
                alert('Terjadi kesalahan saat menghapus pengumuman');
                console.error(error);
            }
        }

        // Share Pengumuman
        function sharePengumuman() {
            if (navigator.share) {
                navigator.share({
                    title: document.querySelector('#viewPengumumanModal h3')?.textContent || 'Pengumuman',
                    text: document.querySelector('#viewPengumumanContent')?.textContent || '',
                    url: window.location.href
                });
            } else {
                alert('Fitur bagikan tidak didukung di browser ini');
            }
        }

        // Helper functions
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function formatDate(dateString) {
            const date = new Date(dateString);
            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
            return `${date.getDate()} ${months[date.getMonth()]} ${date.getFullYear()}`;
        }

        function formatTime(dateString) {
            const date = new Date(dateString);
            return `${String(date.getHours()).padStart(2, '0')}:${String(date.getMinutes()).padStart(2, '0')} WIB`;
        }
    </script>
</body>
</html>
