<?php
require_once __DIR__ . '/api/config.php';
echo "<h2>Fix Pengumuman Table Structure</h2><pre>";

try {
    $pdo = getDbConnection();
    echo "✓ Database connected\n\n";
    
    // Check existing columns
    echo "Checking existing columns...\n";
    $stmt = $pdo->query('DESCRIBE pengumuman');
    $columns = [];
    while ($row = $stmt->fetch()) {
        $columns[] = $row['Field'];
        echo "  - {$row['Field']} ({$row['Type']})\n";
    }
    
    echo "\n";
    
    // Check and add missing columns
    $changes = [];
    
    if (!in_array('tanggal_acara', $columns)) {
        if (in_array('tanggal_event', $columns)) {
            echo "→ Renaming 'tanggal_event' to 'tanggal_acara'...\n";
            $pdo->exec('ALTER TABLE pengumuman CHANGE tanggal_event tanggal_acara DATETIME NULL');
            $changes[] = 'tanggal_acara';
        } else {
            echo "→ Adding 'tanggal_acara' column...\n";
            $pdo->exec('ALTER TABLE pengumuman ADD COLUMN tanggal_acara DATETIME NULL');
            $changes[] = 'tanggal_acara';
        }
    }
    
    if (!in_array('lokasi_acara', $columns)) {
        if (in_array('lokasi', $columns)) {
            echo "→ Renaming 'lokasi' to 'lokasi_acara'...\n";
            $pdo->exec('ALTER TABLE pengumuman CHANGE lokasi lokasi_acara VARCHAR(255) NULL');
            $changes[] = 'lokasi_acara';
        } else {
            echo "→ Adding 'lokasi_acara' column...\n";
            $pdo->exec('ALTER TABLE pengumuman ADD COLUMN lokasi_acara VARCHAR(255) NULL');
            $changes[] = 'lokasi_acara';
        }
    }
    
    if (!in_array('dibuat_oleh', $columns)) {
        if (in_array('diposting_oleh', $columns)) {
            echo "→ Renaming 'diposting_oleh' to 'dibuat_oleh'...\n";
            $pdo->exec('ALTER TABLE pengumuman CHANGE diposting_oleh dibuat_oleh INT NOT NULL');
            $changes[] = 'dibuat_oleh';
        } else {
            echo "→ Adding 'dibuat_oleh' column...\n";
            $pdo->exec('ALTER TABLE pengumuman ADD COLUMN dibuat_oleh INT NOT NULL DEFAULT 1');
            $changes[] = 'dibuat_oleh';
        }
    }
    
    if (!in_array('created_at', $columns)) {
        echo "→ Adding 'created_at' column...\n";
        $pdo->exec('ALTER TABLE pengumuman ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP');
        $changes[] = 'created_at';
    }
    
    if (empty($changes)) {
        echo "✓ No changes needed. Table structure is correct.\n";
    } else {
        echo "\n✓ Applied changes: " . implode(', ', $changes) . "\n";
    }
    
    // Show final structure
    echo "\nFinal table structure:\n";
    $stmt = $pdo->query('DESCRIBE pengumuman');
    while ($row = $stmt->fetch()) {
        echo "  - {$row['Field']} ({$row['Type']})\n";
    }
    
    // Show existing data
    echo "\nExisting data:\n";
    $stmt = $pdo->query('SELECT id, judul, kategori, created_at FROM pengumuman ORDER BY created_at DESC');
    while ($row = $stmt->fetch()) {
        echo "  [{$row['id']}] {$row['judul']} ({$row['kategori']}) - {$row['created_at']}\n";
    }
    
    echo "\n=================================\n";
    echo "✓ Table structure fixed!\n";
    echo "=================================\n";
    echo "\n<a href='pengumuman.php'>Lihat Halaman Pengumuman</a>";
    
} catch (PDOException $e) {
    echo "✗ Error: " . $e->getMessage();
}

echo "</pre>";
?>
