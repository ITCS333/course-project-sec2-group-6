<?php
session_start();
header("Content-Type: application/json");

function out($success, $message, $data = null, $code = 200) {
    http_response_code($code);
    echo json_encode([
        "success" => $success,
        "message" => $message,
        "data" => $data
    ]);
    exit;
}

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

// لازم POST فقط
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    out(false, "Method not allowed", null, 405);
}

// قراءة JSON أو POST
$input = json_decode(file_get_contents("php://input"), true);
$email = $input["email"] ?? $_POST["email"] ?? null;
$password = $input["password"] ?? $_POST["password"] ?? null;

// validation
if (!$email || !$password) {
    out(false, "Missing email or password", null, 400);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    out(false, "Invalid email format", null, 400);
}

if (strlen($password) < 8) {
    out(false, "Password must be at least 8 characters", null, 400);
}

// find user
$user = null;
foreach ($users as $u) {
    if ($u["email"] === $email) {
        $user = $u;
        break;
    }
}

if (!$user) {
    out(false, "User not found", null, 404);
}

if ($user["password"] !== $password) {
    out(false, "Wrong password", null, 401);
}

unset($user["password"]);

$_SESSION["user_id"] = $user["id"];
setcookie("session_id", session_id(), time() + 3600, "/");

out(true, "Login successful", $user, 200);
