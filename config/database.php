<?php

// AfyaCall database connection.
// NOTE: The system MariaDB root account uses unix_socket auth and is not
// reachable from this (non-root) OS user, so the app connects to a local
// MariaDB instance running on 127.0.0.1:3307 (started separately as the
// current user). See setup notes for how that instance was created.
$host = "127.0.0.1";
$user = "root";
$password = "";
$database = "afyacall";
$port = 3307;

$conn = mysqli_connect($host, $user, $password, $database, $port);

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

?>