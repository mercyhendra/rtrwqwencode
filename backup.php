<?php
/**
 * Backup Script - RT/RW Digital Management System
 * File: backup.php
 * 
 * Cara menggunakan:
 * 1. Buka browser: http://localhost/backup.php
 * 2. Script akan otomatis membuat backup ZIP
 * 3. Download akan otomatis dimulai
 */

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'wargavbr');
define('DB_USER', 'root');
define('DB_PASS', '');

// Project root (parent directory of this script)
$projectRoot = dirname(__FILE__);

// Backup directory
$backupDir = $projectRoot . '/backups';
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
}

// Generate filename with timestamp
$timestamp = date('Y-m-d_His');
$zipFilename = "rt-rw-backup_{$timestamp}.zip";
$zipPath = $backupDir . '/' . $zipFilename;

// Create ZIP archive
$zip = new ZipArchive();
if ($zip->open($zipPath, ZipArchive::CREATE) !== TRUE) {
    die("❌ Gagal membuat ZIP file<br>Path: {$zipPath}");
}

// Add project files to ZIP
$filesAdded = 0;

// Recursive function to add files
function addFilesToZip($zip, $source, $base = '') {
    $filesAdded = 0;
    
    if (is_dir($source)) {
        $dir = dir($source);
        while (($file = $dir->read()) !== false) {
            if ($file === '.' || $file === '..' || $file === 'backups' || $file === 'backup.php') {
                continue; // Skip backups folder and this script
            }
            
            $fullPath = $source . DIRECTORY_SEPARATOR . $file;
            $zipPath = $base ? $base . '/' . $file : $file;
            
            if (is_dir($fullPath)) {
                $filesAdded += addFilesToZip($zip, $fullPath, $zipPath);
            } else {
                $zip->addFile($fullPath, $zipPath);
                $filesAdded++;
            }
        }
        $dir->close();
    }
    
    return $filesAdded;
}

// Add all project files
$filesAdded = addFilesToZip($zip, $projectRoot);

// Export database
$dbFilename = "database_backup_{$timestamp}.sql";
$dbPath = $backupDir . '/' . $dbFilename;

try {
    // Connect to database
    $pdo = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get all tables
    $tables = $pdo->query("SHOW TABLES FROM " . DB_NAME)->fetchAll(PDO::FETCH_COLUMN);
    
    // Export structure and data
    $sql = "-- ========================================\n";
    $sql .= "-- Database Backup: " . DB_NAME . "\n";
    $sql .= "-- Date: " . date('Y-m-d H:i:s') . "\n";
    $sql .= "-- ========================================\n\n";
    
    foreach ($tables as $table) {
        // Add CREATE TABLE statement
        $create = $pdo->query("SHOW CREATE TABLE " . DB_NAME . "." . $table)->fetch();
        $sql .= "-- Table: {$table}\n";
        $sql .= "DROP TABLE IF EXISTS `{$table}`;\n";
        $sql .= $create[1] . ";\n\n";
        
        // Add INSERT statements for data
        $rows = $pdo->query("SELECT * FROM " . DB_NAME . "." . $table)->fetchAll(PDO::FETCH_ASSOC);
        if (count($rows) > 0) {
            foreach ($rows as $row) {
                $values = array_map(function($val) use ($pdo) {
                    return $val === null ? 'NULL' : $pdo->quote($val);
                }, array_values($row));
                
                $sql .= "INSERT INTO `{$table}` (`" . implode('`, `', array_keys($row)) . "`) VALUES (" . implode(', ', $values) . ");\n";
            }
            $sql .= "\n";
        }
    }
    
    // Save SQL file
    file_put_contents($dbPath, $sql);
    
    // Add SQL file to ZIP
    $zip->addFile($dbPath, 'database_backup.sql');
    
    $dbExported = true;
    $tableCount = count($tables);
    
} catch (PDOException $e) {
    $dbExported = false;
    $dbError = $e->getMessage();
    $tableCount = 0;
}

