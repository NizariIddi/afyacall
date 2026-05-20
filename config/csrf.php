<?php
/**
 * CSRF Protection helper
 * Usage:
 *   Generate token in form: csrf_field()
 *   Validate on POST:       csrf_verify()
 */

if (session_status() === PHP_SESSION_NONE) session_start();

function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token()) . '">';
}

function csrf_verify(): void {
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals(csrf_token(), $token)) {
        http_response_code(403);
        die('<h3 style="font-family:sans-serif;color:#c03434;padding:40px;">403 – Invalid or missing CSRF token. Please go back and try again.</h3>');
    }
    // Rotate token after use
    unset($_SESSION['csrf_token']);
}
