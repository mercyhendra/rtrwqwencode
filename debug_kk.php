<?php
/**
 * Debug khusus untuk KK data
 * Akses: http://localhost/debug_kk.php
 */

require_once 'api/config.php';

$pdo = getDbConnection();

echo "<h2>Debug: Data Kartu Keluarga</h2>";
echo "<hr>";

try {
    // Get all warga users
    $stmt = $pdo->query("
        SELECT id, email, role, nama, no_kk, nik, rt, rw, no_rumah, whatsapp, pekerjaan, status 
        FROM users 
        WHERE role = 'warga' 
        ORDER BY no_kk, created_at
    ");
    $users = $stmt->fetchAll();
    
    echo "<h3>Total Warga: " . count($users) . "</h3>";
    
    if (count($users) === 0) {
        echo "<p style='color: orange;'>⚠️ Belum ada warga terdaftar!</p>";
    } else {
        // Group by KK
        $grouped = [];
        foreach ($users as $user) {
            $kk = $user['no_kk'] ?? 'unknown';
            if (!isset($grouped[$kk])) {
                $grouped[$kk] = [];
            }
            $grouped[$kk][] = $user;
        }
        
        echo "<h3>Total KK: " . count($grouped) . "</h3>";
        
        foreach ($grouped as $kk => $members) {
            echo "<div style='border: 1px solid #ddd; padding: 15px; margin: 10px 0; border-radius: 5px;'>";
            echo "<h4 style='color: var(--primary-color);'>🏠 KK: " . ($kk ?? 'NULL') . "</h4>";
            echo "<p><strong>Total Anggota:</strong> " . count($members) . "</p>";
            echo "<ul>";
            foreach ($members as $member) {
                echo "<li>";
                echo "<strong>" . htmlspecialchars($member['nama']) . "</strong><br>";
                echo "Email: " . htmlspecialchars($member['email']) . "<br>";
                echo "No KK: " . htmlspecialchars($member['no_kk']) . "<br>";
                echo "NIK: " . htmlspecialchars($member['nik']) . "<br>";
                echo "RT/RW: " . htmlspecialchars($member['rt']) . "/" . htmlspecialchars($member['rw']) . "<br>";
                echo "No Rumah: " . htmlspecialchars($member['no_rumah']) . "<br>";
                echo "Status: " . htmlspecialchars($member['status']);
                echo "</li>";
            }
            echo "</ul>";
            echo "</div>";
        }
    }
    
    echo "<hr>";
    echo "<h3>Raw Data (JSON):</h3>";
    echo "<pre>" . json_encode($grouped ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>
