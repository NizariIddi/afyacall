<?php
/**
 * sanitize.php — Input sanitization helpers
 * Include once after database.php
 */

/**
 * Sanitize a plain string — strips tags, trims whitespace.
 */
function clean(string $val): string {
    return trim(strip_tags($val));
}

/**
 * Sanitize for SQL via mysqli — escape + strip tags.
 */
function db_clean($conn, string $val): string {
    return mysqli_real_escape_string($conn, clean($val));
}

/**
 * Safe integer cast.
 */
function clean_int($val): int {
    return intval($val);
}

/**
 * Validate and return a date string (Y-m-d), or empty string.
 */
function clean_date(string $val): string {
    $d = DateTime::createFromFormat('Y-m-d', trim($val));
    return ($d && $d->format('Y-m-d') === trim($val)) ? trim($val) : '';
}

/**
 * Validate and return a datetime string (Y-m-d H:i or Y-m-d\TH:i).
 */
function clean_datetime(string $val): string {
    $val = str_replace('T', ' ', trim($val));
    $d = DateTime::createFromFormat('Y-m-d H:i', $val);
    return $d ? $d->format('Y-m-d H:i:s') : '';
}

/**
 * Validate email.
 */
function clean_email(string $val): string {
    $v = filter_var(trim($val), FILTER_VALIDATE_EMAIL);
    return $v ? $v : '';
}

/**
 * Allow only known values from a whitelist.
 */
function clean_enum(string $val, array $allowed, string $default = ''): string {
    return in_array($val, $allowed) ? $val : $default;
}
