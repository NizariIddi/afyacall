
<?php

// AfyaCall database connection.
// Uses environment variables when available (e.g. GitHub Actions).
// Otherwise, falls back to local Fedora MariaDB settings.

$host = getenv('DB_HOST') ?: '127.0.0.1';
$user = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASSWORD') !== false
    ? getenv('DB_PASSWORD')
    : '';
$database = getenv('DB_NAME') ?: 'afyacall';
$port = (int) (getenv('DB_PORT') ?: 3307);

$conn = mysqli_connect(
    $host,
    $user,
    $password,
    $database,
    $port
);

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8mb4");

?>
