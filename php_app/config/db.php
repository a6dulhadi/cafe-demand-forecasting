<?php
// config/db.php

$host = "127.0.0.1";
$user = "root";
$password = "";
$database = "cafe_forecasting_db";
$port = 3306;

$conn = mysqli_connect($host, $user, $password, $database, $port);

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8mb4");
?>