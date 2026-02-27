<?php
/**
 * Comprehensive Error Check
 * Akses: https://vbr.fakechefnats.site/check_all_errors.php
 */

require_once __DIR__ . '/api/config.php';

echo "<h2>🔍 Comprehensive Error Check</h2>";
echo "<pre>";

$errors = [];
$warnings = [];
$success = [];

// 1. Check Database Connection
echo "=== CHECKING DATABASE CONNECTION ===\n";
try {
    $pdo = getDbConnection();
    echo "✓ Database connected\n";
    $success[] = "Database connection OK";
} catch (PDOException $e) {
    echo "✗ Database connection failed: " . $e->getMessage() . "\n";
    $errors[] = "Database connection: " . $e->getMessage();
    die();
}

// 2. Check All Tables Exist
echo "\n=== CHECKING TABLES ===\n";
$requiredTables = [
    'users',
    'pengumuman',
    'kegiatan',
    'notifikasi',
    'layanan_surat',
    'iuran',
    'anggota_keluarga',
    'anggota_keluarga_request'
];

foreach ($requiredTables as $table) {
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM $table");
        $count = $stmt->fetch()['count'];
        echo "✓ Table '$table' exists ($count records)\n";
    } catch (PDOException $e) {
        echo "✗ Table '$table' not found\n";
        $errors[] = "Table $table not found";
    }
}

// 3. Check Table Structure
echo "\n=== CHECKING TABLE STRUCTURE ===\n";

// Check users table
echo "Checking 'users' table structure...\n";
try {
    $stmt = $pdo->query("DESCRIBE users");
    $columns = [];
    while ($row = $stmt->fetch()) {
        $columns[] = $row['Field'];
    }
    
    $requiredColumns = ['id', 'nama', 'email', 'password', 'role', 'nik', 'no_kk', 'rt', 'rw', 'status'];
    foreach ($requiredColumns as $col) {
        if (in_array($col, $columns)) {
            echo "  ✓ Column '$col' exists\n";
        } else {
            echo "  ✗ Column '$col' MISSING\n";
            $errors[] = "users table missing column: $col";
        }
    }
} catch (PDOException $e) {
    echo "  ✗ Error checking users table: " . $e->getMessage() . "\n";
}

// Check pengumuman table
echo "\nChecking 'pengumuman' table structure...\n";
try {
    $stmt = $pdo->query("DESCRIBE pengumuman");
    $columns = [];
    while ($row = $stmt->fetch()) {
        $columns[] = $row['Field'];
    }
    
    $requiredColumns = ['id', 'judul', 'kategori', 'isi', 'tanggal_acara', 'lokasi_acara', 'dibuat_oleh', 'created_at', 'views'];
    foreach ($requiredColumns as $col) {
        if (in_array($col, $columns)) {
            echo "  ✓ Column '$col' exists\n";
        } else {
            echo "  ✗ Column '$col' MISSING\n";
            $errors[] = "pengumuman table missing column: $col";
        }
    }
} catch (PDOException $e) {
    echo "  ✗ Error checking pengumuman table: " . $e->getMessage() . "\n";
}

// Check kegiatan table
echo "\nChecking 'kegiatan' table structure...\n";
try {
    $stmt = $pdo->query("DESCRIBE kegiatan");
    $columns = [];
    while ($row = $stmt->fetch()) {
        $columns[] = $row['Field'];
    }
    
    $requiredColumns = ['id', 'nama_kegiatan', 'kategori', 'tanggal_kegiatan', 'lokasi', 'deskripsi', 'kuota_peserta', 'jumlah_peserta', 'status', 'dibuat_oleh', 'created_at'];
    foreach ($requiredColumns as $col) {
        if (in_array($col, $columns)) {
            echo "  ✓ Column '$col' exists\n";
        } else {
            echo "  ✗ Column '$col' MISSING\n";
            $errors[] = "kegiatan table missing column: $col";
        }
    }
} catch (PDOException $e) {
    echo "  ✗ Error checking kegiatan table: " . $e->getMessage() . "\n";
}

// Check notifikasi table
echo "\nChecking 'notifikasi' table structure...\n";
try {
    $stmt = $pdo->query("DESCRIBE notifikasi");
    $columns = [];
    while ($row = $stmt->fetch()) {
        $columns[] = $row['Field'];
    }
    
    $requiredColumns = ['id', 'user_id', 'judul', 'isi', 'tipe', 'sudah_dibaca', 'created_at'];
    foreach ($requiredColumns as $col) {
        if (in_array($col, $columns)) {
            echo "  ✓ Column '$col' exists\n";
        } else {
            echo "  ✗ Column '$col' MISSING\n";
            $errors[] = "notifikasi table missing column: $col";
        }
    }
} catch (PDOException $e) {
    echo "  ✗ Error checking notifikasi table: " . $e->getMessage() . "\n";
}

