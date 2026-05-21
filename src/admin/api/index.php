<?php
header("Content-Type: application/json");

require_once __DIR__ . "/../../config/db.php";

try {

    $pdo = getDBConnection();
    $method = $_SERVER["REQUEST_METHOD"];

    // ================= GET =================
    if ($method === "GET") {

        if (isset($_GET["id"])) {
            $stmt = $pdo->prepare("SELECT id,name,email,is_admin FROM users WHERE id=?");
            $stmt->execute([$_GET["id"]]);
            $user = $stmt->fetch();

            if (!$user) {
                http_response_code(404);
                echo json_encode(["success" => false]);
                exit;
            }

            echo json_encode(["success" => true, "data" => $user]);
            exit;
        }

        if (isset($_GET["search"])) {
            $q = "%" . $_GET["search"] . "%";

            $stmt = $pdo->prepare("
                SELECT id,name,email,is_admin 
                FROM users 
                WHERE name LIKE ? OR email LIKE ?
            ");
            $stmt->execute([$q, $q]);

            echo json_encode([
                "success" => true,
                "data" => $stmt->fetchAll()
            ]);
            exit;
        }

        $stmt = $pdo->query("SELECT id,name,email,is_admin FROM users");

        echo json_encode([
            "success" => true,
            "data" => $stmt->fetchAll()
        ]);
        exit;
    }

    // ================= POST =================
    if ($method === "POST") {

        $data = json_decode(file_get_contents("php://input"), true);

        if (!$data || !isset($data["name"], $data["email"], $data["password"])) {
            http_response_code(400);
            echo json_encode(["success" => false]);
            exit;
        }

        if (strlen($data["password"]) < 8) {
            http_response_code(400);
            echo json_encode(["success" => false]);
            exit;
        }

        $stmt = $pdo->prepare("SELECT id FROM users WHERE email=?");
        $stmt->execute([$data["email"]]);

        if ($stmt->fetch()) {
            http_response_code(409);
            echo json_encode(["success" => false]);
            exit;
        }

        $stmt = $pdo->prepare("
            INSERT INTO users (name,email,password,is_admin)
            VALUES (?,?,?,?)
        ");

        $stmt->execute([
            $data["name"],
            $data["email"],
            password_hash($data["password"], PASSWORD_DEFAULT),
            $data["is_admin"] ?? 0
        ]);

        http_response_code(201);
        echo json_encode(["success" => true]);
        exit;
    }

    // ================= PUT =================
    if ($method === "PUT") {

        $data = json_decode(file_get_contents("php://input"), true);

        $stmt = $pdo->prepare("SELECT * FROM users WHERE id=?");
        $stmt->execute([$data["id"]]);
        $user = $stmt->fetch();

        if (!$user) {
            http_response_code(404);
            echo json_encode(["success" => false]);
            exit;
        }

        if (isset($data["name"])) {
            $stmt = $pdo->prepare("UPDATE users SET name=? WHERE id=?");
            $stmt->execute([$data["name"], $data["id"]]);
        }

        if (isset($data["email"])) {

            $stmt = $pdo->prepare("SELECT id FROM users WHERE email=? AND id!=?");
            $stmt->execute([$data["email"], $data["id"]]);

            if ($stmt->fetch()) {
                http_response_code(409);
                echo json_encode(["success" => false]);
                exit;
            }

            $stmt = $pdo->prepare("UPDATE users SET email=? WHERE id=?");
            $stmt->execute([$data["email"], $data["id"]]);
        }

        echo json_encode(["success" => true]);
        exit;
    }

    // ================= DELETE =================
    if ($method === "DELETE") {

        $id = $_GET["id"] ?? null;

        $stmt = $pdo->prepare("DELETE FROM users WHERE id=?");
        $stmt->execute([$id]);

        if ($stmt->rowCount() === 0) {
            http_response_code(404);
            echo json_encode(["success" => false]);
            exit;
        }

        echo json_encode(["success" => true]);
        exit;
    }

    http_response_code(405);
    echo json_encode(["success" => false]);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error" => $e->getMessage()
    ]);
}
