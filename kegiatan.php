<?php
/**
 * Kegiatan Page
 * VILLA BINTARO REGENCY RT/RW Digital
 */

require_once __DIR__ . '/api/config.php';
require_once __DIR__ . '/includes/auth.php';
requireLogin();

$user = getCurrentUser();
$isStaff = isStaff();

// Fetch kegiatan from database
try {
    $pdo = getDbConnection();
    
    // Get upcoming kegiatan (future events)
    $stmt = $pdo->prepare("
        SELECT k.*, u.nama as creator_name 
        FROM kegiatan k 
        LEFT JOIN users u ON k.dibuat_oleh = u.id 
        WHERE k.tanggal_kegiatan >= CURDATE() AND k.status = 'aktif'
        ORDER BY k.tanggal_kegiatan ASC
        LIMIT 6
    ");
    $stmt->execute();
    $upcomingKegiatan = $stmt->fetchAll();
    
    // Get past kegiatan
    $stmt = $pdo->prepare("
        SELECT k.*, u.nama as creator_name 
        FROM kegiatan k 
        LEFT JOIN users u ON k.dibuat_oleh = u.id 
        WHERE k.tanggal_kegiatan < CURDATE()
        ORDER BY k.tanggal_kegiatan DESC
        LIMIT 10
    ");
    $stmt->execute();
    $pastKegiatan = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $upcomingKegiatan = [];
    $pastKegiatan = [];
}

$kategoriConfig = [
    'umum' => ['label' => 'Umum', 'color' => 'blue', 'icon' => '📢'],
    'kesehatan' => ['label' => 'Kesehatan', 'color' => 'green', 'icon' => '💉'],
    'sosial' => ['label' => 'Sosial', 'color' => 'yellow', 'icon' => '🤝'],
    'olahraga' => ['label' => 'Olahraga', 'color' => 'orange', 'icon' => '⚽'],
    'keagamaan' => ['label' => 'Keagamaan', 'color' => 'purple', 'icon' => '🕌'],
    'pengurus' => ['label' => 'Pengurus', 'color' => 'red', 'icon' => '👥']
];

$kategoriIcons = [
    'umum' => '🧹',
    'kesehatan' => '💉',
    'sosial' => '🤝',
    'olahraga' => '⚽',
    'keagamaan' => '🕌',
    'pengurus' => '👥'
];

function formatTanggalIndo($date) {
    if (empty($date)) return '-';
    $bulan = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    $d = date('j', strtotime($date));
    $m = $bulan[(int)date('n', strtotime($date)) - 1];
    $y = date('Y', strtotime($date));
    return "$d $m $y";
}

function formatWaktuIndo($date) {
    if (empty($date)) return '-';
    return date('H:i', strtotime($date)) . ' WIB';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kegiatan - VILLA BINTARO REGENCY RT/RW Digital</title>
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
                <a href="kegiatan.php" class="sidebar-menu-item active"><span class="sidebar-menu-icon">📅</span><span>Kegiatan</span></a>
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
                    <h1 class="page-title">Kegiatan</h1>
                </div>
                <div class="topbar-right">
                    <button class="notification-btn" onclick="window.location.href='notifikasi.php'">🔔<span class="notification-badge">5</span></button>
                </div>
            </header>

            <div class="content">
                <!-- Upcoming Events -->
                <div style="margin-bottom: 32px;">
                    <h2 style="font-size: 1.5rem; margin-bottom: 20px;">📅 Kegiatan Mendatang</h2>
                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 24px;">
                        <?php if (empty($upcomingKegiatan)): ?>
                            <div style="grid-column: 1 / -1; text-align: center; padding: 60px 20px;">
                                <div style="font-size: 4rem; margin-bottom: 16px;">📅</div>
                                <h3 style="font-size: 1.25rem; margin-bottom: 8px;">Belum Ada Kegiatan</h3>
                                <p style="color: var(--gray-500);">Kegiatan akan muncul di sini ketika ada kegiatan yang dijadwalkan.</p>
                                <?php if ($isStaff): ?>
                                <button class="btn btn-primary" style="margin-top: 16px;" onclick="openModal('addKegiatanModal')">➕ Tambah Kegiatan Pertama</button>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <?php foreach ($upcomingKegiatan as $kegiatan): 
                                $kat = $kategoriConfig[$kegiatan['kategori']] ?? $kategoriConfig['umum'];
                                $icon = $kategoriIcons[$kegiatan['kategori']] ?? '📅';
                            ?>
                            <div class="card">
                                <div style="height: 150px; background: linear-gradient(135deg, var(--<?= $kat['color'] ?>-color), var(--<?= $kat['color'] ?>-light)); display: flex; align-items: center; justify-content: center; font-size: 4rem;"><?= $icon ?></div>
                                <div class="card-body">
                                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                                        <h3 style="font-size: 1.2rem;"><?= sanitize($kegiatan['nama_kegiatan']) ?></h3>
                                        <span class="badge badge-<?= $kat['color'] ?>"><?= $kat['label'] ?></span>
                                    </div>
                                    <div style="display: flex; gap: 16px; margin-bottom: 16px; color: var(--gray-600); font-size: 0.9rem;">
                                        <span>📅 <?= formatTanggalIndo($kegiatan['tanggal_kegiatan']) ?></span>
                                        <span>⏰ <?= formatWaktuIndo($kegiatan['tanggal_kegiatan']) ?></span>
                                    </div>
                                    <p style="color: var(--gray-600); margin-bottom: 16px; font-size: 0.95rem;">
                                        <?= sanitize(substr($kegiatan['deskripsi'], 0, 100)) ?><?= strlen($kegiatan['deskripsi']) > 100 ? '...' : '' ?>
                                    </p>
                                    <div style="display: flex; justify-content: space-between; align-items: center;">
                                        <span style="color: var(--gray-500); font-size: 0.9rem;">👥 <?= $kegiatan['jumlah_peserta'] ?> warga terdaftar</span>
                                        <button class="btn btn-sm btn-primary" onclick="joinKegiatan(<?= $kegiatan['id'] ?>)">Daftar</button>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Past Events Table -->
                <div class="table-container">
                    <div class="table-header">
                        <h3 class="card-title">Riwayat Kegiatan</h3>
                        <?php if ($isStaff): ?>
                        <button class="btn btn-primary" onclick="openModal('addKegiatanModal')">➕ Tambah Kegiatan</button>
                        <?php endif; ?>
                    </div>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Kegiatan</th>
                                <th>Tanggal</th>
                                <th>Peserta</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($pastKegiatan)): ?>
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 40px; color: var(--gray-500);">
                                    Belum ada riwayat kegiatan
                                </td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($pastKegiatan as $i => $k): 
                                $kat = $kategoriConfig[$k['kategori']] ?? $kategoriConfig['umum'];
                            ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 12px;">
                                        <div style="width: 40px; height: 40px; background: rgba(37, 99, 235, 0.1); border-radius: var(--radius); display: flex; align-items: center; justify-content: center; font-size: 1.5rem;"><?= $kategoriIcons[$k['kategori']] ?? '📅' ?></div>
                                        <span><?= sanitize($k['nama_kegiatan']) ?></span>
                                    </div>
                                </td>
                                <td><?= formatTanggalIndo($k['tanggal_kegiatan']) ?></td>
                                <td><?= $k['jumlah_peserta'] ?> warga</td>
                                <td>
                                    <?php if ($k['status'] == 'aktif'): ?>
                                        <span class="badge badge-success">Selesai</span>
                                    <?php elseif ($k['status'] == 'selesai'): ?>
                                        <span class="badge badge-success">Selesai</span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary">Batal</span>
                                    <?php endif; ?>
                                </td>
                                <td class="table-actions">
                                    <button class="btn btn-sm btn-outline" onclick="viewLaporan(<?= $k['id'] ?>)">📊 Laporan</button>
                                    <button class="btn btn-sm btn-outline" onclick="viewGaleri(<?= $k['id'] ?>)">📷 Galeri</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Add Kegiatan Modal -->
    <div class="modal-overlay" id="addKegiatanModal">
        <div class="modal modal-lg">
            <div class="modal-header">
                <h3 class="modal-title">Tambah Kegiatan</h3>
                <button class="modal-close" onclick="closeModal('addKegiatanModal')">&times;</button>
            </div>
            <form id="addKegiatanForm" onsubmit="handleAdd(event)">
                <div class="modal-body">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div class="form-group" style="grid-column: 1 / -1;">
                            <label class="form-label">Nama Kegiatan *</label>
                            <input type="text" class="form-control" name="nama_kegiatan" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Kategori *</label>
                            <select class="form-control" name="kategori" required>
                                <option value="">Pilih Kategori</option>
                                <option value="umum">Umum</option>
                                <option value="kesehatan">Kesehatan</option>
                                <option value="sosial">Sosial</option>
                                <option value="olahraga">Olahraga</option>
                                <option value="keagamaan">Keagamaan</option>
                                <option value="pengurus">Pengurus</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Tanggal & Waktu *</label>
                            <input type="datetime-local" class="form-control" name="tanggal_kegiatan" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Lokasi</label>
                            <input type="text" class="form-control" name="lokasi">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Kuota Peserta</label>
                            <input type="number" class="form-control" name="kuota_peserta">
                        </div>
                        <div class="form-group" style="grid-column: 1 / -1;">
                            <label class="form-label">Deskripsi</label>
                            <textarea class="form-control" name="deskripsi" rows="4"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('addKegiatanModal')">Batal</button>
                    <button type="submit" class="btn btn-primary">Tambah Kegiatan</button>
                </div>
            </form>
        </div>
    </div>

    <script src="js/main.js"></script>
    <script>
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

        async function joinKegiatan(id) {
            if (confirm('Apakah Anda ingin mendaftar kegiatan ini?')) {
                try {
                    const response = await fetch('api/kegiatan.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ kegiatan_id: id, action: 'join' })
                    });
                    const result = await response.json();
                    
                    if (result.success) {
                        alert('Berhasil mendaftar kegiatan');
                        window.location.reload();
                    } else {
                        alert('Gagal mendaftar: ' + result.message);
                    }
                } catch (error) {
                    alert('Terjadi kesalahan');
                    console.error(error);
                }
            }
        }

        async function handleAdd(e) {
            e.preventDefault();
            const form = e.target;
            const formData = new FormData(form);
            const data = Object.fromEntries(formData);

            try {
                const response = await fetch('api/kegiatan.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                const result = await response.json();

                if (result.success) {
                    alert('Kegiatan berhasil ditambahkan');
                    closeModal('addKegiatanModal');
                    window.location.reload();
                } else {
                    alert('Gagal menambahkan kegiatan: ' + result.message);
                }
            } catch (error) {
                alert('Terjadi kesalahan');
                console.error(error);
            }
        }

        async function viewLaporan(id) {
            try {
                const response = await fetch(`api/kegiatan.php?id=${id}`);
                const result = await response.json();
                
                if (result.success) {
                    const k = result.data;
                    const laporanText = `
LAPORAN KEGIATAN
================

Nama Kegiatan: ${k.nama_kegiatan}
Kategori: ${k.kategori}
Tanggal: ${formatTanggal(k.tanggal_kegiatan)}
Lokasi: ${k.lokasi || '-'}

DESKRIPSI:
${k.deskripsi || '-'}

STATISTIK:
- Kuota: ${k.kuota_peserta || 'Tidak terbatas'} peserta
- Terdaftar: ${k.jumlah_peserta} peserta
- Status: ${k.status === 'aktif' || k.status === 'selesai' ? 'Selesai' : 'Batal'}

Dibuat oleh: ${k.creator_name || 'Admin'}
                    `.trim();
                    
                    alert(laporanText);
                }
            } catch (error) {
                alert('Gagal memuat laporan');
                console.error(error);
            }
        }

        async function viewGaleri(id) {
            try {
                // Get kegiatan detail
                const response = await fetch(`api/kegiatan.php?id=${id}`);
                const result = await response.json();
                
                if (!result.success) {
                    alert('Gagal memuat data kegiatan');
                    return;
                }
                
                const k = result.data;
                
                // Get galeri photos
                const galeriResponse = await fetch(`api/kegiatan_galeri.php?kegiatan_id=${id}`);
                const galeriResult = await galeriResponse.json();
                
                let galeriHTML = `
<div style="text-align: left;">
    <h3 style="margin-bottom: 8px;">${k.nama_kegiatan}</h3>
    <p style="color: var(--gray-500); margin-bottom: 16px;">${formatTanggal(k.tanggal_kegiatan)}</p>
    
    <div id="galeriPhotos" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 12px; margin-bottom: 20px;">
`;
                
                if (galeriResult.success && galeriResult.data.length > 0) {
                    galeriResult.data.forEach(foto => {
                        galeriHTML += `
    <div style="position: relative; group: galeri-item;">
        <img src="uploads/kegiatan/${foto.file_path}" 
             style="width: 100%; height: 150px; object-fit: cover; border-radius: 8px; cursor: pointer;" 
             onclick="viewFullImage('uploads/kegiatan/${foto.file_path}')"
             alt="${foto.keterangan || 'Foto kegiatan'}">
        ${isStaff ? `
        <button onclick="deleteFoto(${foto.id}, '${foto.file_path}')" 
                style="position: absolute; top: 8px; right: 8px; background: rgba(239, 68, 68, 0.9); color: white; border: none; border-radius: 50%; width: 32px; height: 32px; cursor: pointer; display: flex; align-items: center; justify-content: center;"
                title="Hapus foto">🗑️</button>
        ` : ''}
    </div>
`;
                    });
                } else {
                    galeriHTML = `
    <div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: var(--gray-500);">
        <div style="font-size: 3rem; margin-bottom: 16px;">📷</div>
        <p>Belum ada foto untuk kegiatan ini</p>
    </div>
`;
                }
                
                galeriHTML += `
    </div>
    
    ${isStaff ? `
    <div style="border-top: 1px solid var(--gray-200); padding-top: 16px;">
        <h4 style="margin-bottom: 12px;">📤 Upload Foto</h4>
        <form id="uploadFotoForm" enctype="multipart/form-data">
            <input type="hidden" name="kegiatan_id" value="${id}">
            <div style="display: flex; gap: 12px; align-items: flex-end;">
                <div style="flex: 1;">
                    <label style="display: block; margin-bottom: 4px; font-size: 0.9rem;">Pilih Foto</label>
                    <input type="file" name="foto" accept="image/*" required 
                           style="width: 100%; padding: 8px; border: 1px solid var(--gray-300); border-radius: var(--radius);">
                    <small style="color: var(--gray-500);">Max 20MB (JPG, PNG, GIF, WEBP)</small>
                </div>
                <div style="flex: 1;">
                    <label style="display: block; margin-bottom: 4px; font-size: 0.9rem;">Keterangan (Opsional)</label>
                    <input type="text" name="keterangan" placeholder="Contoh: Foto bersama peserta" 
                           style="width: 100%; padding: 8px; border: 1px solid var(--gray-300); border-radius: var(--radius);">
                </div>
                <button type="submit" class="btn btn-primary">Upload</button>
            </div>
        </form>
    </div>
    ` : ''}
</div>
`;
                
                // Show modal with galeri
                showCustomModal('Galeri Kegiatan', galeriHTML);
                
                // Attach upload form handler
                if (isStaff) {
                    document.getElementById('uploadFotoForm')?.addEventListener('submit', function(e) {
                        e.preventDefault();
                        uploadFoto(id, this);
                    });
                }
                
            } catch (error) {
                alert('Gagal memuat galeri');
                console.error(error);
            }
        }

        function showCustomModal(title, content) {
            // Close existing modal if any
            const existingModal = document.getElementById('customGaleriModal');
            if (existingModal) {
                existingModal.remove();
            }
            
            const modal = document.createElement('div');
            modal.className = 'modal-overlay active';
            modal.id = 'customGaleriModal';
            modal.innerHTML = `
<div class="modal modal-lg" onclick="event.stopPropagation()">
    <div class="modal-header">
        <h3 class="modal-title">${title}</h3>
        <button class="modal-close" onclick="closeCustomModal()">&times;</button>
    </div>
    <div class="modal-body">
        ${content}
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeCustomModal()">Tutup</button>
    </div>
</div>
`;
            document.body.appendChild(modal);
            
            // Close on overlay click
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    closeCustomModal();
                }
            });
        }

        function closeCustomModal() {
            const modal = document.getElementById('customGaleriModal');
            if (modal) {
                modal.remove();
            }
        }

        async function uploadFoto(kegiatanId, form) {
            const formData = new FormData(form);
            
            console.log('Uploading foto...');
            console.log('Form data:', Object.fromEntries(formData));
            
            try {
                const response = await fetch('api/kegiatan_galeri.php', {
                    method: 'POST',
                    body: formData
                });
                
                console.log('Response status:', response.status);
                const result = await response.json();
                console.log('Response data:', result);
                
                if (result.success) {
                    alert('✅ ' + result.message);
                    viewGaleri(kegiatanId); // Reload galeri
                } else {
                    alert('❌ Gagal upload: ' + result.message);
                }
            } catch (error) {
                console.error('Upload error:', error);
                alert('❌ Terjadi kesalahan: ' + error.message);
            }
        }

        async function deleteFoto(id, filepath) {
            if (!confirm('Yakin ingin menghapus foto ini?')) {
                return;
            }
            
            try {
                const response = await fetch('api/kegiatan_galeri.php', {
                    method: 'DELETE',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id })
                });
                const result = await response.json();
                
                if (result.success) {
                    alert('Foto berhasil dihapus');
                    // Reload galeri - need to get kegiatan_id from somewhere
                    // For now, just reload page
                    window.location.reload();
                } else {
                    alert('Gagal menghapus: ' + result.message);
                }
            } catch (error) {
                alert('Terjadi kesalahan');
                console.error(error);
            }
        }

        function viewFullImage(src) {
            // Close existing modal if any
            const existingModal = document.getElementById('fullImageModal');
            if (existingModal) {
                existingModal.remove();
            }
            
            const modal = document.createElement('div');
            modal.className = 'modal-overlay active';
            modal.id = 'fullImageModal';
            modal.innerHTML = `
<div class="modal" style="max-width: 90%; max-height: 90%;" onclick="event.stopPropagation()">
    <div class="modal-header">
        <button class="modal-close" onclick="closeFullImageModal()">&times;</button>
    </div>
    <div class="modal-body" style="text-align: center;">
        <img src="${src}" style="max-width: 100%; max-height: 80vh; object-fit: contain;">
    </div>
</div>
`;
            document.body.appendChild(modal);
            
            // Close on overlay click
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    closeFullImageModal();
                }
            });
        }

        function closeFullImageModal() {
            const modal = document.getElementById('fullImageModal');
            if (modal) {
                modal.remove();
            }
        }

        function formatTanggal(dateString) {
            const date = new Date(dateString);
            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
            const day = date.getDate();
            const month = months[date.getMonth()];
            const year = date.getFullYear();
            const hours = String(date.getHours()).padStart(2, '0');
            const minutes = String(date.getMinutes()).padStart(2, '0');
            return `${day} ${month} ${year} ${hours}:${minutes} WIB`;
        }
    </script>
</body>
</html>
