<?php
/**
 * Nadics Digital Solution — Admin Session Keep-alive / Status
 *
 * POST  → the admin is active in the page: extend the session.
 * GET   → only report how long the session has left (does not extend it).
 *
 * Responds with JSON: { "active": bool, "seconds_left": int }
 */

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    define('ADMIN_SESSION_PASSIVE', true);
}

require_once __DIR__ . '/includes/auth.php';

header('Content-Type: application/json');
header('Cache-Control: no-store');

if (!isAdminLoggedIn()) {
    http_response_code(401);
    echo json_encode(['active' => false, 'seconds_left' => 0]);
    exit;
}

echo json_encode(['active' => true, 'seconds_left' => adminSessionSecondsLeft()]);
