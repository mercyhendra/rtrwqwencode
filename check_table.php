<?php
require_once __DIR__ . '/api/config.php';
$pdo = getDbConnection();
$stmt = $pdo->query('DESCRIBE pengumuman');
echo "<pre>";
while ($row = $stmt->fetch()) {
    print_r($row);
}
echo "</pre>";
?>
