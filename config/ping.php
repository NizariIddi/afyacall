<?php
// Lightweight keepalive — resets session last_activity
ini_set('session.cookie_httponly', 1);
if (session_status() === PHP_SESSION_NONE) session_start();
if (isset($_SESSION['admin_id'])) {
    $_SESSION['last_activity'] = time();
    http_response_code(204);
} else {
    http_response_code(401);
}
