<?php
// dbconfig.php

// Database configuration constants
// Ensure these values match your docker-compose.yml MySQL service environment variables
define('DB_HOST', 'mysql'); // This is the service name for your MySQL container in docker-compose.yml
define('DB_NAME', 'developmentdb'); // Database name
define('DB_USER', 'root'); // Your database username (from docker-compose.yml)
define('DB_PASS', 'secret123'); // Your database password (from docker-compose.yml)

$pdoOptions = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
    PDO::ATTR_TIMEOUT            => 5, // Connection timeout in seconds
];

// DSN (Data Source Name) string for PDO
$dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";