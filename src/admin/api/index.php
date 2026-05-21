<?php
header("Content-Type: application/json");
require_once __DIR__ . '/../../config/db.php';

$pdo = getDBConnection();
$method = $_SERVER['REQUEST_METHOD'];

function response($data, $code = 200)
{
    http_response_code($code);
    echo json_encode($data);
    exit;
}

/* ===================== GET ===================== */
if ($method === "GET") {

    if (isset($_GET['id'])) {
        $stmt = $pdo->prepare("SELECT id, name, email, is_admin FROM users WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        $user = $stmt->fetch();

        if (!$user) response(null, 404);

        response(["success" => true, "data" => $user]);
    }

    if (isset($_GET['search'])) {
        $s = "%" . $_GET['search'] . "%";

        $stmt = $pdo->prepare("SELECT id, name, email, is_admin FROM users WHERE name LIKE ? OR email LIKE ?");
        $stmt->execute([$s, $s]);

        response(["success" => true, "data" => $stmt->fetchAll()]);
    }

    $stmt = $pdo->query("SELECT id, name, email, is_admin FROM users");
    response(["success" => true, "data" => $stmt->fetchAll()]);
}

/* ===================== POST ===================== */
if ($method === "POST") {

    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data['name'] || !$data['email'] || !$data['password']) {
        response(["success" => false], 400);
    }

    if (strlen($data['password']) < 8) {
        response(["success" => false], 400);
    }

    $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $check->execute([$data['email']]);

    if ($check->fetch()) {
        response(["success" => false], 409);
    }

    $stmt = $pdo->prepare("
        INSERT INTO users (name, email, password, is_admin)
        VALUES (?, ?, ?, ?)
    ");

    $stmt->execute([
        $data['name'],
        $data['email'],
        password_hash($data['password'], PASSWORD_DEFAULT),
        $data['is_admin'] ?? 0
    ]);

    response(["success" => true, "id" => $pdo->lastInsertId()], 201);
}

/* ===================== PUT ===================== */
if ($method === "PUT") {

    $data = json_decode(file_get_contents("php://input"), true);

    $id = $data['id'] ?? null;
    if (!$id) response(["success" => false], 400);

    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch();

    if (!$user) response(["success" => false], 404);

    if (isset($data['email'])) {

        $check = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $check->execute([$data['email'], $id]);

        if ($check->fetch()) {
            response(["success" => false], 409);
        }
    }

    $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
    $stmt->execute([
        $data['name'] ?? $user['name'],
        $data['email'] ?? $user['email'],
        $id
    ]);

    response(["success" => true]);
}

/* ===================== DELETE ===================== */
if ($method === "DELETE") {

    $id = $_GET['id'] ?? null;

    if (!$id) response(["success" => false], 400);

    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$id]);

    if ($stmt->rowCount() === 0) {
        response(["success" => false], 404);
    }

    response(["success" => true]);
}

response(["success" => false], 405);
