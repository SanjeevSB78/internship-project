<?php
header("Content-Type: application/json; charset=utf-8");

function respond(int $statusCode, array $data): void
{
    http_response_code($statusCode);
    echo json_encode($data);
    exit;
}

$authHeader = $_SERVER["HTTP_AUTHORIZATION"]
    ?? $_SERVER["REDIRECT_HTTP_AUTHORIZATION"]
    ?? "";

if ($authHeader === "" && function_exists("getallheaders")) {
    foreach (getallheaders() as $name => $value) {
        if (strcasecmp($name, "Authorization") === 0) {
            $authHeader = $value;
            break;
        }
    }
}

if (strncmp($authHeader, "Bearer ", 7) !== 0) {
    respond(401, [
        "success" => false,
        "message" => "No token provided."
    ]);
}

$token = trim(substr($authHeader, 7));

if ($token === "") {
    respond(401, [
        "success" => false,
        "message" => "No token provided."
    ]);
}

$redisHost = getenv("REDIS_HOST");
$redisPort = (int) (getenv("REDIS_PORT") ?: "6379");
$redisPassword = getenv("REDIS_PASSWORD") ?: "";

if (!$redisHost) {
    error_log("REDIS_HOST is not configured.");
    respond(500, [
        "success" => false,
        "message" => "Session service is not configured."
    ]);
}

try {
    $redis = new Redis();
    $redis->connect($redisHost, $redisPort, 5);

    if ($redisPassword !== "") {
        $redis->auth($redisPassword);
    }

    $deleted = $redis->del($token);
    $redis->close();

    respond(200, $deleted
        ? ["success" => true, "message" => "Logged out successfully"]
        : ["success" => false, "message" => "Token not found"]
    );
} catch (Throwable $error) {
    error_log("Logout error: " . $error->getMessage());

    respond(500, [
        "success" => false,
        "message" => "Unable to log out right now."
    ]);
}