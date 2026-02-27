<?php
/**
 * Script untuk reset password user default
 * Akses: http://localhost/reset_password.php
 */

require_once 'api/config.php';

$pdo = getDbConnection();

// Password baru
$passwords = [
    'admin@rtrw.com' => 'admin123',
    'rt@rtrw.com' => 'rt123',
    'warga@rtrw.com' => 'warga123'
];

echo "Reset Password Users\n";
echo "====================\n\n";

foreach ($passwords as $email => $password) {
    // Generate hash baru
    $hash = password_hash($password, PASSWORD_DEFAULT);
    
    // Update ke database
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
    $stmt->execute([$hash, $email]);
    
    echo "Email: $email\n";
    echo "Password: $password\n";
    echo "Hash: $hash\n";
    echo "Updated: " . ($stmt->rowCount() > 0 ? 'YES' : 'NO') . "\n";
    echo "---\n";
}

echo "\n✅ Password reset selesai!\n";
echo "Sekarang bisa login dengan:\n";
echo "- admin@rtrw.com / admin123\n";
echo "- rt@rtrw.com / rt123\n";
echo "- warga@rtrw.com / warga123\n";
?>
