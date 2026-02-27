-- Tabel untuk galeri foto kegiatan
CREATE TABLE IF NOT EXISTS kegiatan_galeri (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kegiatan_id INT NOT NULL,
    nama_file VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size INT DEFAULT 0,
    mime_type VARCHAR(100),
    keterangan TEXT,
    diupload_oleh INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (kegiatan_id) REFERENCES kegiatan(id) ON DELETE CASCADE,
    FOREIGN KEY (diupload_oleh) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_kegiatan_id (kegiatan_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
