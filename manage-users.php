<?php
/**
 * Manage Users Page
 * VILLA BINTARO REGENCY RT/RW Digital
 */

require_once __DIR__ . '/api/config.php';
require_once __DIR__ . '/includes/auth.php';
requireAuth([ROLE_ADMIN, ROLE_RT, ROLE_RW]);

$user = getCurrentUser();
$isStaff = isStaff();

// Fetch users from database
try {
    $pdo = getDbConnection();
    
    $search = $_GET['search'] ?? '';
    $filterRole = $_GET['role'] ?? '';
    $filterStatus = $_GET['status'] ?? '';
    
    $whereClause = "1=1";
    $params = [];
    
    if (!empty($search)) {
        $whereClause .= " AND (nama LIKE ? OR email LIKE ? OR nik LIKE ?)";
        $searchTerm = "%$search%";
        $params = [$searchTerm, $searchTerm, $searchTerm];
    }
    
    if (!empty($filterRole)) {
        $whereClause .= " AND role = ?";
        $params[] = $filterRole;
    }
    
    if (!empty($filterStatus)) {
        $whereClause .= " AND status = ?";
        $params[] = $filterStatus;
    }
    
    $stmt = $pdo->prepare("
        SELECT * FROM users 
        WHERE $whereClause
        ORDER BY 
            CASE role 
                WHEN 'admin' THEN 1 
                WHEN 'rt' THEN 2 
                WHEN 'rw' THEN 3 
                ELSE 4 
            END,
            nama ASC
    ");
    $stmt->execute($params);
    $userList = $stmt->fetchAll();
    
    // Count by role
    $stmt = $pdo->query("SELECT role, COUNT(*) as count FROM users GROUP BY role");
    $roleCounts = $stmt->fetchAll();
    
    // Count by status
    $stmt = $pdo->query("SELECT status, COUNT(*) as count FROM users GROUP BY status");
    $statusCounts = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $userList = [];
    $roleCounts = [];
    $statusCounts = [];
}

$roleConfig = [
    'admin' => ['label' => 'Administrator', 'color' => 'red', 'icon' => '⚙️'],
    'rt' => ['label' => 'Ketua RT', 'color' => 'blue', 'icon' => '👔'],
    'rw' => ['label' => 'Ketua RW', 'color' => 'purple', 'icon' => '🎖️'],
    'warga' => ['label' => 'Warga', 'color' => 'green', 'icon' => '👤']
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - VILLA BINTARO REGENCY RT/RW Digital</title>
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
                <a href="laporan.php" class="sidebar-menu-item"><span class="sidebar-menu-icon">📈</span><span>Laporan</span></a>
                <?php if ($user['role'] === 'admin'): ?>
                <div style="margin: var(--space-4) var(--space-6); padding-top: var(--space-4); border-top: 1px solid rgba(255,255,255,0.05);">
                    <span style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.1em; color: var(--slate-500); font-weight: 600;">Admin</span>
                </div>
                <a href="manage-users.php" class="sidebar-menu-item active"><span class="sidebar-menu-icon">👥</span><span>Manage Users</span></a>
                <?php endif; ?>
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
                    <h1 class="page-title">Manage Users</h1>
                </div>
                <div class="topbar-right">
                    <button class="btn btn-sm btn-primary" onclick="openModal('addUserModal')">➕ Tambah User</button>
                </div>
            </header>

            <div class="content">
                <!-- Stats -->
                <div class="dashboard-stats">
                    <?php foreach ($roleCounts as $r): 
                        $role = $roleConfig[$r['role']] ?? ['label' => $r['role'], 'color' => 'gray', 'icon' => '👤'];
                    ?>
                    <div class="stat-card">
                        <div class="stat-card-icon <?= $role['color'] ?>"><?= $role['icon'] ?></div>
                        <div class="stat-card-content">
                            <h3><?= $r['count'] ?></h3>
                            <p><?= $role['label'] ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Filter -->
                <div class="card">
                    <div class="card-body">
                        <form method="GET" class="filter-form" style="display: flex; gap: 12px; flex-wrap: wrap;">
                            <input type="text" name="search" class="form-control" placeholder="🔍 Cari nama, email, NIK..." value="<?= sanitize($search) ?>" style="flex: 1; min-width: 250px;">
                            <select name="role" class="form-control" style="width: 150px;">
                                <option value="">Semua Role</option>
                                <option value="admin" <?= $filterRole === 'admin' ? 'selected' : '' ?>>Admin</option>
                                <option value="rt" <?= $filterRole === 'rt' ? 'selected' : '' ?>>RT</option>
                                <option value="rw" <?= $filterRole === 'rw' ? 'selected' : '' ?>>RW</option>
                                <option value="warga" <?= $filterRole === 'warga' ? 'selected' : '' ?>>Warga</option>
                            </select>
                            <select name="status" class="form-control" style="width: 150px;">
                                <option value="">Semua Status</option>
                                <option value="aktif" <?= $filterStatus === 'aktif' ? 'selected' : '' ?>>Aktif</option>
                                <option value="pindah" <?= $filterStatus === 'pindah' ? 'selected' : '' ?>>Pindah</option>
                                <option value="meninggal" <?= $filterStatus === 'meninggal' ? 'selected' : '' ?>>Meninggal</option>
                            </select>
                            <button type="submit" class="btn btn-primary">Filter</button>
                            <a href="manage-users.php" class="btn btn-outline">Reset</a>
                        </form>
                    </div>
                </div>

                <!-- Table -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Daftar Users</h3>
                    </div>
                    <div class="card-body">
                        <?php if (empty($userList)): ?>
                            <div style="text-align: center; padding: var(--space-6); color: var(--slate-500);">
                                <div style="font-size: 4rem; margin-bottom: 16px;">👥</div>
                                <p>Belum ada data users</p>
                            </div>
                        <?php else: ?>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>User</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>RT/RW</th>
                                        <th>Rumah</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($userList as $u): 
                                        $role = $roleConfig[$u['role']] ?? ['label' => $u['role'], 'color' => 'gray', 'icon' => '👤'];
                                    ?>
                                    <tr>
                                        <td>
                                            <div style="display: flex; align-items: center; gap: 12px;">
                                                <div style="width: 40px; height: 40px; background: var(--<?= $role['color'] ?>-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold;"><?= strtoupper(substr($u['nama'], 0, 1)) ?></div>
                                                <div>
                                                    <div style="font-weight: 500;"><?= sanitize($u['nama']) ?></div>
                                                    <div style="font-size: 0.85rem; color: var(--gray-500);"><?= sanitize($u['nik']) ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?= sanitize($u['email']) ?></td>
                                        <td><span class="badge badge-<?= $role['color'] ?>"><?= $role['icon'] ?> <?= $role['label'] ?></span></td>
                                        <td>RT <?= $u['rt'] ?? '-' ?> / RW <?= $u['rw'] ?? '-' ?></td>
                                        <td><?= $u['no_rumah'] ?? '-' ?></td>
                                        <td>
                                            <?php if ($u['status'] === 'aktif'): ?>
                                                <span class="badge badge-success">Aktif</span>
                                            <?php elseif ($u['status'] === 'pindah'): ?>
                                                <span class="badge badge-warning">Pindah</span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary">Meninggal</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline" onclick="editUser(<?= $u['id'] ?>)" title="Edit">✏️</button>
                                            <button class="btn btn-sm btn-outline" onclick="changePassword(<?= $u['id'] ?>)" title="Ganti Password">🔑</button>
                                            <?php if ($u['id'] != $user['id']): ?>
                                            <button class="btn btn-sm btn-danger" onclick="deleteUser(<?= $u['id'] ?>)" title="Hapus">🗑️</button>
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

    <!-- Add User Modal -->
    <div class="modal-overlay" id="addUserModal">
        <div class="modal modal-lg">
            <div class="modal-header">
                <h3 class="modal-title">Tambah User</h3>
                <button class="modal-close" onclick="closeModal('addUserModal')">&times;</button>
            </div>
            <form id="addUserForm" onsubmit="handleAdd(event)">
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
                            <label class="form-label">Role *</label>
                            <select class="form-control" name="role" required>
                                <option value="">Pilih Role</option>
                                <option value="warga">Warga</option>
                                <option value="rt">Ketua RT</option>
                                <option value="rw">Ketua RW</option>
                                <option value="admin">Administrator</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Password *</label>
                            <input type="password" class="form-control" name="password" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">RT</label>
                            <input type="text" class="form-control" name="rt" maxlength="3">
                        </div>
                        <div class="form-group">
                            <label class="form-label">RW</label>
                            <input type="text" class="form-control" name="rw" maxlength="3">
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
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('addUserModal')">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div class="modal-overlay" id="editUserModal">
        <div class="modal modal-lg">
            <div class="modal-header">
                <h3 class="modal-title">Edit User</h3>
                <button class="modal-close" onclick="closeModal('editUserModal')">&times;</button>
            </div>
            <form id="editUserForm" onsubmit="handleEdit(event)">
                <input type="hidden" name="id" id="editId">
                <div class="modal-body">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div class="form-group">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control" name="nama" id="editNama">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" id="editEmail">
                        </div>
                        <div class="form-group">
                            <label class="form-label">No KK</label>
                            <input type="text" class="form-control" name="no_kk" id="editNoKk">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Role</label>
                            <select class="form-control" name="role" id="editRole">
                                <option value="warga">Warga</option>
                                <option value="rt">Ketua RT</option>
                                <option value="rw">Ketua RW</option>
                                <option value="admin">Administrator</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">RT</label>
                            <input type="text" class="form-control" name="rt" id="editRt">
                        </div>
                        <div class="form-group">
                            <label class="form-label">RW</label>
                            <input type="text" class="form-control" name="rw" id="editRw">
                        </div>
                        <div class="form-group">
                            <label class="form-label">No Rumah</label>
                            <input type="text" class="form-control" name="no_rumah" id="editNoRumah">
                        </div>
                        <div class="form-group">
                            <label class="form-label">WhatsApp</label>
                            <input type="text" class="form-control" name="whatsapp" id="editWhatsapp">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Pekerjaan</label>
                            <input type="text" class="form-control" name="pekerjaan" id="editPekerjaan">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Status</label>
                            <select class="form-control" name="status" id="editStatus">
                                <option value="aktif">Aktif</option>
                                <option value="pindah">Pindah</option>
                                <option value="meninggal">Meninggal</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('editUserModal')">Batal</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Change Password Modal -->
    <div class="modal-overlay" id="changePasswordModal">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title">Ganti Password</h3>
                <button class="modal-close" onclick="closeModal('changePasswordModal')">&times;</button>
            </div>
            <form id="changePasswordForm" onsubmit="handleChangePassword(event)">
                <input type="hidden" name="id" id="passwordId">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">User</label>
                        <input type="text" class="form-control" id="passwordUserName" readonly style="background: var(--gray-100);">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Password Baru *</label>
                        <input type="password" class="form-control" name="password" required minlength="6">
                        <small style="color: var(--gray-500);">Minimal 6 karakter</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('changePasswordModal')">Batal</button>
                    <button type="submit" class="btn btn-primary">Update Password</button>
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
                const response = await fetch('api/manage-users.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                const result = await response.json();

                if (result.success) {
                    alert('✅ User berhasil ditambahkan');
                    closeModal('addUserModal');
                    window.location.reload();
                } else {
                    alert('❌ Gagal menambahkan user: ' + result.message);
                }
            } catch (error) {
                alert('❌ Terjadi kesalahan');
                console.error(error);
            }
        }

        async function editUser(id) {
            try {
                const response = await fetch(`api/manage-users.php?id=${id}`);
                const result = await response.json();
                
                if (result.success) {
                    const u = result.data;
                    document.getElementById('editId').value = u.id;
                    document.getElementById('editNama').value = u.nama;
                    document.getElementById('editEmail').value = u.email;
                    document.getElementById('editNoKk').value = u.no_kk;
                    document.getElementById('editRole').value = u.role;
                    document.getElementById('editRt').value = u.rt || '';
                    document.getElementById('editRw').value = u.rw || '';
                    document.getElementById('editNoRumah').value = u.no_rumah || '';
                    document.getElementById('editWhatsapp').value = u.whatsapp || '';
                    document.getElementById('editPekerjaan').value = u.pekerjaan || '';
                    document.getElementById('editStatus').value = u.status;
                    openModal('editUserModal');
                } else {
                    alert('Gagal memuat data user');
                }
            } catch (error) {
                alert('Terjadi kesalahan');
                console.error(error);
            }
        }

        async function handleEdit(e) {
            e.preventDefault();
            const form = e.target;
            const formData = new FormData(form);
            const data = Object.fromEntries(formData);

            try {
                const response = await fetch('api/manage-users.php', {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                const result = await response.json();

                if (result.success) {
                    alert('✅ User berhasil diupdate');
                    closeModal('editUserModal');
                    window.location.reload();
                } else {
                    alert('❌ Gagal mengupdate user: ' + result.message);
                }
            } catch (error) {
                alert('❌ Terjadi kesalahan');
                console.error(error);
            }
        }

        async function changePassword(id) {
            try {
                const response = await fetch(`api/manage-users.php?id=${id}`);
                const result = await response.json();
                
                if (result.success) {
                    const u = result.data;
                    document.getElementById('passwordId').value = u.id;
                    document.getElementById('passwordUserName').value = u.nama + ' (' + u.email + ')';
                    openModal('changePasswordModal');
                } else {
                    alert('Gagal memuat data user');
                }
            } catch (error) {
                alert('Terjadi kesalahan');
                console.error(error);
            }
        }

        async function handleChangePassword(e) {
            e.preventDefault();
            const form = e.target;
            const formData = new FormData(form);
            const data = Object.fromEntries(formData);

            try {
                const response = await fetch('api/manage-users.php', {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ ...data, action: 'change_password' })
                });
                const result = await response.json();

                if (result.success) {
                    alert('✅ Password berhasil diubah');
                    closeModal('changePasswordModal');
                    window.location.reload();
                } else {
                    alert('❌ Gagal mengubah password: ' + result.message);
                }
            } catch (error) {
                alert('❌ Terjadi kesalahan');
                console.error(error);
            }
        }

        async function deleteUser(id) {
            if (confirm('⚠️ Yakin ingin menghapus user ini? Tindakan ini tidak dapat dibatalkan.')) {
                try {
                    const response = await fetch('api/manage-users.php', {
                        method: 'DELETE',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id })
                    });
                    const result = await response.json();

                    if (result.success) {
                        alert('✅ User berhasil dihapus');
                        window.location.reload();
                    } else {
                        alert('❌ Gagal menghapus user: ' + result.message);
                    }
                } catch (error) {
                    alert('❌ Terjadi kesalahan');
                    console.error(error);
                }
            }
        }
    </script>
</body>
</html>
