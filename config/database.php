<?php

$host = "localhost";
$user = "root";
$password = "nizari2412@@.!";
$database = "afyacall";

$conn = mysqli_connect($host, $user, $password, $database);

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

?>