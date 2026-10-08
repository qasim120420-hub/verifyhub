<?php
/**
 * VerifyHub Helper Functions
 */

// Prevent direct access
if (!defined('VERIFYHUB')) {
    define('VERIFYHUB', true);
}

function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data ?? '')), ENT_QUOTES, 'UTF-8');
}

function redirect($url) {
    // Support absolute URLs
    if (strpos($url, 'http') !== 0 && strpos($url, '/') !== 0) {
        header("Location: " . $url);
    } else {
        header("Location: " . $url);
    }
    exit();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function generateOTP($length = 6) {
    return str_pad(random_int(0, 999999), $length, '0', STR_PAD_LEFT);
}

function getWalletBalance($pdo, $user_id) {
    try {
        $stmt = $pdo->prepare("SELECT balance FROM wallets WHERE user_id = ?");
        $stmt->execute([$user_id]);
        return (float)($stmt->fetchColumn() ?? 0.00);
    } catch (Exception $e) {
        return 0.00;
    }
}

function addNotification($pdo, $user_id, $title, $message) {
    try {
        $stmt = $pdo->prepare("INSERT INTO notifications (user_id, title, message) VALUES (?, ?, ?)");
        return $stmt->execute([$user_id, $title, $message]);
    } catch (Exception $e) {
        return false;
    }
}

function getSiteUrl() {
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    return $protocol . '://' . $host;
}

function formatCurrency($amount, $currency = 'PKR') {
    return $currency . ' ' . number_format((float)$amount, 2);
}

function timeAgo($timestamp) {
    $diff = time() - strtotime($timestamp);
    if ($diff < 60) return $diff . 's ago';
    if ($diff < 3600) return floor($diff / 60) . 'm ago';
    if ($diff < 86400) return floor($diff / 3600) . 'h ago';
    return floor($diff / 86400) . 'd ago';
}
?>