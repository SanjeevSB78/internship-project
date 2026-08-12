<?php
header("Content-Type: application/json");

require_once "config.php";

$username = $_POST["username"];
$password = $_POST["password"];

$sql = "SELECT * FROM users WHERE username = ?";

$stmt = $conn->prepare($sql);

$stmt->bind_param("s", $username);

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid Username or Password"
    ]);
    exit;
}

$user = $result->fetch_assoc();

if (password_verify($password, $user["password"])) {

    $token = bin2hex(random_bytes(32));

    $redis = new Redis();
    $redis->connect("127.0.0.1", 6379);
    $redis->setEx($token, 3600, $user["id"]);

    echo json_encode([
        "success" => true,
        "token" => $token
    ]);

} else {
    echo json_encode([
        "success" => false,
        "message" => "Invalid Username or Password"
    ]);
}

$stmt->close();
$conn->close();

?>