-- Sample Data Pengumuman
-- Database: wargavbr
-- Note: Schema menggunakan field: tanggal_acara, lokasi_acara, dibuat_oleh

-- --------------------------------------------------------
-- Dumping data untuk tabel `pengumuman`
-- --------------------------------------------------------

INSERT INTO pengumuman (id, judul, kategori, isi, tanggal_acara, lokasi_acara, dibuat_oleh, created_at, views) VALUES
(1, 
'Kerja Bakti Minggu Ini', 
'umum', 
'Diberitahukan kepada seluruh warga RT 001/RW 001 untuk mengikuti kerja bakti membersihkan selokan dan lingkungan sekitar.\n\n📅 Hari: Minggu, 18 Februari 2025\n⏰ Waktu: 07:00 WIB\n📍 Lokasi: Balai Warga', 
'2025-02-18 07:00:00', 
'Balai Warga', 
1, 
'2025-02-15 10:30:00', 
125),

(2, 
'Pembayaran Iuran Bulan Ini', 
'penting', 
'Diharapkan kepada seluruh warga untuk membayar iuran kebersihan dan keamanan bulan Februari 2025.\n\n💵 Jumlah: Rp 50.000\n📅 Batas: 25 Februari 2025\n📍 Pembayaran: Bendahara RT', 
'2025-02-25 23:59:59', 
'Bendahara RT', 
1, 
'2025-02-14 14:00:00', 
98),

(3, 
'Rapat RT Bulanan', 
'warga', 
'Undangan rapat bulanan untuk seluruh pengurus RT dan perwakilan warga.\n\n📅 Hari: Sabtu, 17 Februari 2025\n⏰ Waktu: 19:00 WIB\n📍 Lokasi: Rumah Pak RT', 
'2025-02-17 19:00:00', 
'Rumah Pak RT', 
1, 
'2025-02-12 09:00:00', 
76),

(4, 
'Waspada Demam Berdarah', 
'darurat', 
'Diberitahukan agar seluruh warga meningkatkan kewaspadaan terhadap penyakit demam berdarah.\n\n🔸 Lakukan 3M Plus\n🔸 Gunakan kelambu\n🔸 Segera ke puskesmas jika ada gejala', 
NULL, 
NULL, 
1, 
'2025-02-10 16:45:00', 
200);

-- --------------------------------------------------------
-- Catatan:
-- - id: Auto increment
-- - kategori: umum, penting, warga, darurat
-- - tanggal_acara: Tanggal acara (opsional, bisa NULL)
-- - lokasi_acara: Lokasi acara (opsional, bisa NULL)
-- - dibuat_oleh: ID user yang memposting (foreign key ke users.id)
-- - views: Jumlah kali pengumuman dilihat
-- - User ID 1 = Admin (admin@rtrw.com)
-- --------------------------------------------------------
