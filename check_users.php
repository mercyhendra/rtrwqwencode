<?php
require_once __DIR__ . '/api/config.php';
echo "<h2>Users Table Structure</h2><pre>";
$pdo = getDbConnection();
$stmt = $pdo->query('DESCRIBE users');
while ($row = $stmt->fetch()) {
    echo "{$row['Field']} | {$row['Type']} | {$row['Null']} | {$row['Key']} | {$row['Default']}\n";
}
echo "</pre>";

echo "<h2>Users Data</h2><pre>";
$stmt = $pdo->query('SELECT * FROM users LIMIT 5');
while ($row = $stmt->fetch()) {
    print_r($row);
}
echo "</pre>";
?>
