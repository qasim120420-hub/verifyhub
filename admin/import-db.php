<?php
/**
 * Temporary Database Import Script
 * ⚠️ DELETE THIS FILE AFTER IMPORTING
 */
require_once '../config/session.php';

// Simple password protection
$password = $_GET['key'] ?? '';
if ($password !== 'verifyhub_import_2024') {
    die("❌ Access denied. Add ?key=verifyhub_import_2024 to URL.");
}

$sql_file = __DIR__ . '/../database/verifyhub.sql';

if (!file_exists($sql_file)) {
    die("❌ SQL file not found at: " . $sql_file);
}

$sql = file_get_contents($sql_file);

try {
    // Split on semicolon but not inside strings (simple version)
    $statements = array_filter(
        array_map('trim', explode(";\n", $sql)),
        function($s) { return !empty($s) && strpos($s, '--') !== 0; }
    );

    $count = 0;
    foreach ($statements as $stmt) {
        if (!empty($stmt)) {
            try {
                $pdo->exec($stmt);
                $count++;
            } catch (Exception $e) {
                // Skip errors on CREATE IF NOT EXISTS
                if (strpos($e->getMessage(), 'already exists') === false) {
                    echo "⚠️ Statement " . ($count + 1) . " error: " . htmlspecialchars($e->getMessage()) . "<br>";
                }
            }
        }
    }

    echo "✅ <strong>Database imported!</strong> ({$count} statements executed)<br>";
    echo "🌐 <a href='../public/index.php'>Go to Homepage</a><br>";
    echo "🔐 <a href='login.php'>Go to Admin Login</a><br>";
    echo "<br><strong style='color:red;'>⚠️ DELETE THIS FILE (admin/import-db.php) NOW!</strong>";

} catch (Exception $e) {
    echo "❌ Error: " . htmlspecialchars($e->getMessage());
}
?>