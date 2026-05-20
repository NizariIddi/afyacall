<?php
ini_set('session.cookie_httponly', 1);
if (session_status() === PHP_SESSION_NONE) session_start();

// Log the logout before destroying session
if (isset($_SESSION['admin_id'])) {
    include("../config/database.php");
    include("../config/activity_log.php");
    log_activity($conn, 'Logged out', 'auth', intval($_SESSION['admin_id']));
}

$_SESSION = [];
if (ini_get("session.use_cookies")) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $p["path"], $p["domain"], $p["secure"], $p["httponly"]);
}
session_destroy();
header("Location: login.php?logged_out=1");
exit();
