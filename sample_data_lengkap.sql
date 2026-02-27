-- Sample Data Lengkap untuk VILLA BINTARO REGENCY
-- Database: wargavbr
-- Akses: https://vbr.fakechefnats.site/insert_sample_data.php

-- --------------------------------------------------------
-- 1. SAMPLE DATA PENGUMUMAN
-- --------------------------------------------------------
INSERT INTO pengumuman (judul, kategori, isi, tanggal_acara, lokasi_acara, dibuat_oleh, created_at, views) VALUES
('Kerja Bakti Minggu Ini', 'umum', 'Diberitahukan kepada seluruh warga RT 001/RW 001 untuk mengikuti kerja bakti membersihkan selokan dan lingkungan sekitar.\n\n📅 Hari: Minggu, 18 Februari 2025\n⏰ Waktu: 07:00 WIB\n📍 Lokasi: Balai Warga', '2025-02-18 07:00:00', 'Balai Warga', 1, '2025-02-15 10:30:00', 125),
('Pembayaran Iuran Bulan Ini', 'penting', 'Diharapkan kepada seluruh warga untuk membayar iuran kebersihan dan keamanan bulan Februari 2025.\n\n💵 Jumlah: Rp 50.000\n📅 Batas: 25 Februari 2025\n📍 Pembayaran: Bendahara RT', '2025-02-25 23:59:59', 'Bendahara RT', 1, '2025-02-14 14:00:00', 98),
('Rapat RT Bulanan', 'warga', 'Undangan rapat bulanan untuk seluruh pengurus RT dan perwakilan warga.\n\n📅 Hari: Sabtu, 17 Februari 2025\n⏰ Waktu: 19:00 WIB\n📍 Lokasi: Rumah Pak RT', '2025-02-17 19:00:00', 'Rumah Pak RT', 1, '2025-02-12 09:00:00', 76),
('Waspada Demam Berdarah', 'darurat', 'Diberitahukan agar seluruh warga meningkatkan kewaspadaan terhadap penyakit demam berdarah.\n\n🔸 Lakukan 3M Plus\n🔸 Gunakan kelambu\n🔸 Segera ke puskesmas jika ada gejala', NULL, NULL, 1, '2025-02-10 16:45:00', 200);

-- --------------------------------------------------------
-- 2. SAMPLE DATA KEGIATAN
-- --------------------------------------------------------
INSERT INTO kegiatan (nama_kegiatan, kategori, tanggal_kegiatan, lokasi, deskripsi, kuota_peserta, jumlah_peserta, status, dibuat_oleh, created_at) VALUES
('Senam Pagi Bersama', 'kesehatan', '2025-02-22 06:00:00', 'Lapangan RT 001', 'Senam pagi untuk seluruh warga setiap minggu pagi. Gratis dan terbuka untuk semua usia.', 100, 45, 'aktif', 1, '2025-02-10 08:00:00'),
('Posyandu Balita', 'kesehatan', '2025-02-25 09:00:00', 'Posyandu Melati', 'Pemeriksaan kesehatan rutin untuk balita. Termasuk imunisasi dan pemberian vitamin.', 50, 32, 'aktif', 1, '2025-02-08 10:00:00'),
('Karang Taruna Futsal', 'olahraga', '2025-02-23 16:00:00', 'Lapangan Futsal Merdeka', 'Latihan futsal rutin karang taruna. Diharapkan seluruh anggota hadir.', 20, 18, 'aktif', 2, '2025-02-05 14:00:00'),
('Pengajian Rutin Ibu-Ibu', 'keagamaan', '2025-02-24 10:00:00', 'Masjid Al-Ikhlas', 'Pengajian rutin ibu-ibu setiap minggu. Pembicara: Ustadzah Siti Aminah.', 100, 75, 'aktif', 1, '2025-02-03 09:00:00'),
('Kerja Bakti Massal', 'sosial', '2025-02-18 07:00:00', 'Balai Warga', 'Kerja bakti membersihkan selokan dan lingkungan sekitar RT 001.', 200, 120, 'selesai', 1, '2025-02-01 08:00:00'),
('Lomba 17 Agustus', 'umum', '2025-08-17 08:00:00', 'Lapangan RT 001', 'Lomba-lomba memeriahkan HUT RI ke-80. Berbagai lomba untuk anak-anak dan dewasa.', 500, 0, 'aktif', 1, '2025-01-15 10:00:00');

