-- ========================================
-- Update Database: Status Keluarga
-- ========================================
-- File: update_001_status_keluarga.sql
-- Tanggal: 2025-02-20
-- Deskripsi: Menambahkan kolom status_keluarga untuk membedakan
--            Kepala Keluarga dan Anggota Keluarga
-- ========================================

USE wargavbr;

-- ========================================
-- Step 1: Tambah kolom status_keluarga (jika belum ada)
-- ========================================
-- Catatan: Jika error "Duplicate column name", hapus step ini dan lanjut step 2

-- ========================================
-- Step 2: Reset semua warga ke 'anggota'
-- ========================================
UPDATE users SET status_keluarga = 'anggota' WHERE role = 'warga';

-- ========================================
-- Step 3: Set kepala keluarga untuk user pertama di setiap KK
-- ========================================
UPDATE users u1
JOIN (
    SELECT no_kk, MIN(id) as min_id
    FROM users
    WHERE role = 'warga'
    GROUP BY no_kk
) u2 ON u1.no_kk = u2.no_kk AND u1.id = u2.min_id
SET u1.status_keluarga = 'kepala_keluarga';

-- ========================================
-- Step 4: Verify hasil update
-- ========================================
SELECT 
    id, 
    nama, 
    no_kk, 
    status_keluarga,
    created_at 
FROM users 
WHERE role = 'warga' 
ORDER BY no_kk, id;

-- ========================================
-- Selesai
-- ========================================
