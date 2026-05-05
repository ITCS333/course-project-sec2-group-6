<?php

session_start();

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// allow only POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "message" => "Method not allowed"
    ]);
    exit;
}

require_once "db.php"; // فيه getDBConnection()

try {

    $pdo = getDBConnection();

    // read input
    $input = json_decode(file_get_contents("php://input"), true);

    if (!isset($input['email']) || !isset($input['password'])) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "Missing email or password"
        ]);
        exit;
    }

    $email = trim($input['email']);
    $password = $input['password'];

    // validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "Invalid email format"
        ]);
        exit;
    }

    if (strlen($password) < 8) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "Password too short"
        ]);
        exit;
    }

    // query user
    $stmt = $pdo->prepare("SELECT id, name, email, password, is_admin FROM users WHERE email = ?");
    $stmt->execute([$email]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($password, $user['password'])) {
        http_response_code(401);
        echo json_encode([
            "success" => false,
            "message" => "Invalid email or password"
        ]);
        exit;
    }

    // session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['is_admin'] = $user['is_admin'];
    $_SESSION['logged_in'] = true;

    // success response
    echo json_encode([
        "success" => true,
        "message" => "Login successful",
        "user" => [
            "id" => $user['id'],
            "name" => $user['name'],
            "email" => $user['email'],
            "is_admin" => $user['is_admin']
        ]
    ]);

    exit;

} catch (PDOException $e) {

    error_log($e->getMessage());

    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Server error"
    ]);

    exit;
}
