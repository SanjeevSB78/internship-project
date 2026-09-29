<?php

header("Content-Type: application/json");

require_once __DIR__ . "/config.php";

$username = $_POST["username"];
$email = $_POST["email"];
$password = $_POST["password"];

// Check whether username or email already exists
$checkSql = "SELECT id FROM users WHERE username = ? OR email = ?";

$checkStmt = $conn->prepare($checkSql);
$checkStmt->bind_param("ss", $username, $email);
$checkStmt->execute();

$result = $checkStmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode([
        "success" => false,
        "message" => "Username or Email already exists!"
    ]);
    $checkStmt->close();
    $conn->close();
    exit;
}

$checkStmt->close();

// Hash password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Insert user
$sql = "INSERT INTO users (username, email, password)
        VALUES (?, ?, ?)";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "sss",
    $username,
    $email,
    $hashedPassword
);

if ($stmt->execute()) {

    // Get the newly created MySQL user ID
    $userId = $conn->insert_id;

    // Connect to MongoDB
    $mongo = new MongoDB\Driver\Manager($mongoUri);

    // Create an empty profile linked to the MySQL user
    $profile = [
        "user_id" => (int)$userId,
        "age" => null,
        "dob" => null,
        "contact" => null
    ];

    $bulk = new MongoDB\Driver\BulkWrite();

    $bulk->insert($profile);

    $mongo->executeBulkWrite(
        "internship_db.profiles",
        $bulk
    );

    echo json_encode([
        "success" => true,
        "message" => "Registration successful"
    ]);

} else {

    echo json_encode([
        "success" => false,
        "message" => "Registration failed"
    ]);
}

$stmt->close();
$conn->close();

?>