<?php
$host     = getenv("MYSQL_HOST") ?: getenv("MYSQLHOST") ?: "127.0.0.1";
$username = getenv("MYSQL_USER") ?: getenv("MYSQLUSER") ?: "root";
$password = getenv("MYSQL_PASSWORD") ?: getenv("MYSQLPASSWORD") ?: "";
$database = getenv("MYSQL_DATABASE") ?: getenv("MYSQLDATABASE") ?: "internship_db";
$port     = (int)(getenv("MYSQL_PORT") ?: getenv("MYSQLPORT") ?: "3306");

$conn = mysqli_init();
$conn->options(MYSQLI_OPT_CONNECT_TIMEOUT, 10);

$mysqlSsl = filter_var(
    getenv("MYSQL_SSL") ?: "false",
    FILTER_VALIDATE_BOOLEAN
);

$flags = 0;

if ($mysqlSsl) {
    $caCertificate = getenv("MYSQL_SSL_CA")
        ?: "/etc/ssl/certs/ca-certificates.crt";

    $conn->ssl_set(null, null, $caCertificate, null, null);
    $conn->options(MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, true);
    $flags = MYSQLI_CLIENT_SSL;
}

if (!$conn->real_connect(
    $host,
    $username,
    $password,
    $database,
    $port,
    null,
    $flags
)) {
    die("Connection Failed: " . $conn->connect_error);
}
?>