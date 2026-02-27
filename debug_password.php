<?php
/**
 * Debug Password Hash & Login
 * Akses: https://vbr.fakechefnats.site/debug_password.php
 */

require_once __DIR__ . '/api/config.php';
require_once __DIR__ . '/includes/auth.php';

echo "<h2>🔍 Debug Password Hash & Login</h2><pre>";

// Test 1: Check hashPassword function
echo "=== TEST 1: hashPassword Function ===\n";
$testPassword = 'test123';
$hashed = hashPassword($testPassword);
echo "Plain password: $testPassword\n";
echo "Hashed: $hashed\n";
echo "Hash starts with \$2y\$: " . (strpos($hashed, '$2y$') === 0 ? 'YES' : 'NO') . "\n";

// Test 2: Check verifyPassword function
echo "\n=== TEST 2: verifyPassword Function ===\n";
$verify = verifyPassword($testPassword, $hashed);
echo "Verify result: " . ($verify ? 'TRUE' : 'FALSE') . "\n";

// Test 3: Check user in database
echo "\n=== TEST 3: Check User in Database ===\n";
try {
    $pdo = getDbConnection();
    
    // Find user by email
    $email = 'nanda@local';
    $stmt = $pdo->prepare("SELECT id, nama, email, role, password FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user) {
        echo "✓ User found:\n";
        echo "  ID: {$user['id']}\n";
        echo "  Nama: {$user['nama']}\n";
        echo "  Email: {$user['email']}\n";
        echo "  Role: {$user['role']}\n";
        echo "  Password hash: {$user['password']}\n";
        echo "  Password hash length: " . strlen($user['password']) . "\n";
        echo "  Hash starts with \$2y\$: " . (strpos($user['password'], '$2y$') === 0 ? 'YES' : 'NO') . "\n";
        
        // Test verify with test password
        echo "\n=== TEST 4: Verify Password ===\n";
        $testPass = 'test123';
        $verify = verifyPassword($testPass, $user['password']);
        echo "Testing password '$testPass': " . ($verify ? 'MATCH' : 'NO MATCH') . "\n";
        
        // Try login
        echo "\n=== TEST 5: Simulate Login ===\n";
        $loginResult = login($email, $testPass);
        echo "Login result: " . json_encode($loginResult, JSON_PRETTY_PRINT) . "\n";
        
    } else {
        echo "✗ User with email '$email' not found\n";
        
        // List all users
        echo "\nAll users in database:\n";
        $stmt = $pdo->query("SELECT id, nama, email, role FROM users ORDER BY id");
        while ($u = $stmt->fetch()) {
            echo "  [{$u['id']}] {$u['nama']} ({$u['email']}) - {$u['role']}\n";
        }
    }
    
} catch (PDOException $e) {
    echo "✗ Database error: " . $e->getMessage();
}

echo "\n=================================\n";
echo "Debug completed!\n";
echo "=================================\n";
?>

</pre>

<hr>

<h3>Quick Actions</h3>
<form method="POST" action="">
    <h4>Reset Password for nanda@local</h4>
    <input type="hidden" name="reset_email" value="nanda@local">
    <input type="text" name="new_password" placeholder="New password" required>
    <button type="submit" name="reset_password">Reset Password</button>
</form>

<?php
// Handle password reset
if (isset($_POST['reset_password'])) {
    try {
        $pdo = getDbConnection();
        $email = $_POST['reset_email'];
        $newPassword = $_POST['new_password'];
        
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
        $stmt->execute([hashPassword($newPassword), $email]);
        
        echo "<p style='color: green;'><strong>✓ Password reset successfully!</strong></p>";
        echo "<p>Try logging in with email <strong>$email</strong> and password <strong>" . htmlspecialchars($newPassword) . "</strong></p>";
    } catch (PDOException $e) {
        echo "<p style='color: red;'><strong>✗ Error: " . $e->getMessage() . "</strong></p>";
    }
}
?>
