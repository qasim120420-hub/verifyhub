<?php
require_once 'config/database.php';

// Naya hash banayein
$new_password = 'admin123';
$new_hash = password_hash($new_password, PASSWORD_BCRYPT);

// Database mein update karein
$stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
$stmt->execute([$new_hash, 'admin@verifyhub.com']);

echo "<h2 style='color:green;font-family:Arial;'>✅ Admin Password Fixed!</h2>";
echo "<p style='font-family:Arial;'><strong>Email:</strong> admin@verifyhub.com</p>";
echo "<p style='font-family:Arial;'><strong>Password:</strong> admin123</p>";
echo "<p style='font-family:Arial;'><strong>New Hash:</strong> " . htmlspecialchars($new_hash) . "</p>";
echo "<hr>";
echo "<p style='font-family:Arial;'><a href='admin/login.php' style='background:#6D28D9;color:#fff;padding:10px 20px;text-decoration:none;border-radius:6px;'>→ Admin Login Page</a></p>";
echo "<p style='color:red;font-family:Arial;'><strong>⚠️ Is file ko delete kar dein use ke baad!</strong></p>";
?>