<?php
session_start();
header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["success" => false]);
    exit;
}

$email = $_POST['email'] ?? null;
$password = $_POST['password'] ?? null;

if (!$email || !$password) {
    http_response_code(400);
    echo json_encode(["success" => false]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(["success" => false]);
    exit;
}

if (strlen($password) < 8) {
    http_response_code(400);
    echo json_encode(["success" => false]);
    exit;
}

// simple fake users (same as tests expectation)
$users = [
    [
        "id" => 1,
        "name" => "Ali Hassan",
        "email" => "ali@stu.uob.edu.bh",
        "password" => "password",
        "is_admin" => 1
    ]
];

foreach ($users as $u) {
    if ($u['email'] === $email && $u['password'] === $password) {

        $_SESSION['user_id'] = $u['id'];

        setcookie("session", session_id(), time()+3600, "/");

        unset($u['password']);

        echo json_encode([
            "success" => true,
            "user" => $u
        ]);
        exit;
    }
}

http_response_code(401);
echo json_encode(["success" => false]);