-- --------------------------------------------------------
-- 3. SAMPLE DATA LAYANAN SURAT
-- --------------------------------------------------------
INSERT INTO layanan_surat (user_id, jenis_surat, keperluan, status, no_surat, catatan_admin, dibuat_oleh, created_at) VALUES
(3, 'domisili', 'Untuk keperluan pembuatan KTP', 'selesai', 'DOM-001/RT001/II/2025', 'Surat sudah siap diambil', 1, '2025-02-10 09:00:00'),
(3, 'ktp', 'KTP hilang', 'proses', 'KT P-002/RT001/II/2025', 'Sedang diproses di kelurahan', 1, '2025-02-12 10:00:00'),
(3, 'usaha', 'Untuk izin usaha UMKM', 'pending', NULL, NULL, 1, '2025-02-14 11:00:00'),
(3, 'kelahiran', 'Melaporkan kelahiran bayi', 'selesai', 'LAH-003/RT001/II/2025', 'Sudah selesai', 1, '2025-02-08 08:00:00'),
(3, 'nikah', 'Untuk keperluan administrasi pernikahan', 'proses', 'NIK-004/RT001/II/2025', 'Menunggu dokumen tambahan', 2, '2025-02-11 14:00:00');

-- --------------------------------------------------------
-- 4. SAMPLE DATA IURAN
-- --------------------------------------------------------
INSERT INTO iuran (user_id, jenis_iuran, nominal, bulan, tanggal_bayar, status, keterangan, dibuat_oleh, created_at) VALUES
(3, 'kebersihan', 50000, '2025-02', '2025-02-15 10:00:00', 'lunas', 'Iuran kebersihan Februari 2025', 1, '2025-02-15 10:00:00'),
(3, 'keamanan', 30000, '2025-02', NULL, 'belum_bayar', 'Iuran keamanan Februari 2025', 1, '2025-02-01 08:00:00'),
(3, 'sampah', 20000, '2025-02', '2025-02-10 09:00:00', 'lunas', 'Iuran sampah Februari 2025', 1, '2025-02-10 09:00:00'),
(3, 'kebersihan', 50000, '2025-01', '2025-01-15 10:00:00', 'lunas', 'Iuran kebersihan Januari 2025', 1, '2025-01-15 10:00:00'),
(3, 'keamanan', 30000, '2025-01', '2025-01-10 08:00:00', 'lunas', 'Iuran keamanan Januari 2025', 1, '2025-01-10 08:00:00'),
(3, 'sampah', 20000, '2025-01', '2025-01-10 09:00:00', 'lunas', 'Iuran sampah Januari 2025', 1, '2025-01-10 09:00:00');

-- --------------------------------------------------------
-- 5. SAMPLE DATA NOTIFIKASI
-- --------------------------------------------------------
INSERT INTO notifikasi (user_id, judul, isi, tipe, sudah_dibaca, created_at) VALUES
(3, 'Pengumuman Baru: Kerja Bakti', 'Ada pengumuman baru tentang kerja bakti minggu ini', 'info', 0, '2025-02-15 10:30:00'),
(3, 'Iuran Bulan Ini', 'Iuran kebersihan dan keamanan bulan Februari sudah dapat dibayarkan', 'warning', 0, '2025-02-14 14:00:00'),
(3, 'Surat Domisili Siap', 'Surat domisili Anda sudah siap untuk diambil', 'success', 1, '2025-02-11 09:00:00'),
(3, 'Rapat RT Bulanan', 'Undangan rapat RT bulanan untuk seluruh pengurus', 'info', 1, '2025-02-12 09:00:00'),
(3, 'Peringatan Iuran', 'Iuran keamanan Anda belum dibayar untuk bulan Februari', 'danger', 0, '2025-02-10 08:00:00');

-- --------------------------------------------------------
-- 6. SAMPLE DATA ANGGOTA KELUARGA REQUEST
-- --------------------------------------------------------
INSERT INTO anggota_keluarga_request (user_id, nama, nik, hubungan, jenis_kelamin, tempat_lahir, tanggal_lahir, pekerjaan, status_perkawinan, status, catatan_admin, created_at) VALUES
(3, 'Budi Santoso', '3171012345678902', 'anak', 'L', 'Jakarta', '2010-05-15', 'Pelajar', NULL, 'pending', NULL, '2025-02-14 10:00:00'),
(3, 'Siti Aminah', '3171012345678903', 'istri', 'P', 'Bandung', '1985-08-20', 'Ibu Rumah Tangga', 'kawin', 'approved', 'Data sudah ditambahkan', 1, '2025-02-10 09:00:00'),
(3, 'Ahmad Fauzi', '3171012345678904', 'lainnya', 'L', 'Surabaya', '1990-03-10', 'Karyawan Swasta', 'belum_kawin', 'rejected', 'NIK sudah terdaftar', 1, '2025-02-08 14:00:00');

-- --------------------------------------------------------
-- CATATAN:
-- - User ID 1 = Admin (admin@rtrw.com)
-- - User ID 2 = RT (rt@rtrw.com)  
-- - User ID 3 = Warga (warga@rtrw.com)
-- - Password default: admin123, rt123, warga123
-- --------------------------------------------------------