// Close ZIP
$zip->close();

// Delete temporary SQL file
if (file_exists($dbPath)) {
    unlink($dbPath);
}

// Get file size
$fileSize = filesize($zipPath);
$fileSizeMB = round($fileSize / 1024 / 1024, 2);

// Get relative path for download link (from current script location)
$downloadPath = 'backups/' . $zipFilename;

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Backup - RT/RW Digital</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 12px;
            padding: 40px;
            max-width: 600px;
            width: 100%;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 24px;
        }
        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }
        .success {
            background: #d4edda;
            border-left: 4px solid #28a745;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .info {
            background: #d1ecf1;
            border-left: 4px solid #17a2b8;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .warning {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }
        .stat-card {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
        }
        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #667eea;
        }
        .stat-label {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
        .btn {
            display: inline-block;
            background: #667eea;
            color: white;
            padding: 12px 30px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            margin: 5px;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
        }
        .btn:hover {
            background: #5568d3;
            transform: translateY(-2px);
        }
        .btn-secondary {
            background: #6c757d;
        }
        .btn-secondary:hover {
            background: #5a6268;
        }
        .file-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-family: monospace;
            font-size: 13px;
        }
        .icon {
            font-size: 48px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">💾</div>
        <h1>Backup Berhasil Dibuat!</h1>
        <p class="subtitle">RT/RW Digital Management System</p>
        
        <div class="success">
            ✅ Backup file berhasil dibuat dan siap di-download!
        </div>
        
        <div class="stats">
            <div class="stat-card">
                <div class="stat-value"><?php echo $filesAdded; ?></div>
                <div class="stat-label">Files</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $tableCount; ?></div>
                <div class="stat-label">Database Tables</div>
            </div>
        </div>
        
        <div class="file-info">
            <strong>Filename:</strong> <?php echo $zipFilename; ?><br>
            <strong>Size:</strong> <?php echo $fileSizeMB; ?> MB<br>
            <strong>Location:</strong> <?php echo realpath($zipPath); ?>
        </div>
        
        <?php if ($dbExported): ?>
            <div class="info">
                ✅ Database <strong><?php echo DB_NAME; ?></strong> berhasil di-export dan sudah termasuk dalam ZIP file.
            </div>
        <?php else: ?>
            <div class="warning">
                ⚠️ Database export gagal: <?php echo $dbError; ?><br>
                Backup hanya berisi file project (tanpa database).
            </div>
        <?php endif; ?>
        
        <div style="text-align: center; margin-top: 30px;">
            <a href="<?php echo htmlspecialchars($downloadPath); ?>" class="btn" download="<?php echo htmlspecialchars($zipFilename); ?>">
                📥 Download Backup
            </a>
            <a href="index.html" class="btn btn-secondary">
                🏠 Kembali ke Home
            </a>
            <br><br>
            <button onclick="window.location.reload()" class="btn btn-secondary">
                🔄 Refresh Page
            </button>
        </div>
        
        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; font-size: 12px; color: #999;">
            <p>💡 <strong>Tip:</strong> Simpan file backup di tempat aman (Google Drive, USB, dll)</p>
            <p>📁 Backup location: <code><?php echo realpath($backupDir); ?></code></p>
        </div>
        
        <div style="margin-top: 20px; padding: 15px; background: #fff3cd; border-radius: 8px; font-size: 13px;">
            <strong>⚠️ Download tidak otomatis?</strong><br>
            1. Klik tombol "Download Backup" di atas<br>
            2. Atau buka file langsung: <code><?php echo htmlspecialchars($downloadPath); ?></code><br>
            3. Atau cek folder: <code><?php echo realpath($backupDir); ?></code>
        </div>
    </div>
    
    <script>
        // Try to trigger download
        setTimeout(function() {
            const link = document.createElement('a');
            link.href = '<?php echo htmlspecialchars($downloadPath); ?>';
            link.download = '<?php echo htmlspecialchars($zipFilename); ?>';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }, 1500);
    </script>
</body>
</html>
