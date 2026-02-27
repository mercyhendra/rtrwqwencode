-- ========================================
-- Fix All Status Keluarga
-- ========================================
-- File: fix_002_all_status_keluarga.sql
-- Tanggal: 2025-02-20
-- Deskripsi: Memperbaiki status_keluarga untuk semua KK
--            User pertama (ID terkecil) di setiap KK = Kepala Keluarga
--            Sisanya = Anggota
-- ========================================

USE wargavbr;

-- ========================================
-- Step 1: Reset semua warga ke 'anggota'
-- ========================================
UPDATE users SET status_keluarga = 'anggota' WHERE role = 'warga';

-- ========================================
-- Step 2: Set kepala keluarga untuk user pertama (ID terkecil) di setiap KK
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
-- Step 3: Verify hasil fix
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
