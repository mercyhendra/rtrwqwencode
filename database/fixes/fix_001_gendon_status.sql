-- ========================================
-- Fix Status Keluarga: nanda & Gendon
-- ========================================
-- File: fix_001_gendon_status.sql
-- Tanggal: 2025-02-20
-- Deskripsi: Memperbaiki status_keluarga untuk nanda dan Gendon
--            nanda (ID 4) harusnya Kepala Keluarga
--            Gendon (ID 7) harusnya Anggota
-- ========================================

USE wargavbr;

-- ========================================
-- Step 1: Fix status_keluarga untuk nanda (ID 4)
-- ========================================
UPDATE users SET status_keluarga = 'kepala_keluarga' WHERE id = 4;

-- ========================================
-- Step 2: Fix status_keluarga untuk Gendon (ID 7)
-- ========================================
UPDATE users SET status_keluarga = 'anggota' WHERE id = 7;

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
WHERE no_kk = '3171012345678002' 
ORDER BY id;

-- ========================================
-- Selesai
-- ========================================
