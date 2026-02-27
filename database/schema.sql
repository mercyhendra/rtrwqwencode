-- Database schema untuk VILLA BINTARO REGENCY RT/RW Digital
-- Database: wargavbr
-- User: root (tanpa password)

-- Buat database
CREATE DATABASE IF NOT EXISTS wargavbr CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE wargavbr;

-- Tabel users (warga, admin, rt, rw)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    uuid VARCHAR(36) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'rt', 'rw', 'warga') DEFAULT 'warga',
    nama VARCHAR(255) NOT NULL,
    nik VARCHAR(16) UNIQUE NOT NULL,
    no_kk VARCHAR(16) NOT NULL,
    rt VARCHAR(3),
    rw VARCHAR(3),
    no_rumah VARCHAR(20),
    whatsapp VARCHAR(20),
    pekerjaan VARCHAR(100),
    status ENUM('aktif', 'pindah', 'meninggal') DEFAULT 'aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_nik (nik),
    INDEX idx_no_kk (no_kk),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel anggota keluarga (untuk 1 KK dengan multiple anggota)
CREATE TABLE IF NOT EXISTS anggota_keluarga (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    nama VARCHAR(255) NOT NULL,
    nik VARCHAR(16) UNIQUE,
    hubungan ENUM('kepala_keluarga', 'istri', 'suami', 'anak', 'lainnya') DEFAULT 'lainnya',
    jenis_kelamin ENUM('L', 'P') NOT NULL,
    tempat_lahir VARCHAR(100),
    tanggal_lahir DATE,
    pekerjaan VARCHAR(100),
    status_perkawinan ENUM('belum_kawin', 'kawin', 'cerai_hidup', 'cerai_mati'),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel pengumuman
CREATE TABLE IF NOT EXISTS pengumuman (
    id INT AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(255) NOT NULL,
    isi TEXT NOT NULL,
    kategori ENUM('umum', 'penting', 'warga', 'darurat') DEFAULT 'umum',
    tanggal_acara DATETIME,
    lokasi_acara VARCHAR(255),
    dibuat_oleh INT NOT NULL,
    views INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (dibuat_oleh) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_kategori (kategori)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel iuran
CREATE TABLE IF NOT EXISTS iuran (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    jenis_iuran ENUM('kebersihan', 'keamanan', 'sampah', 'lainnya') NOT NULL,
    nominal DECIMAL(10,2) NOT NULL,
    bulan VARCHAR(7) NOT NULL, -- format: YYYY-MM
    tanggal_bayar DATETIME,
    status ENUM('belum_bayar', 'lunas') DEFAULT 'belum_bayar',
    keterangan TEXT,
    dibuat_oleh INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (dibuat_oleh) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_bulan (bulan),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel layanan surat
CREATE TABLE IF NOT EXISTS layanan_surat (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    jenis_surat ENUM('domisili', 'ktp', 'kelahiran', 'kematian', 'nikah', 'usaha') NOT NULL,
    keperluan TEXT NOT NULL,
    status ENUM('pending', 'proses', 'selesai', 'ditolak') DEFAULT 'pending',
    no_surat VARCHAR(50),
    catatan_admin TEXT,
    dibuat_oleh INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (dibuat_oleh) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel kegiatan
CREATE TABLE IF NOT EXISTS kegiatan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_kegiatan VARCHAR(255) NOT NULL,
    kategori ENUM('umum', 'kesehatan', 'sosial', 'olahraga', 'keagamaan', 'pengurus') DEFAULT 'umum',
    tanggal_kegiatan DATETIME NOT NULL,
    lokasi VARCHAR(255),
    deskripsi TEXT,
    kuota_peserta INT,
    jumlah_peserta INT DEFAULT 0,
    status ENUM('aktif', 'selesai', 'batal') DEFAULT 'aktif',
    dibuat_oleh INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (dibuat_oleh) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_kategori (kategori),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel notifikasi
CREATE TABLE IF NOT EXISTS notifikasi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    judul VARCHAR(255) NOT NULL,
    isi TEXT NOT NULL,
    tipe ENUM('info', 'warning', 'success', 'danger') DEFAULT 'info',
    sudah_dibaca BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_sudah_dibaca (sudah_dibaca)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default users
-- Password default: admin123, rt123, warga123 (hash bcrypt)
INSERT INTO users (uuid, email, password, role, nama, nik, no_kk, rt, rw, no_rumah, whatsapp, pekerjaan, status) VALUES
(UUID(), 'admin@rtrw.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'Administrator', '3171012345678999', '3171012345678999', '001', '001', 'A-01', '081234567890', 'Administrator Sistem', 'aktif'),
(UUID(), 'rt@rtrw.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'rt', 'Ketua RT 001', '3171012345678998', '3171012345678998', '001', '001', 'A-02', '081234567891', 'Ketua RT', 'aktif'),
(UUID(), 'warga@rtrw.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'warga', 'Ahmad Santoso', '3171012345678901', '3171012345678001', '001', '001', 'A-12', '081234567890', 'Wiraswasta', 'aktif');
