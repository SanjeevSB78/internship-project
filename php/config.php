<?php

// --- MySQL ---
// Checks docker-compose naming first, then Railway's naming, then falls back to local defaults
$host     = getenv("MYSQL_HOST") ?: getenv("MYSQLHOST") ?: "127.0.0.1";
$username = getenv("MYSQL_USER") ?: getenv("MYSQLUSER") ?: "root";
$password = getenv("MYSQL_PASSWORD") ?: getenv("MYSQLPASSWORD") ?: "";
$database = getenv("MYSQL_DATABASE") ?: getenv("MYSQLDATABASE") ?: "internship_db";
$port     = getenv("MYSQL_PORT") ?: getenv("MYSQLPORT") ?: "3306";

$conn = new mysqli($host, $username, $password, $database, (int)$port);

if ($conn->connect_error) {
    die("Connection Failed: " . $conn->connect_error);
}

// --- Redis ---
$redisHost     = getenv("REDIS_HOST") ?: getenv("REDISHOST") ?: "127.0.0.1";
$redisPort     = (int)(getenv("REDIS_PORT") ?: getenv("REDISPORT") ?: 6379);
$redisPassword = getenv("REDIS_PASSWORD") ?: getenv("REDISPASSWORD") ?: null;

// --- MongoDB ---
// Railway may provide a full connection string, while docker-compose provides host and port separately.
$mongoUri = trim((string)(getenv("MONGO_URI") ?: getenv("MONGO_URL") ?: ""));

if ($mongoUri === "") {
    $mongoHost = trim((string)(getenv("MONGO_HOST") ?: ""));
    $mongoPort = trim((string)(getenv("MONGO_PORT") ?: "27017"));

    if ($mongoHost === "") {
        $mongoHost = "127.0.0.1";
    }

    $mongoUri = "mongodb://" . $mongoHost . ":" . $mongoPort;
}

?>