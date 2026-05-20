<?php
/**
 * activity_log.php — Audit trail helper
 *
 * Usage:
 *   log_activity($conn, 'Added patient', 'patients', $new_id);
 */

function log_activity($conn, string $action, string $module = '', int $record_id = 0): void {
    if (!isset($_SESSION['admin_id'])) return;

    $user_id   = intval($_SESSION['admin_id']);
    $user_name = mysqli_real_escape_string($conn, $_SESSION['admin_name'] ?? '');
    $user_role = mysqli_real_escape_string($conn, $_SESSION['admin_role'] ?? '');
    $action    = mysqli_real_escape_string($conn, $action);
    $module    = mysqli_real_escape_string($conn, $module);
    $ip        = mysqli_real_escape_string($conn, $_SERVER['REMOTE_ADDR'] ?? '');
    $ua        = mysqli_real_escape_string($conn, substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255));

    mysqli_query($conn, "
        INSERT INTO activity_logs (user_id, user_name, user_role, action, module, record_id, ip_address, user_agent, created_at)
        VALUES ('$user_id','$user_name','$user_role','$action','$module','$record_id','$ip','$ua',NOW())
    ");
}
