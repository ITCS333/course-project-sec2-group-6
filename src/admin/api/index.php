<?php
header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode([
        "success" => false,
        "message" => "Only POST allowed"
    ]);
    exit;
}

$email = $_POST["email"] ?? null;
$password = $_POST["password"] ?? null;

if (!$email || !$password) {
    echo json_encode([
        "success" => false,
        "message" => "Missing fields"
    ]);
    exit;
}

echo json_encode([
    "success" => true,
    "user" => [
        "id" => 1,
        "name" => "Test User",
        "email" => $email,
        "is_admin" => 0
    ]
]);