// Check layanan_surat table
echo "\nChecking 'layanan_surat' table structure...\n";
try {
    $stmt = $pdo->query("DESCRIBE layanan_surat");
    $columns = [];
    while ($row = $stmt->fetch()) {
        $columns[] = $row['Field'];
    }
    
    $requiredColumns = ['id', 'user_id', 'jenis_surat', 'keperluan', 'status', 'no_surat', 'catatan_admin', 'dibuat_oleh', 'created_at'];
    foreach ($requiredColumns as $col) {
        if (in_array($col, $columns)) {
            echo "  ✓ Column '$col' exists\n";
        } else {
            echo "  ✗ Column '$col' MISSING\n";
            $errors[] = "layanan_surat table missing column: $col";
        }
    }
} catch (PDOException $e) {
    echo "  ✗ Error checking layanan_surat table: " . $e->getMessage() . "\n";
}

// Check iuran table
echo "\nChecking 'iuran' table structure...\n";
try {
    $stmt = $pdo->query("DESCRIBE iuran");
    $columns = [];
    while ($row = $stmt->fetch()) {
        $columns[] = $row['Field'];
    }
    
    $requiredColumns = ['id', 'user_id', 'jenis_iuran', 'nominal', 'bulan', 'tanggal_bayar', 'status', 'keterangan', 'dibuat_oleh', 'created_at'];
    foreach ($requiredColumns as $col) {
        if (in_array($col, $columns)) {
            echo "  ✓ Column '$col' exists\n";
        } else {
            echo "  ✗ Column '$col' MISSING\n";
            $errors[] = "iuran table missing column: $col";
        }
    }
} catch (PDOException $e) {
    echo "  ✗ Error checking iuran table: " . $e->getMessage() . "\n";
}

// 4. Check PHP Files Exist
echo "\n=== CHECKING PHP FILES ===\n";
$phpFiles = [
    'dashboard.php',
    'warga.php',
    'kk.php',
    'requests.php',
    'pengumuman.php',
    'notifikasi.php',
    'layanan.php',
    'iuran.php',
    'kegiatan.php',
    'laporan.php',
    'warga-saya.php',
    'keluarga-saya.php',
    'iuran-saya.php'
];

foreach ($phpFiles as $file) {
    if (file_exists(__DIR__ . '/' . $file)) {
        echo "✓ $file exists\n";
    } else {
        echo "✗ $file NOT FOUND\n";
        $errors[] = "File $file not found";
    }
}

// 5. Check API Files
echo "\n=== CHECKING API FILES ===\n";
$apiFiles = [
    'api/pengumuman.php',
    'api/kegiatan.php',
    'api/notifikasi.php',
    'api/layanan.php',
    'api/iuran.php',
    'api/warga.php',
    'api/requests.php'
];

foreach ($apiFiles as $file) {
    if (file_exists(__DIR__ . '/' . $file)) {
        echo "✓ $file exists\n";
    } else {
        echo "✗ $file NOT FOUND\n";
        $errors[] = "API file $file not found";
    }
}

// 6. Check Data Count
echo "\n=== CHECKING DATA COUNT ===\n";
$tables = [
    'users' => 'Users',
    'pengumuman' => 'Pengumuman',
    'kegiatan' => 'Kegiatan',
    'notifikasi' => 'Notifikasi',
    'layanan_surat' => 'Layanan Surat',
    'iuran' => 'Iuran',
    'anggota_keluarga_request' => 'Request Anggota'
];

foreach ($tables as $table => $label) {
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM $table");
        $count = $stmt->fetch()['count'];
        if ($count == 0) {
            echo "⚠ $label: 0 records (EMPTY)\n";
            $warnings[] = "$label table is empty";
        } else {
            echo "✓ $label: $count records\n";
        }
    } catch (PDOException $e) {
        echo "✗ Error checking $label: " . $e->getMessage() . "\n";
    }
}

// 7. Summary
echo "\n=================================\n";
echo "SUMMARY\n";
echo "=================================\n";
echo "Errors: " . count($errors) . "\n";
echo "Warnings: " . count($warnings) . "\n";
echo "Success: " . count($success) . "\n";

if (count($errors) > 0) {
    echo "\n❌ ERRORS FOUND:\n";
    foreach ($errors as $error) {
        echo "  - $error\n";
    }
}

if (count($warnings) > 0) {
    echo "\n⚠️  WARNINGS:\n";
    foreach ($warnings as $warning) {
        echo "  - $warning\n";
    }
}

if (count($errors) == 0) {
    echo "\n✅ ALL CHECKS PASSED!\n";
}

echo "\n=================================\n";

if (count($warnings) > 0 && strpos(implode(' ', $warnings), 'empty') !== false) {
    echo "\n⚠️  Some tables are empty. Run fix_sample_data.php to insert sample data.\n";
    echo "<a href='fix_sample_data.php'>📥 Insert Sample Data Now</a>\n";
}

echo "</pre>";
?>
