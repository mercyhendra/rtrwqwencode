<?php
/**
 * Fix & Insert Sample Data Lengkap
 * Akses: https://vbr.fakechefnats.site/fix_sample_data.php
 */

require_once __DIR__ . '/api/config.php';

echo "<h2>🔧 Fix & Insert Sample Data Lengkap</h2>";
echo "<pre>";

try {
    $pdo = getDbConnection();
    echo "✓ Database connected\n\n";
    
    // Check users
    echo "=== CHECKING USERS ===\n";
    $stmt = $pdo->query("SELECT id, nama, email, role FROM users ORDER BY id");
    $users = $stmt->fetchAll();
    if (empty($users)) {
        echo "⚠ No users found! Inserting default users...\n";
        
        $password = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("
            INSERT INTO users (uuid, email, password, role, nama, nik, no_kk, rt, rw, no_rumah, whatsapp, pekerjaan, status) 
            VALUES 
            (UUID(), 'admin@rtrw.com', ?, 'admin', 'Administrator', '3171012345678999', '3171012345678999', '001', '001', 'A-01', '081234567890', 'Administrator Sistem', 'aktif'),
            (UUID(), 'rt@rtrw.com', ?, 'rt', 'Ketua RT 001', '3171012345678998', '3171012345678998', '001', '001', 'A-02', '081234567891', 'Ketua RT', 'aktif'),
            (UUID(), 'warga@rtrw.com', ?, 'warga', 'Ahmad Santoso', '3171012345678901', '3171012345678001', '001', '001', 'A-12', '081234567890', 'Wiraswasta', 'aktif')
        ");
        $stmt->execute([$password, $password, $password]);
        echo "✓ Users inserted\n";
        
        $stmt = $pdo->query("SELECT id, nama, email, role FROM users ORDER BY id");
        $users = $stmt->fetchAll();
    }
    
    foreach ($users as $u) {
        echo "  [{$u['id']}] {$u['nama']} ({$u['email']}) - {$u['role']}\n";
    }
    
    // Get user IDs
    $adminId = 1;
    $rtId = 2;
    $wargaId = 3;
    
    echo "\n=== INSERTING PENGUMUMAN ===\n";
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM pengumuman");
    if ($stmt->fetch()['count'] == 0) {
        $stmt = $pdo->prepare("
            INSERT INTO pengumuman (judul, kategori, isi, tanggal_acara, lokasi_acara, dibuat_oleh, created_at, views) VALUES
            ('Kerja Bakti Minggu Ini', 'umum', 'Diberitahukan kepada seluruh warga RT 001/RW 001 untuk mengikuti kerja bakti membersihkan selokan dan lingkungan sekitar.\n\n📅 Hari: Minggu, 18 Februari 2025\n⏰ Waktu: 07:00 WIB\n📍 Lokasi: Balai Warga', '2025-02-18 07:00:00', 'Balai Warga', ?, '2025-02-15 10:30:00', 125),
            ('Pembayaran Iuran Bulan Ini', 'penting', 'Diharapkan kepada seluruh warga untuk membayar iuran kebersihan dan keamanan bulan Februari 2025.\n\n💵 Jumlah: Rp 50.000\n📅 Batas: 25 Februari 2025\n📍 Pembayaran: Bendahara RT', '2025-02-25 23:59:59', 'Bendahara RT', ?, '2025-02-14 14:00:00', 98),
            ('Rapat RT Bulanan', 'warga', 'Undangan rapat bulanan untuk seluruh pengurus RT dan perwakilan warga.\n\n📅 Hari: Sabtu, 17 Februari 2025\n⏰ Waktu: 19:00 WIB\n📍 Lokasi: Rumah Pak RT', '2025-02-17 19:00:00', 'Rumah Pak RT', ?, '2025-02-12 09:00:00', 76),
            ('Waspada Demam Berdarah', 'darurat', 'Diberitahukan agar seluruh warga meningkatkan kewaspadaan terhadap penyakit demam berdarah.\n\n🔸 Lakukan 3M Plus\n🔸 Gunakan kelambu\n🔸 Segera ke puskesmas jika ada gejala', NULL, NULL, ?, '2025-02-10 16:45:00', 200)
        ");
        $stmt->execute([$adminId, $adminId, $adminId, $adminId]);
        echo "✓ Pengumuman inserted\n";
    } else {
        echo "✓ Pengumuman already exists\n";
    }
    
    echo "\n=== INSERTING KEGIATAN ===\n";
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM kegiatan");
    if ($stmt->fetch()['count'] == 0) {
        $stmt = $pdo->prepare("
            INSERT INTO kegiatan (nama_kegiatan, kategori, tanggal_kegiatan, lokasi, deskripsi, kuota_peserta, jumlah_peserta, status, dibuat_oleh, created_at) VALUES
            ('Senam Pagi Bersama', 'kesehatan', '2025-02-22 06:00:00', 'Lapangan RT 001', 'Senam pagi untuk seluruh warga setiap minggu pagi. Gratis dan terbuka untuk semua usia.', 100, 45, 'aktif', ?, '2025-02-10 08:00:00'),
            ('Posyandu Balita', 'kesehatan', '2025-02-25 09:00:00', 'Posyandu Melati', 'Pemeriksaan kesehatan rutin untuk balita. Termasuk imunisasi dan pemberian vitamin.', 50, 32, 'aktif', ?, '2025-02-08 10:00:00'),
            ('Karang Taruna Futsal', 'olahraga', '2025-02-23 16:00:00', 'Lapangan Futsal Merdeka', 'Latihan futsal rutin karang taruna. Diharapkan seluruh anggota hadir.', 20, 18, 'aktif', ?, '2025-02-05 14:00:00'),
            ('Pengajian Rutin Ibu-Ibu', 'keagamaan', '2025-02-24 10:00:00', 'Masjid Al-Ikhlas', 'Pengajian rutin ibu-ibu setiap minggu. Pembicara: Ustadzah Siti Aminah.', 100, 75, 'aktif', ?, '2025-02-03 09:00:00'),
            ('Kerja Bakti Massal', 'sosial', '2025-02-18 07:00:00', 'Balai Warga', 'Kerja bakti membersihkan selokan dan lingkungan sekitar RT 001.', 200, 120, 'selesai', ?, '2025-02-01 08:00:00'),
            ('Lomba 17 Agustus', 'umum', '2025-08-17 08:00:00', 'Lapangan RT 001', 'Lomba-lomba memeriahkan HUT RI ke-80. Berbagai lomba untuk anak-anak dan dewasa.', 500, 0, 'aktif', ?, '2025-01-15 10:00:00')
        ");
        $stmt->execute([$adminId, $adminId, $rtId, $adminId, $adminId, $adminId]);
        echo "✓ Kegiatan inserted\n";
    } else {
        echo "✓ Kegiatan already exists\n";
    }
    
    echo "\n=== INSERTING LAYANAN SURAT ===\n";
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM layanan_surat");
    if ($stmt->fetch()['count'] == 0) {
        $stmt = $pdo->prepare("
            INSERT INTO layanan_surat (user_id, jenis_surat, keperluan, status, no_surat, catatan_admin, dibuat_oleh, created_at) VALUES
            (?, 'domisili', 'Untuk keperluan pembuatan KTP', 'selesai', 'DOM-001/RT001/II/2025', 'Surat sudah siap diambil', ?, '2025-02-10 09:00:00'),
            (?, 'ktp', 'KTP hilang', 'proses', 'KTP-002/RT001/II/2025', 'Sedang diproses di kelurahan', ?, '2025-02-12 10:00:00'),
            (?, 'usaha', 'Untuk izin usaha UMKM', 'pending', NULL, NULL, ?, '2025-02-14 11:00:00'),
            (?, 'kelahiran', 'Melaporkan kelahiran bayi', 'selesai', 'LAH-003/RT001/II/2025', 'Sudah selesai', ?, '2025-02-08 08:00:00'),
            (?, 'nikah', 'Untuk keperluan administrasi pernikahan', 'proses', 'NIK-004/RT001/II/2025', 'Menunggu dokumen tambahan', ?, '2025-02-11 14:00:00')
        ");
        $stmt->execute([
            $wargaId, $adminId,
            $wargaId, $adminId,
            $wargaId, $adminId,
            $wargaId, $adminId,
            $wargaId, $rtId
        ]);
        echo "✓ Layanan surat inserted\n";
    } else {
        echo "✓ Layanan surat already exists\n";
    }
    
    echo "\n=== INSERTING IURAN ===\n";
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM iuran");
    if ($stmt->fetch()['count'] == 0) {
        $stmt = $pdo->prepare("
            INSERT INTO iuran (user_id, jenis_iuran, nominal, bulan, tanggal_bayar, status, keterangan, dibuat_oleh, created_at) VALUES
            (?, 'kebersihan', 50000, '2025-02', '2025-02-15 10:00:00', 'lunas', 'Iuran kebersihan Februari 2025', ?, '2025-02-15 10:00:00'),
            (?, 'keamanan', 30000, '2025-02', NULL, 'belum_bayar', 'Iuran keamanan Februari 2025', ?, '2025-02-01 08:00:00'),
            (?, 'sampah', 20000, '2025-02', '2025-02-10 09:00:00', 'lunas', 'Iuran sampah Februari 2025', ?, '2025-02-10 09:00:00'),
            (?, 'kebersihan', 50000, '2025-01', '2025-01-15 10:00:00', 'lunas', 'Iuran kebersihan Januari 2025', ?, '2025-01-15 10:00:00'),
            (?, 'keamanan', 30000, '2025-01', '2025-01-10 08:00:00', 'lunas', 'Iuran keamanan Januari 2025', ?, '2025-01-10 08:00:00'),
            (?, 'sampah', 20000, '2025-01', '2025-01-10 09:00:00', 'lunas', 'Iuran sampah Januari 2025', ?, '2025-01-10 09:00:00')
        ");
        $stmt->execute([
            $wargaId, $adminId,
            $wargaId, $adminId,
            $wargaId, $adminId,
            $wargaId, $adminId,
            $wargaId, $adminId,
            $wargaId, $adminId
        ]);
        echo "✓ Iuran inserted\n";
    } else {
        echo "✓ Iuran already exists\n";
    }
    
    echo "\n=== INSERTING NOTIFIKASI ===\n";
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM notifikasi");
    if ($stmt->fetch()['count'] == 0) {
        $stmt = $pdo->prepare("
            INSERT INTO notifikasi (user_id, judul, isi, tipe, sudah_dibaca, created_at) VALUES
            (?, 'Pengumuman Baru: Kerja Bakti', 'Ada pengumuman baru tentang kerja bakti minggu ini', 'info', 0, '2025-02-15 10:30:00'),
            (?, 'Iuran Bulan Ini', 'Iuran kebersihan dan keamanan bulan Februari sudah dapat dibayarkan', 'warning', 0, '2025-02-14 14:00:00'),
            (?, 'Surat Domisili Siap', 'Surat domisili Anda sudah siap untuk diambil', 'success', 1, '2025-02-11 09:00:00'),
            (?, 'Rapat RT Bulanan', 'Undangan rapat RT bulanan untuk seluruh pengurus', 'info', 1, '2025-02-12 09:00:00'),
            (?, 'Peringatan Iuran', 'Iuran keamanan Anda belum dibayar untuk bulan Februari', 'danger', 0, '2025-02-10 08:00:00')
        ");
        $stmt->execute([$wargaId, $wargaId, $wargaId, $wargaId, $wargaId]);
        echo "✓ Notifikasi inserted\n";
    } else {
        echo "✓ Notifikasi already exists\n";
    }
    
    echo "\n=== INSERTING ANGGOTA_KELUARGA_REQUEST ===\n";
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM anggota_keluarga_request");
    if ($stmt->fetch()['count'] == 0) {
        $stmt = $pdo->prepare("
            INSERT INTO anggota_keluarga_request (user_id, nama, nik, hubungan, jenis_kelamin, tempat_lahir, tanggal_lahir, pekerjaan, status_perkawinan, status, catatan_admin, created_at) VALUES
            (?, 'Budi Santoso', '3171012345678902', 'anak', 'L', 'Jakarta', '2010-05-15', 'Pelajar', NULL, 'pending', NULL, '2025-02-14 10:00:00'),
            (?, 'Siti Aminah', '3171012345678903', 'istri', 'P', 'Bandung', '1985-08-20', 'Ibu Rumah Tangga', 'kawin', 'approved', 'Data sudah ditambahkan', ?, '2025-02-10 09:00:00'),
            (?, 'Ahmad Fauzi', '3171012345678904', 'lainnya', 'L', 'Surabaya', '1990-03-10', 'Karyawan Swasta', 'belum_kawin', 'rejected', 'NIK sudah terdaftar', ?, '2025-02-08 14:00:00')
        ");
        $stmt->execute([$wargaId, $wargaId, $adminId, $wargaId, $adminId]);
        echo "✓ Anggota keluarga request inserted\n";
    } else {
        echo "✓ Anggota keluarga request already exists\n";
    }
    
    echo "\n=================================\n";
    echo "SUMMARY DATA COUNT:\n";
    echo "=================================\n";
    
    $tables = [
        'users' => 'Users',
        'pengumuman' => 'Pengumuman',
        'kegiatan' => 'Kegiatan',
        'layanan_surat' => 'Layanan Surat',
        'iuran' => 'Iuran',
        'notifikasi' => 'Notifikasi',
        'anggota_keluarga_request' => 'Request Anggota'
    ];
    
    foreach ($tables as $table => $label) {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM $table");
        $row = $stmt->fetch();
        echo "✓ $label: {$row['count']} data\n";
    }
    
    echo "\n=================================\n";
    echo "✅ Sample data successfully inserted!\n";
    echo "=================================\n\n";
    
    echo "<a href='dashboard.php'>📊 Dashboard</a> | ";
    echo "<a href='pengumuman.php'>📢 Pengumuman</a> | ";
    echo "<a href='kegiatan.php'>📅 Kegiatan</a> | ";
    echo "<a href='layanan.php'>📋 Layanan Surat</a> | ";
    echo "<a href='iuran.php'>💰 Iuran</a> | ";
    echo "<a href='notifikasi.php'>🔔 Notifikasi</a>";
    
} catch (PDOException $e) {
    echo "✗ Error: " . $e->getMessage();
}

echo "</pre>";
?>
