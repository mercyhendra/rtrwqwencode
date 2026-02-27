-- Tabel untuk request penambahan anggota keluarga
CREATE TABLE IF NOT EXISTS anggota_keluarga_request (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    no_kk VARCHAR(16) NOT NULL,
    nama_lengkap VARCHAR(255) NOT NULL,
    nik VARCHAR(16),
    hubungan ENUM('istri', 'suami', 'anak', 'orang_tua', 'saudara', 'lainnya') NOT NULL,
    jenis_kelamin ENUM('L', 'P') NOT NULL,
    tempat_lahir VARCHAR(100),
    tanggal_lahir DATE,
    pekerjaan VARCHAR(100),
    status_perkawinan ENUM('belum_kawin', 'kawin', 'cerai_hidup', 'cerai_mati'),
    alasan TEXT,
    dokumen_pendukung VARCHAR(255),
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    catatan_admin TEXT,
    dibuat_oleh INT,
    disetujui_oleh INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (dibuat_oleh) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (disetujui_oleh) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_no_kk (no_kk),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample requests
INSERT INTO anggota_keluarga_request (user_id, no_kk, nama_lengkap, nik, hubungan, jenis_kelamin, tempat_lahir, tanggal_lahir, pekerjaan, status, created_at) VALUES
(3, '3171012345678001', 'Siti Nurhaliza', '3171012345678902', 'istri', 'P', 'Jakarta', '1990-05-15', 'Ibu Rumah Tangga', 'pending', NOW()),
(3, '3171012345678001', 'Fajar Santoso', '3171012345678903', 'anak', 'L', 'Jakarta', '2015-08-20', 'Pelajar', 'approved', DATE_SUB(NOW(), INTERVAL 7 DAY));
