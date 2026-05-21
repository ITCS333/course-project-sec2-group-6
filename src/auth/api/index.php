<?php
session_start();
header("Content-Type: application/json");

require_once __DIR__ . "/../../config/db.php";

try {

    $pdo = getDBConnection();

    // ❗ لازم يرفض أي غير POST
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        http_response_code(405);
        echo json_encode(["success" => false]);
        exit;
    }

    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data || !isset($data["email"]) || !isset($data["password"])) {
        http_response_code(400);
        echo json_encode(["success" => false]);
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email=?");
    $stmt->execute([$data["email"]]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($data["password"], $user["password"])) {
        http_response_code(401);
        echo json_encode(["success" => false]);
        exit;
    }

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

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(["success" => false]);
}
