<?php
/**
 * VerifyHub Database Configuration
 * Supports both local (XAMPP) and Railway deployment
 */

// Railway provides MYSQLHOST, MYSQLUSER, MYSQLPASSWORD, MYSQLDATABASE, MYSQLPORT
// Locally we use XAMPP defaults

define('DB_HOST', getenv('MYSQLHOST') ?: getenv('DB_HOST') ?: 'localhost');
define('DB_USER', getenv('MYSQLUSER') ?: getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('MYSQLPASSWORD') ?: getenv('DB_PASS') ?: '');
define('DB_NAME', getenv('MYSQLDATABASE') ?: getenv('DB_NAME') ?: 'verifyhub');
define('DB_PORT', getenv('MYSQLPORT') ?: getenv('DB_PORT') ?: '3306');

// Optional: Set default timezone
date_default_timezone_set('Asia/Karachi');

try {
    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_TIMEOUT => 10,
    ]);
} catch(PDOException $e) {
    // In production, log error instead of displaying
    if (getenv('RAILWAY_ENVIRONMENT') || getenv('APP_ENV') === 'production') {
        error_log("Database Connection Failed: " . $e->getMessage());
        die("Service temporarily unavailable. Please try again later.");
    } else {
        die("Database Connection Failed: " . $e->getMessage());
    }
}
?>