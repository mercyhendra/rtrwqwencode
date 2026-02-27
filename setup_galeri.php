<?php
/**
 * Setup Galeri Foto Kegiatan
 * Akses: https://vbr.fakechefnats.site/setup_galeri.php
 */

require_once __DIR__ . '/api/config.php';

echo "<h2>📷 Setup Galeri Foto Kegiatan</h2><pre>";

try {
    $pdo = getDbConnection();
    echo "✓ Database connected\n\n";
    
    // 1. Create table
    echo "=== CREATING TABLE ===\n";
    $sql = "
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";
    
    $pdo->exec($sql);
    echo "✓ Table 'kegiatan_galeri' created\n";
    
    // 2. Create upload directory
    echo "\n=== CREATING UPLOAD DIRECTORY ===\n";
    $uploadDir = __DIR__ . '/uploads/kegiatan/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
        echo "✓ Upload directory created: $uploadDir\n";
    } else {
        echo "✓ Upload directory already exists\n";
    }
    
    // 3. Create .htaccess for security
    echo "\n=== CREATING SECURITY FILES ===\n";
    $htaccessContent = "Options -Indexes\n<Files *.php>\ndeny from all\n</Files>";
    file_put_contents($uploadDir . '.htaccess', $htaccessContent);
    echo "✓ .htaccess created\n";
    
    // 4. Create index.html to prevent directory listing
    file_put_contents($uploadDir . 'index.html', '<!DOCTYPE html><html><head><title>403 Forbidden</title></head><body><h1>403 - Forbidden</h1></body></html>');
    echo "✓ index.html created\n";
    
    // 5. Test write permission
    echo "\n=== TESTING PERMISSIONS ===\n";
    $testFile = $uploadDir . 'test.txt';
    if (file_put_contents($testFile, 'test')) {
        unlink($testFile);
        echo "✓ Upload directory is writable\n";
    } else {
        echo "⚠ Warning: Upload directory may not be writable\n";
    }
    
    echo "\n=================================\n";
    echo "✅ GALLERY SETUP COMPLETED!\n";
    echo "=================================\n";
    echo "\nFeatures available:\n";
    echo "✓ Upload foto kegiatan (staff only)\n";
    echo "✓ View galeri foto\n";
    echo "✓ Delete foto (staff only)\n";
    echo "✓ Full image view\n";
    echo "✓ Max file size: 20MB\n";
    echo "✓ Supported formats: JPG, PNG, GIF, WEBP\n";
    echo "\n<a href='kegiatan.php'>📅 Go to Kegiatan Page</a>";
    
} catch (PDOException $e) {
    echo "✗ Database Error: " . $e->getMessage();
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage();
}

echo "</pre>";
?>
