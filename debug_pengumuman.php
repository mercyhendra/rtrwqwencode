<?php
/**
 * Debug lengkap pengumuman
 */
require_once __DIR__ . '/api/config.php';

echo "<h2>Debug Pengumuman Database</h2><pre>";

try {
    $pdo = getDbConnection();
    echo "✓ Database connected\n\n";
    
    // 1. Check table structure
    echo "=== TABLE STRUCTURE ===\n";
    $stmt = $pdo->query('DESCRIBE pengumuman');
    while ($row = $stmt->fetch()) {
        echo "  {$row['Field']} | {$row['Type']} | {$row['Null']} | {$row['Key']} | {$row['Default']}\n";
    }
    
    // 2. Count records
    echo "\n=== RECORD COUNT ===\n";
    $stmt = $pdo->query('SELECT COUNT(*) as count FROM pengumuman');
    $count = $stmt->fetch();
    echo "Total records: {$count['count']}\n";
    
    // 3. Show ALL data with ALL columns
    echo "\n=== ALL DATA (RAW) ===\n";
    $stmt = $pdo->query('SELECT * FROM pengumuman ORDER BY id');
    $allData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($allData as $i => $row) {
        echo "\n--- Record #" . ($i + 1) . " ---\n";
        foreach ($row as $key => $value) {
            if (is_null($value)) {
                echo "  {$key}: NULL\n";
            } elseif (strlen($value) > 100) {
                echo "  {$key}: " . substr($value, 0, 100) . "...\n";
            } else {
                echo "  {$key}: {$value}\n";
            }
        }
    }
    
    // 4. Test the actual query used in pengumuman.php
    echo "\n=== TEST QUERY (from pengumuman.php) ===\n";
    $stmt = $pdo->prepare("
        SELECT p.*, u.name as posted_by_name 
        FROM pengumuman p 
        LEFT JOIN users u ON p.dibuat_oleh = u.id 
        ORDER BY COALESCE(p.created_at, p.tanggal_post) DESC
    ");
    $stmt->execute();
    $result = $stmt->fetchAll();
    echo "Query returned: " . count($result) . " rows\n";
    
    foreach ($result as $i => $row) {
        echo "\n--- Result #" . ($i + 1) . " ---\n";
        echo "  id: {$row['id']}\n";
        echo "  judul: {$row['judul']}\n";
        echo "  kategori: {$row['kategori']}\n";
        echo "  created_at: {$row['created_at']}\n";
        echo "  tanggal_acara: {$row['tanggal_acara']}\n";
        echo "  lokasi_acara: {$row['lokasi_acara']}\n";
        echo "  dibuat_oleh: {$row['dibuat_oleh']}\n";
        echo "  posted_by_name: {$row['posted_by_name']}\n";
    }
    
    // 5. Check users table
    echo "\n=== USERS TABLE ===\n";
    $stmt = $pdo->query('SELECT id, name, email, role FROM users');
    while ($row = $stmt->fetch()) {
        echo "  [{$row['id']}] {$row['name']} ({$row['email']}) - {$row['role']}\n";
    }
    
} catch (PDOException $e) {
    echo "✗ Error: " . $e->getMessage();
}

echo "</pre>";
?>
