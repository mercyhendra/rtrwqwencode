<?php
/**
 * Create Gallery Table Script
 * Akses: https://vbr.fakechefnats.site/create_gallery_table.php
 */

require_once __DIR__ . '/api/config.php';

echo "<h2>📷 Create Gallery Table</h2><pre>";

try {
    $pdo = getDbConnection();
    echo "✓ Database connected\n\n";
    
    // Read SQL file
    $sqlFile = __DIR__ . '/database/create_kegiatan_galeri.sql';
    if (!file_exists($sqlFile)) {
        throw new Exception('SQL file not found');
    }
    
    $sql = file_get_contents($sqlFile);
    
    // Execute SQL
    $pdo->exec($sql);
    
    echo "✓ Table 'kegiatan_galeri' created successfully!\n\n";
    
    // Create upload directory
    $uploadDir = __DIR__ . '/uploads/kegiatan/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
        echo "✓ Upload directory created: $uploadDir\n";
    } else {
        echo "✓ Upload directory already exists: $uploadDir\n";
    }
    
    // Create .htaccess for security
    $htaccessContent = "Options -Indexes\n<Files *.php>\ndeny from all\n</Files>";
    file_put_contents($uploadDir . '.htaccess', $htaccessContent);
    echo "✓ .htaccess created for security\n";
    
    echo "\n=================================\n";
    echo "✅ Gallery setup completed!\n";
    echo "=================================\n";
    echo "\n<a href='kegiatan.php'>📅 Back to Kegiatan</a>";
    
} catch (PDOException $e) {
    echo "✗ Error: " . $e->getMessage();
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage();
}

echo "</pre>";
?>
