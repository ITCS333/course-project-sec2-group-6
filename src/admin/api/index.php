<?php
header("Content-Type: application/json");
require_once __DIR__ . '/../../config/db.php';

try {
    $pdo = getDBConnection();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

function out($data, $code = 200)
{
    http_response_code($code);
    echo json_encode($data);
    exit;
}

/* ================= GET ================= */
if ($method === "GET") {

    if (isset($_GET['id'])) {
        $stmt = $pdo->prepare("SELECT id,name,email,is_admin FROM users WHERE id=?");
        $stmt->execute([$_GET['id']]);
        $user = $stmt->fetch();

        if (!$user) out(["success" => false], 404);

        out(["success" => true, "data" => $user]);
    }

    if (isset($_GET['search'])) {
        $s = "%" . $_GET['search'] . "%";
        $stmt = $pdo->prepare("SELECT id,name,email,is_admin FROM users WHERE name LIKE ? OR email LIKE ?");
        $stmt->execute([$s, $s]);

        out(["success" => true, "data" => $stmt->fetchAll()]);
    }

    $stmt = $pdo->query("SELECT id,name,email,is_admin FROM users");
    out(["success" => true, "data" => $stmt->fetchAll()]);
}

/* ================= POST ================= */
if ($method === "POST") {

    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data['name'] || !$data['email'] || !$data['password']) {
        out(["success" => false], 400);
    }

    if (strlen($data['password']) < 8) {
        out(["success" => false], 400);
    }

    $check = $pdo->prepare("SELECT id FROM users WHERE email=?");
    $check->execute([$data['email']]);

    if ($check->fetch()) {
        out(["success" => false], 409);
    }

    $stmt = $pdo->prepare("
        INSERT INTO users (name,email,password,is_admin)
        VALUES (?,?,?,?)
    ");

    $stmt->execute([
        $data['name'],
        $data['email'],
        password_hash($data['password'], PASSWORD_DEFAULT),
        $data['is_admin'] ?? 0
    ]);

    out(["success" => true], 201);
}

/* ================= PUT ================= */
if ($method === "PUT") {

    $data = json_decode(file_get_contents("php://input"), true);

    $id = $data['id'] ?? null;
    if (!$id) out(["success" => false], 400);

    $stmt = $pdo->prepare("SELECT * FROM users WHERE id=?");
    $stmt->execute([$id]);
    $user = $stmt->fetch();

    if (!$user) out(["success" => false], 404);

    if (isset($data['email'])) {
        $check = $pdo->prepare("SELECT id FROM users WHERE email=? AND id != ?");
        $check->execute([$data['email'], $id]);

        if ($check->fetch()) {
            out(["success" => false], 409);
        }
    }

    $stmt = $pdo->prepare("UPDATE users SET name=?, email=? WHERE id=?");
    $stmt->execute([
        $data['name'] ?? $user['name'],
        $data['email'] ?? $user['email'],
        $id
    ]);

    out(["success" => true]);
}

/* ================= DELETE ================= */
if ($method === "DELETE") {

    $id = $_GET['id'] ?? null;
    if (!$id) out(["success" => false], 400);

    $stmt = $pdo->prepare("DELETE FROM users WHERE id=?");
    $stmt->execute([$id]);

    if ($stmt->rowCount() === 0) {
        out(["success" => false], 404);
    }

    out(["success" => true]);
}

out(["success" => false], 405);
