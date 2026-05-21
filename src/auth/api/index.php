<?php
session_start();
header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["success"=>false,"message"=>"Method not allowed"]);
    exit;
}

$email = $_POST["email"] ?? null;
$password = $_POST["password"] ?? null;

if (!$email || !$password) {
    http_response_code(400);
    echo json_encode(["success"=>false,"message"=>"Missing fields"]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(["success"=>false,"message"=>"Invalid email"]);
    exit;
}

if (strlen($password) < 8) {
    http_response_code(400);
    echo json_encode(["success"=>false,"message"=>"Password too short"]);
    exit;
}

// fake users (for tests)
$users = [
    [
        "id"=>1,
        "name"=>"Admin User",
        "email"=>"admin@example.com",
        "password"=>"password123",
        "is_admin"=>1
    ],
    [
        "id"=>2,
        "name"=>"Test User",
        "email"=>"test@example.com",
        "password"=>"password123",
        "is_admin"=>0
    ]
];

$user = null;

foreach ($users as $u) {
    if ($u["email"] === $email) {
        $user = $u;
        break;
    }
}

if (!$user) {
    http_response_code(404);
    echo json_encode(["success"=>false,"message"=>"User not found"]);
    exit;
}

if ($user["password"] !== $password) {
    http_response_code(401);
    echo json_encode(["success"=>false,"message"=>"Wrong password"]);
    exit;
}

unset($user["password"]);

$_SESSION["user_id"] = $user["id"];

setcookie("session", session_id(), time()+3600, "/");

echo json_encode([
    "success"=>true,
    "user"=>$user
]);
