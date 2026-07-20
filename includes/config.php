<?php
/**
 * Nadics Digital Solution — Configuration
 * Database connection, constants, and session setup
 */

// ─── Error Reporting (disable in production) ────────────────────────
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// ─── Session ────────────────────────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ─── Database Configuration ─────────────────────────────────────────
define('DB_HOST', 'localhost');
define('DB_NAME', 'nadics_portfolio');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

/**
 * Get PDO database connection
 * @return PDO|null
 */
function getDB(): ?PDO {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            return null;
        }
    }
    return $pdo;
}

// ─── Load Settings from Database ────────────────────────────────────
$db_settings = [];
$db = getDB();
if ($db) {
    try {
        $stmt = $db->query("SELECT setting_key, setting_value FROM site_settings");
        if ($stmt) {
            $db_settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        }
    } catch (Exception $e) {
        // Table or database doesn't exist yet, ignore and use defaults
    }
}

// ─── Site Constants ─────────────────────────────────────────────────
define('SITE_NAME', $db_settings['site_name'] ?? 'Nadics Digital Solution');
define('SITE_TAGLINE', $db_settings['site_tagline'] ?? 'Transforming Ideas Into Digital Reality');
// Auto-detect the site URL so it works both on local XAMPP and in production
$__host = $_SERVER['HTTP_HOST'] ?? 'localhost';
if (strpos($__host, 'localhost') !== false || strpos($__host, '127.0.0.1') !== false) {
    // Local development (XAMPP) — app lives in the /portfolio subfolder
    define('SITE_URL', 'http://' . $__host . '/portfolio');
} else {
    // Production — app is served from the domain root over HTTPS
    $__https = (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https')
        || (($_SERVER['SERVER_PORT'] ?? '') == 443);
    define('SITE_URL', ($__https ? 'https' : 'http') . '://' . $__host);
}
define('SITE_EMAIL', $db_settings['site_email'] ?? 'info@nadicsdigital.com');
define('SITE_PHONE', $db_settings['site_phone'] ?? '+233 24 000 0000');
define('SITE_ADDRESS', $db_settings['site_address'] ?? 'Accra, Ghana');
define('SITE_HOURS', $db_settings['business_hours'] ?? 'Mon – Fri: 8:00 AM – 6:00 PM | Sat: 9:00 AM – 2:00 PM');

// Social links
define('FACEBOOK_URL', $db_settings['facebook_url'] ?? '#');
define('TWITTER_URL', $db_settings['twitter_url'] ?? '#');
define('INSTAGRAM_URL', $db_settings['instagram_url'] ?? '#');
define('LINKEDIN_URL', $db_settings['linkedin_url'] ?? '#');
define('TIKTOK_URL', $db_settings['tiktok_url'] ?? '#');

// Stats
define('STATS_PROJECTS', $db_settings['stats_projects'] ?? '120');
define('STATS_CLIENTS', $db_settings['stats_clients'] ?? '85');
define('STATS_YEARS', $db_settings['stats_years'] ?? '5');
define('STATS_SERVICES', $db_settings['stats_services'] ?? '7');

