<?php
session_start();
header("Content-Type: application/json");

// Fake users database (for tests)
$users = [
    [
        "id" => 1,
        "name" => "Admin User",
        "email" => "admin@example.com",
        "password" => "password123",
        "is_admin" => 1
    ],
    [
        "id" => 2,
        "name" => "Test User",
        "email" => "test@example.com",
        "password" => "password123",
        "is_admin" => 0
    ]
];

function response($success, $message, $data = null, $code = 200) {
    http_response_code($code);
    echo json_encode([
        "success" => $success,
        "message" => $message,
        "data" => $data
    ]);
    exit;
}

// must be POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    response(false, "Method not allowed", null, 405);
}

$email = $_POST["email"] ?? null;
$password = $_POST["password"] ?? null;

// missing fields
if (!$email || !$password) {
    response(false, "Missing email or password", null, 400);
}

// email validation
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    response(false, "Invalid email format", null, 400);
}

// short password
if (strlen($password) < 8) {
    response(false, "Password must be at least 8 characters", null, 400);
}

// find user
$foundUser = null;

foreach ($users as $u) {
    if ($u["email"] === $email) {
        $foundUser = $u;
        break;
    }
}

// unknown user
if (!$foundUser) {
    response(false, "User not found", null, 404);
}

// wrong password
if ($foundUser["password"] !== $password) {
    response(false, "Wrong password", null, 401);
}

// success login
unset($foundUser["password"]);

$_SESSION["user_id"] = $foundUser["id"];

setcookie("session", session_id(), time() + 3600, "/");

response(true, "Login successful", [
    "user" => $foundUser
], 200);
