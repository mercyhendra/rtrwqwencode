<?php
/**
 * Test Register API directly
 * Akses: http://localhost/test_register_api.php
 */

// Test data
$testData = [
    'nama' => 'Test API User',
    'no_kk' => '3171012345678888',
    'nik' => '3171012345678887',
    'rt' => '001',
    'rw' => '001',
    'no_rumah' => 'A-88',
    'whatsapp' => '081234567888',
    'pekerjaan' => 'API Tester',
    'email' => 'testapi88@example.com',
    'password' => 'test123',
    'status' => 'aktif'
];

echo "<h2>Test Register API</h2>";
echo "<pre>";
print_r($testData);
echo "</pre>";

// Call API
$ch = curl_init('http://localhost/api/users.php?action=register');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($testData));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

$response = curl_exec($ch);
curl_close($ch);

echo "<h3>Response:</h3>";
echo "<pre>";
print_r(json_decode($response, true));
echo "</pre>";

// Check database
require_once 'api/config.php';
$pdo = getDbConnection();

echo "<h3>Verify in Database:</h3>";
$stmt = $pdo->prepare("SELECT id, email, nama, no_kk, nik, rt, rw FROM users WHERE email = ?");
$stmt->execute([$testData['email']]);
$user = $stmt->fetch();

if ($user) {
    echo "<pre>";
    print_r($user);
    echo "</pre>";
    echo "<p style='color: green;'><strong>✅ User berhasil disimpan dengan no_kk: {$user['no_kk']}</strong></p>";
} else {
    echo "<p style='color: red;'><strong>❌ User tidak ditemukan di database!</strong></p>";
}
?>
