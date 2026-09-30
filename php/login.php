<?php
header("Content-Type: application/json; charset=utf-8");

function respond(int $statusCode, array $data): void
{
    http_response_code($statusCode);
    echo json_encode($data);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    respond(405, [
        "success" => false,
        "message" => "Method not allowed."
    ]);
}

$username = $_POST["username"] ?? "";
$loginPassword = $_POST["password"] ?? "";

if (
    !is_string($username) ||
    !is_string($loginPassword) ||
    $username === "" ||
    $loginPassword === ""
) {
    respond(400, [
        "success" => false,
        "message" => "Username and password are required."
    ]);
}

try {
    require_once __DIR__ . "/config.php";

    $stmt = $conn->prepare("SELECT id, password FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();

    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user || !password_verify($loginPassword, $user["password"])) {
        $stmt->close();
        $conn->close();

        respond(401, [
            "success" => false,
            "message" => "Invalid Username or Password"
        ]);
    }

    $redisHost = getenv("REDIS_HOST");
    $redisPortValue = getenv("REDIS_PORT");
    $redisPort = ($redisPortValue !== false && $redisPortValue !== "")
        ? (int) $redisPortValue
        : 6379;
    $redisPassword = getenv("REDIS_PASSWORD");

    if (!$redisHost) {
        error_log("REDIS_HOST is not configured.");
        respond(500, [
            "success" => false,
            "message" => "Login service is not configured."
        ]);
    }

    $redis = new Redis();
    $redis->connect($redisHost, $redisPort, 5);

    if ($redisPassword !== false && $redisPassword !== "") {
        $redis->auth($redisPassword);
    }

    $token = bin2hex(random_bytes(32));
    $saved = $redis->setEx($token, 3600, (string) $user["id"]);

    $redis->close();
    $stmt->close();
    $conn->close();

    if (!$saved) {
        throw new RuntimeException("Could not save login token to Redis.");
    }

    respond(200, [
        "success" => true,
        "token" => $token
    ]);
} catch (Throwable $error) {
    error_log("Login error: " . $error->getMessage());

    respond(500, [
        "success" => false,
        "message" => "Unable to log in right now. Please try again."
    ]);
}