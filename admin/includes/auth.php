<?php
/**
 * Nadics Digital Solution — Admin Authentication
 * Session-based auth with hardcoded fallback credentials
 */

require_once __DIR__ . '/../../includes/functions.php';

// ─── Admin Constants ────────────────────────────────────────────────
define('ADMIN_EMAIL', 'admin@nadicsdigital.com');
define('ADMIN_PASSWORD', '@123NadicsDigital');
define('ADMIN_URL', SITE_URL . '/admin');

// ─── Auth Functions ─────────────────────────────────────────────────

/**
 * Check if admin is currently logged in
 */
function isAdminLoggedIn(): bool {
    return !empty($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

/**
 * Require admin login — redirects to login page if not authenticated
 */
function requireAdmin(): void {
    if (!isAdminLoggedIn()) {
        header('Location: ' . ADMIN_URL . '/index.php');
        exit;
    }
}

/**
 * Attempt admin login
 */
function adminLogin(string $email, string $password): bool {
    // First try database
    $db = getDB();
    if ($db) {
        try {
            $stmt = $db->prepare("SELECT * FROM admins WHERE email = :email LIMIT 1");
            $stmt->execute([':email' => $email]);
            $admin = $stmt->fetch();

            if ($admin && password_verify($password, $admin['password'])) {
                setAdminSession($admin['id'], $admin['full_name'], $admin['email']);
                // Update last login
                $db->prepare("UPDATE admins SET last_login = NOW() WHERE id = :id")->execute([':id' => $admin['id']]);
                logActivity($admin['id'], 'Logged in');
                return true;
            }
        } catch (PDOException $e) {
            error_log("Admin login DB error: " . $e->getMessage());
        }
    }

    // Fallback: hardcoded credentials
    if ($email === ADMIN_EMAIL && $password === ADMIN_PASSWORD) {
        setAdminSession(1, 'Admin', ADMIN_EMAIL);
        return true;
    }

    return false;
}

/**
 * Set admin session data
 */
function setAdminSession(int $id, string $name, string $email): void {
    $_SESSION['admin_logged_in'] = true;
    $_SESSION['admin_id'] = $id;
    $_SESSION['admin_name'] = $name;
    $_SESSION['admin_email'] = $email;
    $_SESSION['admin_login_time'] = time();

    // Regenerate session ID for security
    session_regenerate_id(true);
}

/**
 * Logout admin
 */
function adminLogout(): void {
    if (isset($_SESSION['admin_id'])) {
        logActivity($_SESSION['admin_id'], 'Logged out');
    }
    $_SESSION = [];
    session_destroy();
}

/**
 * Get admin name for display
 */
function getAdminName(): string {
    return $_SESSION['admin_name'] ?? 'Admin';
}

/**
 * Get admin email
 */
function getAdminEmail(): string {
    return $_SESSION['admin_email'] ?? ADMIN_EMAIL;
}

// ─── Activity Logging ───────────────────────────────────────────────

/**
 * Log admin activity
 */
function logActivity(int $adminId, string $action, ?string $entityType = null, ?int $entityId = null, ?string $details = null): void {
    $db = getDB();
    if (!$db) return;

    try {
        $stmt = $db->prepare("
            INSERT INTO activity_log (admin_id, action, entity_type, entity_id, details)
            VALUES (:admin_id, :action, :entity_type, :entity_id, :details)
        ");
        $stmt->execute([
            ':admin_id'    => $adminId,
            ':action'      => $action,
            ':entity_type' => $entityType,
            ':entity_id'   => $entityId,
            ':details'     => $details,
        ]);
    } catch (PDOException $e) {
        error_log("Activity log error: " . $e->getMessage());
    }
}

/**
 * Get recent activity log entries
 */
function getRecentActivity(int $limit = 10): array {
    $db = getDB();
    if (!$db) return [];

    try {
        $stmt = $db->prepare("SELECT * FROM activity_log ORDER BY created_at DESC LIMIT :limit");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Activity fetch error: " . $e->getMessage());
        return [];
    }
}

// ─── Dashboard Stats ────────────────────────────────────────────────

/**
 * Get dashboard statistics
 */
function getDashboardStats(): array {
    $db = getDB();
    $stats = [
        'total_contacts' => 0,
        'unread_contacts' => 0,
        'total_portfolio' => 0,
        'total_testimonials' => 0,
        'recent_contacts' => [],
    ];

    if (!$db) return $stats;

    try {
        $stats['total_contacts'] = (int) $db->query("SELECT COUNT(*) FROM contacts")->fetchColumn();
        $stats['unread_contacts'] = (int) $db->query("SELECT COUNT(*) FROM contacts WHERE is_read = 0")->fetchColumn();
        $stats['total_portfolio'] = (int) $db->query("SELECT COUNT(*) FROM portfolio_items")->fetchColumn();

        // Testimonials table may not exist yet
        try {
            $stats['total_testimonials'] = (int) $db->query("SELECT COUNT(*) FROM testimonials")->fetchColumn();
        } catch (PDOException $e) {
            $stats['total_testimonials'] = 0;
        }

        $stats['recent_contacts'] = $db->query("SELECT * FROM contacts ORDER BY created_at DESC LIMIT 5")->fetchAll();
    } catch (PDOException $e) {
        error_log("Dashboard stats error: " . $e->getMessage());
    }

    return $stats;
}
