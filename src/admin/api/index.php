<?php
session_start();
header("Content-Type: application/json");

require_once "../../common/db.php";
$pdo = getDBConnection();

$method = $_SERVER['REQUEST_METHOD'];

// -------------------- GET ALL --------------------
if ($method === "GET" && !isset($_GET['id'])) {

    $stmt = $pdo->query("SELECT id,name,email,is_admin FROM users");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    jsonResponse([
        "success" => true,
        "data" => $users
    ]);
}

// -------------------- GET BY ID --------------------
if ($method === "GET" && isset($_GET['id'])) {

    $stmt = $pdo->prepare("SELECT id,name,email,is_admin FROM users WHERE id=?");
    $stmt->execute([$_GET['id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        jsonResponse(["success" => false], 404);
    }

    jsonResponse([
        "success" => true,
        "data" => $user
    ]);
}

// -------------------- CREATE --------------------
if ($method === "POST") {

    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data['name'], $data['email'], $data['password'])) {
        jsonResponse(["success" => false], 400);
    }

    if (strlen($data['password']) < 8) {
        jsonResponse(["success" => false], 400);
    }

    // duplicate check
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email=?");
    $stmt->execute([$data['email']]);

    if ($stmt->fetch()) {
        jsonResponse(["success" => false], 409);
    }

    $stmt = $pdo->prepare("
        INSERT INTO users (name,email,password,is_admin)
        VALUES (?,?,?,?)
    ");

    $stmt->execute([
        $data['name'],
        $data['email'],
        $data['password'],
        $data['is_admin'] ?? 0
    ]);

    jsonResponse([
        "success" => true,
        "data" => ["id" => $pdo->lastInsertId()]
    ], 201);
}

// -------------------- UPDATE --------------------
if ($method === "PUT") {

    $data = json_decode(file_get_contents("php://input"), true);

    $stmt = $pdo->prepare("SELECT id FROM users WHERE id=?");
    $stmt->execute([$data['id']]);

    if (!$stmt->fetch()) {
        jsonResponse(["success" => false], 404);
    }

    if (isset($data['email'])) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email=? AND id!=?");
        $stmt->execute([$data['email'], $data['id']]);
        if ($stmt->fetch()) {
            jsonResponse(["success" => false], 409);
        }
    }

    $stmt = $pdo->prepare("
        UPDATE users 
        SET name=COALESCE(?,name),
            email=COALESCE(?,email)
        WHERE id=?
    ");

    $stmt->execute([
        $data['name'] ?? null,
        $data['email'] ?? null,
        $data['id']
    ]);

    jsonResponse(["success" => true]);
}

// -------------------- DELETE --------------------
if ($method === "DELETE") {

    $id = $_GET['id'] ?? null;

    $stmt = $pdo->prepare("DELETE FROM users WHERE id=?");
    $stmt->execute([$id]);

    if ($stmt->rowCount() === 0) {
        jsonResponse(["success" => false], 404);
    }

    jsonResponse(["success" => true]);
}

// -------------------- METHOD NOT ALLOWED --------------------
jsonResponse(["success" => false], 405);


// helper
function jsonResponse($data, $status = 200)
{
    http_response_code($status);
    echo json_encode($data);
    exit;
}
