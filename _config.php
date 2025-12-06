<?php
$host     = "localhost"; // Database Host
$user     = "root"; // Database Username
$password = "root"; // Database's user Password
$database = "fablog_3.3.2"; // Database Name

$phpblog_version = "3.3.2"; // PHPBlog Version
$admin_version = "4.2.0"; // Admin Version

$connect = new mysqli($host, $user, $password, $database);

// Checking Connection
if (mysqli_connect_errno()) {
    printf("Database connection failed: %s\n", mysqli_connect_error());
    exit();
}

mysqli_set_charset($connect, "utf8mb4");

?>