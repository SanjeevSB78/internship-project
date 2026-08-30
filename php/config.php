<?php

// --- MySQL ---
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
$mongoFullUrl = getenv("MONGO_URL");

if ($mongoFullUrl) {
    $mongoUri = $mongoFullUrl;
} else {
    $mongoHost = getenv("MONGO_HOST") ?: "127.0.0.1";
    $mongoPort = getenv("MONGO_PORT") ?: "27017";
    $mongoUri = "mongodb://" . $mongoHost . ":" . $mongoPort;
}
?>