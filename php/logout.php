<?php
header("Content-Type: application/json");

require_once "config.php";

$headers = getallheaders();
$authHeader = $headers["Authorization"] ?? "";

if (strpos($authHeader, "Bearer ") !== 0) {
    echo json_encode([
        "success" => false,
        "message" => "No token provided"
    ]);
    exit;
}

$token = substr($authHeader, 7);

$redis = new Redis();
$redis->connect($redisHost, $redisPort);
if ($redisPassword) {
    $redis->auth($redisPassword);
}

$deleted = $redis->del($token);

echo json_encode($deleted
    ? ["success" => true, "message" => "Logged out successfully"]
    : ["success" => false, "message" => "Token not found"]
);
?>