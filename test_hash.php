<?php
// Test password hash
$passwords = ['admin123', 'rt123', 'warga123'];

echo "Password Hash Generator\n";
echo "=======================\n\n";

foreach ($passwords as $password) {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    echo "Password: $password\n";
    echo "Hash: $hash\n\n";
    
    // Verify
    $verify = password_verify($password, $hash);
    echo "Verify: " . ($verify ? 'SUCCESS' : 'FAILED') . "\n\n";
    echo "---\n\n";
}

// Test with existing hash
$existingHash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';
echo "Testing existing hash from schema.sql:\n";
echo "Hash: $existingHash\n";

foreach ($passwords as $password) {
    $verify = password_verify($password, $existingHash);
    echo "Password '$password': " . ($verify ? 'MATCH' : 'NO MATCH') . "\n";
}
?>
