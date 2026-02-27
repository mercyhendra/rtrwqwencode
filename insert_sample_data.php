<?php
/**
 * Script untuk insert sample data lengkap
 * Akses: https://vbr.fakechefnats.site/insert_sample_data.php
 */

require_once __DIR__ . '/api/config.php';

echo "<h2>Insert Sample Data Lengkap</h2>";
echo "<pre>";

try {
    $pdo = getDbConnection();
    echo "✓ Database connected\n\n";
    
    // Read SQL file
    $sqlFile = __DIR__ . '/sample_data_lengkap.sql';
    if (!file_exists($sqlFile)) {
        throw new Exception('File sample_data_lengkap.sql tidak ditemukan');
    }
    
    $sql = file_get_contents($sqlFile);
    
    // Split by statements (simple approach - split by semicolon)
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    $inserted = 0;
    $errors = 0;
    
    foreach ($statements as $statement) {
        if (empty($statement) || strpos($statement, '--') === 0) {
            continue;
        }
        
        try {
            $pdo->exec($statement);
            if (strpos($statement, 'INSERT INTO') !== false) {
                $inserted++;
            }
        } catch (PDOException $e) {
            // Ignore duplicate entry errors
            if ($e->getCode() != 23000) {
                echo "⚠ Warning: " . $e->getMessage() . "\n";
            }
            $errors++;
        }
    }
    
    echo "=================================\n";
    echo "Selesai!\n";
    echo "=================================\n";
    echo "Statements executed: $inserted\n";
    echo "Errors: $errors\n\n";
    
    // Show summary
    echo "=== SUMMARY DATA ===\n\n";
    
    $tables = [
        'pengumuman' => 'Pengumuman',
        'kegiatan' => 'Kegiatan',
        'layanan_surat' => 'Layanan Surat',
        'iuran' => 'Iuran',
        'notifikasi' => 'Notifikasi',
        'anggota_keluarga_request' => 'Request Anggota Keluarga'
    ];
    
    foreach ($tables as $table => $label) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM $table");
            $row = $stmt->fetch();
            echo "✓ $label: {$row['count']} data\n";
        } catch (PDOException $e) {
            echo "✗ $label: Table tidak ada\n";
        }
    }
    
    echo "\n=================================\n";
    echo "✓ Sample data berhasil diinsert!\n";
    echo "=================================\n\n";
    
    echo "<a href='dashboard.php'>📊 Ke Dashboard</a> | ";
    echo "<a href='pengumuman.php'>📢 Ke Pengumuman</a> | ";
    echo "<a href='kegiatan.php'>📅 Ke Kegiatan</a> | ";
    echo "<a href='layanan.php'>📋 Ke Layanan Surat</a> | ";
    echo "<a href='iuran.php'>💰 Ke Iuran</a> | ";
    echo "<a href='notifikasi.php'>🔔 Ke Notifikasi</a>";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage();
}

echo "</pre>";
?>
