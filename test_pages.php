<?php
/**
 * Test Page - Cek semua halaman PHP
 * Akses: https://vbr.fakechefnats.site/test_pages.php
 */

require_once __DIR__ . '/api/config.php';

echo "<h2>🧪 Test Halaman PHP</h2>";
echo "<pre>";

$pages = [
    'dashboard.php' => 'Dashboard',
    'pengumuman.php' => 'Pengumuman',
    'kegiatan.php' => 'Kegiatan',
    'notifikasi.php' => 'Notifikasi',
    'layanan.php' => 'Layanan Surat',
    'iuran.php' => 'Iuran',
    'warga.php' => 'Data Warga',
    'kk.php' => 'Kartu Keluarga',
    'requests.php' => 'Request Anggota',
    'laporan.php' => 'Laporan',
    'iuran-saya.php' => 'Iuran Saya',
    'keluarga-saya.php' => 'Keluarga Saya',
    'warga-saya.php' => 'Warga Saya'
];

echo "=== CHECKING PAGES ===\n\n";

foreach ($pages as $file => $label) {
    $path = __DIR__ . '/' . $file;
    if (file_exists($path)) {
        echo "✓ $label ($file) - EXISTS\n";
        
        // Check if it's PHP and has proper structure
        $content = file_get_contents($path);
        if (strpos($content, '<?php') !== false) {
            echo "  └─ ✓ Contains PHP code\n";
        }
        if (strpos($content, 'require') !== false || strpos($content, 'include') !== false) {
            echo "  └─ ✓ Has includes\n";
        }
        if (strpos($content, 'getDbConnection') !== false) {
            echo "  └─ ✓ Uses database\n";
        }
    } else {
        echo "✗ $label ($file) - NOT FOUND\n";
    }
}

echo "\n=== CHECKING API ENDPOINTS ===\n\n";

$apiEndpoints = [
    'api/pengumuman.php' => 'Pengumuman API',
    'api/kegiatan.php' => 'Kegiatan API',
    'api/notifikasi.php' => 'Notifikasi API',
    'api/layanan.php' => 'Layanan API',
    'api/iuran.php' => 'Iuran API'
];

foreach ($apiEndpoints as $file => $label) {
    $path = __DIR__ . '/' . $file;
    if (file_exists($path)) {
        echo "✓ $label ($file) - EXISTS\n";
    } else {
        echo "✗ $label ($file) - NOT FOUND\n";
    }
}

echo "\n=== CHECKING DATABASE TABLES ===\n\n";

try {
    $pdo = getDbConnection();
    echo "✓ Database connected\n\n";
    
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
            $row = $stmt->fetch();
            echo "✓ $label ($table): {$row['count']} records\n";
        } catch (PDOException $e) {
            echo "✗ $label ($table): Table not found\n";
        }
    }
    
} catch (PDOException $e) {
    echo "✗ Database connection failed: " . $e->getMessage() . "\n";
}

echo "\n=== SUMMARY ===\n\n";

$phpFiles = glob(__DIR__ . '/*.php');
$apiFiles = glob(__DIR__ . '/api/*.php');

echo "Total PHP files: " . count($phpFiles) . "\n";
echo "Total API files: " . count($apiFiles) . "\n";

echo "\n=================================\n";
echo "Test completed!\n";
echo "=================================\n";

echo "</pre>";
echo "<a href='dashboard.php'>📊 Ke Dashboard</a> | ";
echo "<a href='pengumuman.php'>📢 Ke Pengumuman</a> | ";
echo "<a href='kegiatan.php'>📅 Ke Kegiatan</a> | ";
echo "<a href='layanan.php'>📋 Ke Layanan</a> | ";
echo "<a href='iuran.php'>💰 Ke Iuran</a> | ";
echo "<a href='notifikasi.php'>🔔 Ke Notifikasi</a>";
?>
