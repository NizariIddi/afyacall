<?php
/**
 * session.php — Secure session + timeout + role guard
 *
 * Usage at top of every protected page:
 *   include("../config/session.php");
 *
 * Optional role guard (add after include):
 *   require_role(['admin']);
 *   require_role(['admin','doctor']);
 */

// ── Secure session settings (must be before session_start) ──────────────
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.use_strict_mode', 1);
// ini_set('session.cookie_secure', 1); // Uncomment on HTTPS

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ── Regenerate session ID periodically (every 15 min) to prevent fixation
if (!isset($_SESSION['last_regen']) || time() - $_SESSION['last_regen'] > 900) {
    session_regenerate_id(true);
    $_SESSION['last_regen'] = time();
}

// ── Timeout: 30 minutes of inactivity ───────────────────────────────────
define('SESSION_TIMEOUT', 1800); // seconds

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > SESSION_TIMEOUT) {
    // Expired — wipe and redirect
    session_unset();
    session_destroy();
    header("Location: ../auth/login.php?timeout=1");
    exit();
}
$_SESSION['last_activity'] = time();

// ── Authentication check ─────────────────────────────────────────────────
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// ── Role-based access helper ─────────────────────────────────────────────
function require_role(array $allowed_roles): void {
    $role = strtolower($_SESSION['admin_role'] ?? '');
    $allowed = array_map('strtolower', $allowed_roles);
    if (!in_array($role, $allowed)) {
        http_response_code(403);
        include(__DIR__ . '/../includes/403.php');
        exit();
    }
}

function has_role(array $roles): bool {
    $role = strtolower($_SESSION['admin_role'] ?? '');
    return in_array($role, array_map('strtolower', $roles));
}

function can(string $action): bool {
    $role = strtolower($_SESSION['admin_role'] ?? '');
    $permissions = [
        'admin'  => ['view_patients','add_patient','edit_patient','delete_patient',
                     'view_records','add_record','edit_record','delete_record',
                     'view_appointments','add_appointment','manage_appointments',
                     'view_logs','view_analytics','manage_settings',
                     'manage_users','view_activity_log'],
        'doctor' => ['view_patients','add_patient','edit_patient',
                     'view_records','add_record','edit_record',
                     'view_appointments','add_appointment','manage_appointments',
                     'view_logs','view_analytics'],
        'nurse'  => ['view_patients','add_patient',
                     'view_records','add_record',
                     'view_appointments','add_appointment',
                     'view_logs'],
    ];
    return in_array($action, $permissions[$role] ?? []);
}
