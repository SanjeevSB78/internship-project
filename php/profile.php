<?php

header("Content-Type: application/json");

$headers = getallheaders();
$authHeader = $headers["Authorization"] ?? "";

if (strpos($authHeader, "Bearer ") !== 0) {
    echo json_encode([
        "success" => false,
        "message" => "Unauthorized"
    ]);
    exit;
}

$token = substr($authHeader, 7);

$redis = new Redis();
$redis->connect("127.0.0.1", 6379);

$userId = $redis->get($token);

if ($userId === false) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid or expired token"
    ]);
    exit;
}

$mongo = new MongoDB\Driver\Manager(
    "mongodb://127.0.0.1:27017"
);

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $age = $_POST["age"] ?? null;
    $dob = $_POST["dob"] ?? null;
    $contact = $_POST["contact"] ?? null;

    // Treat empty strings as "not provided" so we store null instead of 0/""
    $age = ($age === null || $age === "") ? null : (int)$age;
    $dob = ($dob === null || $dob === "") ? null : $dob;
    $contact = ($contact === null || $contact === "") ? null : $contact;

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

    $mongo->executeBulkWrite(
        "internship_db.profiles",
        $bulk
    );

    echo json_encode([
        "success" => true,
        "message" => "Profile updated successfully"
    ]);

    exit;
}

$filter = [
    "user_id" => (int)$userId
];

$query = new MongoDB\Driver\Query($filter);

$cursor = $mongo->executeQuery(
    "internship_db.profiles",
    $query
);

$profiles = $cursor->toArray();

if (count($profiles) === 0) {

    echo json_encode([
        "success" => false,
        "message" => "Profile not found"
    ]);

    exit;
}

echo json_encode([
    "success" => true,
    "profile" => $profiles[0]
]);

?>