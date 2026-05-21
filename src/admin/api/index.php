<?php
session_start();
header("Content-Type: application/json");

// Always return JSON helper
function respond($success, $message, $data = null, $code = 200) {
    http_response_code($code);
    echo json_encode([
        "success" => $success,
        "message" => $message,
        "data" => $data
    ]);
    exit;
}

// Fake users database (MUST match tests)
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

// ❌ MUST reject non-POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    respond(false, "Method not allowed", null, 405);
}

// Read JSON input (NOT $_POST)
$input = json_decode(file_get_contents("php://input"), true);

$email = $input["email"] ?? null;
$password = $input["password"] ?? null;

// Missing fields
if (!$email || !$password) {
    respond(false, "Missing email or password", null, 400);
}

// Email validation
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    respond(false, "Invalid email format", null, 400);
}

// Password length
if (strlen($password) < 8) {
    respond(false, "Password must be at least 8 characters", null, 400);
}

// Find user
$foundUser = null;

foreach ($users as $u) {
    if ($u["email"] === $email) {
        $foundUser = $u;
        break;
    }
}

// Unknown user
if (!$foundUser) {
    respond(false, "User not found", null, 404);
}

// Wrong password
if ($foundUser["password"] !== $password) {
    respond(false, "Wrong password", null, 401);
}

// SUCCESS LOGIN
unset($foundUser["password"]);

// session must be set
$_SESSION["user_id"] = $foundUser["id"];

// cookie required by tests
setcookie("session_id", session_id(), time() + 3600, "/");

// response must include user directly (important for tests)
respond(true, "Login successful", $foundUser, 200);
