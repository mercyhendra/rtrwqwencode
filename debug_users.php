<?php
/**
 * Debug script untuk cek data users di database
 * Akses: http://localhost/debug_users.php
 */

require_once 'api/config.php';

$pdo = getDbConnection();

echo "<h2>Debug: Data Users di Database</h2>";
echo "<hr>";

try {
    $stmt = $pdo->query("SELECT id, email, role, nama, no_kk, nik, rt, rw, no_rumah, whatsapp, pekerjaan, status, created_at FROM users ORDER BY created_at DESC");
    $users = $stmt->fetchAll();
    
    echo "<h3>Total Users: " . count($users) . "</h3>";
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr>
            <th>ID</th>
            <th>Email</th>
            <th>Role</th>
            <th>Nama</th>
            <th>No KK</th>
            <th>NIK</th>
            <th>RT/RW</th>
            <th>No Rumah</th>
            <th>WhatsApp</th>
            <th>Status</th>
            <th>Created</th>
          </tr>";
    
    foreach ($users as $user) {
        echo "<tr>";
        echo "<td>{$user['id']}</td>";
        echo "<td>{$user['email']}</td>";
        echo "<td>{$user['role']}</td>";
        echo "<td>{$user['nama']}</td>";
        echo "<td><strong>{$user['no_kk']}</strong></td>";
        echo "<td>{$user['nik']}</td>";
        echo "<td>{$user['rt']}/{$user['rw']}</td>";
        echo "<td>{$user['no_rumah']}</td>";
        echo "<td>{$user['whatsapp']}</td>";
        echo "<td>{$user['status']}</td>";
        echo "<td>{$user['created_at']}</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    echo "<hr>";
    echo "<h3>Grouped by KK:</h3>";
    
    $grouped = [];
    foreach ($users as $user) {
        if ($user['role'] === 'warga') {
            $kk = $user['no_kk'] ?? 'unknown';
            if (!isset($grouped[$kk])) {
                $grouped[$kk] = [];
            }
            $grouped[$kk][] = $user;
        }
    }
    
    foreach ($grouped as $kk => $members) {
        echo "<p><strong>KK: $kk</strong> - Total: " . count($members) . " anggota</p>";
        echo "<ul>";
        foreach ($members as $member) {
            echo "<li>{$member['nama']} ({$member['email']})</li>";
        }
        echo "</ul>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>
