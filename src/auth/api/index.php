<?php
session_start();
header("Content-Type: application/json");

require_once __DIR__ . "/../../config/db.php";
$pdo = getDBConnection();

$method = $_SERVER['REQUEST_METHOD'];

if ($method !== "POST") {
    http_response_code(405);
    echo json_encode(["success" => false]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data["email"]) || !isset($data["password"])) {
    http_response_code(400);
    echo json_encode(["success" => false]);
    exit;
}

$email = $data["email"];
$password = $data["password"];

$stmt = $pdo->prepare("SELECT id,name,email,password,is_admin FROM users WHERE email=?");
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || !password_verify($password, $user["password"])) {
    http_response_code(401);
    echo json_encode(["success" => false]);
    exit;
}

// SESSION
$_SESSION["user_id"] = $user["id"];
$_SESSION["user_name"] = $user["name"];
$_SESSION["user_email"] = $user["email"];
$_SESSION["is_admin"] = $user["is_admin"];

echo json_encode([
    "success" => true,
    "user" => [
        "id" => $user["id"],
        "name" => $user["name"],
        "email" => $user["email"],
        "is_admin" => $user["is_admin"]
    ]
]);
