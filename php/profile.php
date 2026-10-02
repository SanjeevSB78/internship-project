<?php

header("Content-Type: application/json");

require_once __DIR__ . "/config.php";

function respond(int $statusCode, array $payload): void
{
    http_response_code($statusCode);
    echo json_encode($payload);
    exit;
}

// Authenticate the request using its Redis token.
$headers = getallheaders();
$authHeader = $headers["Authorization"] ?? "";

if (strpos($authHeader, "Bearer ") !== 0) {
    respond(401, [
        "success" => false,
        "message" => "Unauthorized"
    ]);
}

$token = substr($authHeader, 7);

try {
    $redis = new Redis();
    $redisHost = getenv("REDIS_HOST");
    $redisPortValue = getenv("REDIS_PORT");
    $redisPort = ($redisPortValue !== false && $redisPortValue !== "")
        ? (int) $redisPortValue
        : 6379;
    $redisPassword = getenv("REDIS_PASSWORD");

    if (!$redisHost) {
        error_log("REDIS_HOST is not configured.");
        echo json_encode([
            "success" => false,
            "message" => "Session service is not configured."
        ]);
        exit;
    }
    $redis->connect($redisHost, $redisPort, 5);

    if ($redisPassword) {
        $redis->auth($redisPassword);
    }

    $userId = $redis->get($token);
    $redis->close();
} catch (Throwable $error) {
    respond(500, [
        "success" => false,
        "message" => "Unable to verify the login session."
    ]);
}

if ($userId === false) {
    respond(401, [
        "success" => false,
        "message" => "Invalid or expired token"
    ]);
}

try {
    $mongo = new MongoDB\Driver\Manager($mongoUri);
} catch (Throwable $error) {
    respond(500, [
        "success" => false,
        "message" => "Unable to connect to the profile database."
    ]);
}

// Save profile.
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $ageInput = $_POST["age"] ?? "";
    $dobInput = $_POST["dob"] ?? "";
    $contactInput = $_POST["contact"] ?? "";

    $ageInput = trim((string)$ageInput);
    $dobInput = trim((string)$dobInput);
    $contactInput = trim((string)$contactInput);

    // Age is optional, but when provided it must be a whole number from 0 to 120.
    if ($ageInput !== "") {
        if (!preg_match('/^\d+$/', $ageInput)) {
            respond(400, [
                "success" => false,
                "message" => "Age must be a whole number between 0 and 120."
            ]);
        }

        $age = (int)$ageInput;

        if ($age < 0 || $age > 120) {
            respond(400, [
                "success" => false,
                "message" => "Age must be a whole number between 0 and 120."
            ]);
        }
    } else {
        $age = null;
    }

    $dob = $dobInput === "" ? null : $dobInput;

    if ($dob !== null) {
        $birthDate = DateTime::createFromFormat("!Y-m-d", $dob);
        $dateErrors = DateTime::getLastErrors();

        $invalidDate =
            !$birthDate ||
            ($dateErrors !== false &&
                ($dateErrors["warning_count"] > 0 || $dateErrors["error_count"] > 0)) ||
            ($birthDate && $birthDate->format("Y-m-d") !== $dob);

        if ($invalidDate || $birthDate > new DateTime("today")) {
            respond(400, [
                "success" => false,
                "message" => "Enter a valid date of birth that is not in the future."
            ]);
        }

        // DOB is authoritative: calculate and save the matching age.
        $age = $birthDate->diff(new DateTime("today"))->y;

        if ($age > 120) {
            respond(400, [
                "success" => false,
                "message" => "Age cannot exceed 120."
            ]);
        }
    }

    $contact = $contactInput === "" ? null : $contactInput;

    try {
        $bulk = new MongoDB\Driver\BulkWrite();

        $bulk->update(
            ["user_id" => (int)$userId],
            [
                '$set' => [
                    "age" => $age,
                    "dob" => $dob,
                    "contact" => $contact
                ]
            ],
            [
                "multi" => false,
                "upsert" => true
            ]
        );

        $mongo->executeBulkWrite("internship_db.profiles", $bulk);
    } catch (Throwable $error) {
        respond(500, [
            "success" => false,
            "message" => "Unable to save the profile."
        ]);
    }

    respond(200, [
        "success" => true,
        "message" => "Profile updated successfully"
    ]);
}

// Load profile.
if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    respond(405, [
        "success" => false,
        "message" => "Method not allowed"
    ]);
}

try {
    $filter = [
        "user_id" => (int)$userId
    ];

    $query = new MongoDB\Driver\Query($filter);
    $cursor = $mongo->executeQuery("internship_db.profiles", $query);
    $profiles = $cursor->toArray();
} catch (Throwable $error) {
    respond(500, [
        "success" => false,
        "message" => "Unable to load the profile."
    ]);
}

if (count($profiles) === 0) {
    respond(404, [
        "success" => false,
        "message" => "Profile not found"
    ]);
}

respond(200, [
    "success" => true,
    "profile" => $profiles[0]
]);
?>