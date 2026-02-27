<?php
/**
 * Debug Upload Error
 * Akses: https://vbr.fakechefnats.site/debug_upload.php
 */

echo "<h2>🔍 Debug Upload Error</h2><pre>";

echo "=== CHECKING PHP CONFIG ===\n";
echo "Upload Max Filesize: " . ini_get('upload_max_filesize') . "\n";
echo "Post Max Size: " . ini_get('post_max_size') . "\n";
echo "File Uploads: " . ini_get('file_uploads') . "\n";
echo "Upload Temp Dir: " . ini_get('upload_tmp_dir') . "\n";

echo "\n=== CHECKING DIRECTORY ===\n";
$uploadDir = __DIR__ . '/uploads/kegiatan/';
echo "Upload directory: $uploadDir\n";

if (file_exists($uploadDir)) {
    echo "✓ Directory exists\n";
} else {
    echo "✗ Directory does NOT exist\n";
    echo "Creating directory...\n";
    if (mkdir($uploadDir, 0755, true)) {
        echo "✓ Directory created\n";
    } else {
        echo "✗ Failed to create directory\n";
    }
}

if (is_writable($uploadDir)) {
    echo "✓ Directory is writable\n";
} else {
    echo "✗ Directory is NOT writable\n";
    echo "Trying to fix permissions...\n";
    chmod($uploadDir, 0755);
    if (is_writable($uploadDir)) {
        echo "✓ Permissions fixed\n";
    } else {
        echo "✗ Still not writable. Check server permissions.\n";
    }
}

echo "\n=== CHECKING .HTACCESS ===\n";
$htaccessFile = $uploadDir . '.htaccess';
if (file_exists($htaccessFile)) {
    echo "✓ .htaccess exists\n";
    echo "Content:\n";
    echo file_get_contents($htaccessFile);
} else {
    echo "✗ .htaccess does NOT exist\n";
    echo "Creating...\n";
    $content = "Options -Indexes\n<Files *.php>\ndeny from all\n</Files>";
    file_put_contents($htaccessFile, $content);
    echo "✓ .htaccess created\n";
}

echo "\n=== CHECKING API FILE ===\n";
$apiFile = __DIR__ . '/api/kegiatan_galeri.php';
if (file_exists($apiFile)) {
    echo "✓ API file exists\n";
} else {
    echo "✗ API file does NOT exist\n";
}

echo "\n=== CHECKING DATABASE TABLE ===\n";
try {
    require_once __DIR__ . '/api/config.php';
    $pdo = getDbConnection();
    
    $stmt = $pdo->query("SHOW TABLES LIKE 'kegiatan_galeri'");
    if ($stmt->fetch()) {
        echo "✓ Table 'kegiatan_galeri' exists\n";
        
        // Check table structure
        $stmt = $pdo->query("DESCRIBE kegiatan_galeri");
        echo "\nTable structure:\n";
        while ($row = $stmt->fetch()) {
            echo "  {$row['Field']} | {$row['Type']} | {$row['Null']} | {$row['Key']}\n";
        }
    } else {
        echo "✗ Table 'kegiatan_galeri' does NOT exist\n";
        echo "Run setup_galeri.php first!\n";
    }
} catch (PDOException $e) {
    echo "✗ Database error: " . $e->getMessage();
}

echo "\n=== TESTING FILE UPLOAD ===\n";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['test_file'])) {
    $file = $_FILES['test_file'];
    echo "File received:\n";
    echo "  Name: {$file['name']}\n";
    echo "  Type: {$file['type']}\n";
    echo "  Size: {$file['size']} bytes\n";
    echo "  Temp: {$file['tmp_name']}\n";
    echo "  Error: {$file['error']}\n";
    
    if ($file['error'] === UPLOAD_ERR_OK) {
        $testFile = $uploadDir . 'test_' . time() . '.jpg';
        if (move_uploaded_file($file['tmp_name'], $testFile)) {
            echo "✓ Test file uploaded successfully\n";
            unlink($testFile); // Clean up
        } else {
            echo "✗ Failed to move uploaded file\n";
        }
    } else {
        $errors = [
            UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'No temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
        ];
        echo "✗ Upload error: " . ($errors[$file['error']] ?? 'Unknown error');
    }
} else {
    echo "No file uploaded for testing.\n";
}

echo "\n=================================\n";
echo "Debug completed!\n";
echo "=================================\n";
?>

</pre>

<hr>

<h3>Test Upload Form</h3>
<form method="POST" enctype="multipart/form-data">
    <input type="file" name="test_file" accept="image/*">
    <button type="submit">Test Upload</button>
</form>
