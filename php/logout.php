<?php

header("Content-Type: application/json");

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
$redis->connect("127.0.0.1", 6379);


$deleted = $redis->del($token);

if ($deleted) {
    echo json_encode([
        "success" => true,
        "message" => "Logged out successfully"
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Token not found"
    ]);
}

?>