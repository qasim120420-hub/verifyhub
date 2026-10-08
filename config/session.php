<?php
// Prevent direct access
if (!defined('VERIFYHUB')) {
    define('VERIFYHUB', true);
}

// Start session with secure settings
if (session_status() === PHP_SESSION_NONE) {
    // Secure session settings
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    
    // HTTPS only in production
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        ini_set('session.cookie_secure', 1);
    }
    
    session_start();
}

// Load database (absolute path)
require_once __DIR__ . '/database.php';

// Load helper functions
require_once __DIR__ . '/../includes/functions.php';

// Load theme settings
$theme = [];
try {
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM theme_settings");
    while($row = $stmt->fetch()) {
        $theme[$row['setting_key']] = $row['setting_value'];
    }
} catch (Exception $e) {
    // Fallback theme if table missing
    $theme = [
        'primary_color' => '#8B5CF6',
        'secondary_color' => '#A855F7',
        'accent_color' => '#EC4899',
        'bg_color' => '#F8F7FC',
        'sidebar_color' => '#0F0C29',
        'text_color' => '#0F0F23',
        'card_color' => '#FFFFFF',
        'border_color' => '#E8E5F5',
        'site_name' => 'VerifyHub',
        'site_tagline' => 'Smart Numbers, Instant Verification.',
        'currency' => 'PKR'
    ];
}
?>