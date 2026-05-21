<?php
session_start();
header("Content-Type: application/json");

// Mock users database
$users = [
    [
        "id" => 1,
        "name" => "Test User",
        "email" => "test@example.com",
        "password" => "password123",
        "is_admin" => 0
    ],
    [
        "id" => 2,
        "name" => "Admin User",
        "email" => "admin@example.com",
        "password" => "admin12345",
        "is_admin" => 1
    ]
];

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "message" => "Method Not Allowed"
    ]);
    exit;
}

$email = $_POST["email"] ?? "";
$password = $_POST["password"] ?? "";

// validation
if ($email === "" || $password === "") {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Missing fields"
    ]);
    exit;
}

// email format check
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Invalid email format"
    ]);
    exit;
}

// password length
if (strlen($password) < 8) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Password too short"
    ]);
    exit;
}

// find user
$userFound = null;
foreach ($users as $u) {
    if ($u["email"] === $email) {
        $userFound = $u;
        break;
    }
}

if (!$userFound) {
    http_response_code(404);
    echo json_encode([
        "success" => false,
        "message" => "User not found"
    ]);
    exit;
}

// check password
if ($userFound["password"] !== $password) {
    http_response_code(401);
    echo json_encode([
        "success" => false,
        "message" => "Wrong password"
    ]);
    exit;
}

// success login
$_SESSION["user_id"] = $userFound["id"];

echo json_encode([
    "success" => true,
    "user" => [
        "id" => $userFound["id"],
        "name" => $userFound["name"],
        "email" => $userFound["email"],
        "is_admin" => $userFound["is_admin"]
    ]
]);
exit;
