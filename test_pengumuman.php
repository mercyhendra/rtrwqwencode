<?php
/**
 * Test fetch pengumuman
 */
require_once __DIR__ . '/api/config.php';
require_once __DIR__ . '/includes/auth.php';

echo "<h2>Test Fetch Pengumuman</h2><pre>";

try {
    $pdo = getDbConnection();
    echo "✓ Database connected\n\n";
    
    // Test query
    echo "Query: SELECT p.*, u.name as posted_by_name FROM pengumuman p LEFT JOIN users u ON p.dibuat_oleh = u.id ORDER BY p.created_at DESC\n\n";
    
    $stmt = $pdo->prepare("
        SELECT p.*, u.name as posted_by_name 
        FROM pengumuman p 
        LEFT JOIN users u ON p.dibuat_oleh = u.id 
        ORDER BY p.created_at DESC
    ");
    $stmt->execute();
    $pengumumanList = $stmt->fetchAll();
    
    echo "Found " . count($pengumumanList) . " pengumuman:\n\n";
    
    foreach ($pengumumanList as $p) {
        echo "ID: {$p['id']}\n";
        echo "  Judul: {$p['judul']}\n";
        echo "  Kategori: {$p['kategori']}\n";
        echo "  Isi: " . substr($p['isi'], 0, 50) . "...\n";
        echo "  Tanggal Acara: {$p['tanggal_acara']}\n";
        echo "  Lokasi Acara: {$p['lokasi_acara']}\n";
        echo "  Dibuat Oleh: {$p['dibuat_oleh']}\n";
        echo "  Posted By Name: {$p['posted_by_name']}\n";
        echo "  Views: {$p['views']}\n";
        echo "  Created At: {$p['created_at']}\n";
        echo "---\n";
    }
    
} catch (PDOException $e) {
    echo "✗ Error: " . $e->getMessage();
}

echo "</pre>";
?>
