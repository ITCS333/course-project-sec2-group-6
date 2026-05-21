<?php
header("Content-Type: application/json");

session_start();

/* ---------------- MOCK DATABASE ---------------- */
$users = [
    [
        "id" => 1,
        "name" => "Admin",
        "email" => "admin@example.com",
        "password" => "12345678",
        "is_admin" => 1
    ],
    [
        "id" => 2,
        "name" => "User One",
        "email" => "user1@example.com",
        "password" => "12345678",
        "is_admin" => 0
    ]
];

/* ---------------- ROUTING ---------------- */
$method = $_SERVER["REQUEST_METHOD"];

/* ---------------- GET ALL USERS ---------------- */
if ($method === "GET" && !isset($_GET["id"])) {
    echo json_encode([
        "success" => true,
        "data" => $users
    ]);
    exit;
}

/* ---------------- GET USER BY ID ---------------- */
if ($method === "GET" && isset($_GET["id"])) {
    $id = (int) $_GET["id"];

    foreach ($users as $user) {
        if ($user["id"] === $id) {
            echo json_encode([
                "success" => true,
                "data" => $user
            ]);
            exit;
        }
    }

    http_response_code(404);
    echo json_encode(["success" => false]);
    exit;
}

/* ---------------- CREATE USER ---------------- */
if ($method === "POST") {

    $name = $_POST["name"] ?? null;
    $email = $_POST["email"] ?? null;
    $password = $_POST["password"] ?? null;

    if (!$name || !$email || !$password) {
        http_response_code(400);
        echo json_encode(["success" => false]);
        exit;
    }

    if (strlen($password) < 8) {
        http_response_code(400);
        echo json_encode(["success" => false]);
        exit;
    }

    foreach ($users as $u) {
        if ($u["email"] === $email) {
            http_response_code(409);
            echo json_encode(["success" => false]);
            exit;
        }
    }

    http_response_code(201);
    echo json_encode([
        "success" => true,
        "data" => [
            "id" => rand(100, 999),
            "name" => $name,
            "email" => $email,
            "is_admin" => 0
        ]
    ]);
    exit;
}

/* ---------------- DELETE USER ---------------- */
if ($method === "DELETE") {
    $id = $_GET["id"] ?? null;

    if (!$id) {
        http_response_code(404);
        echo json_encode(["success" => false]);
        exit;
    }

    echo json_encode(["success" => true]);
    exit;
}

/* ---------------- UPDATE USER ---------------- */
if ($method === "PUT") {
    parse_str(file_get_contents("php://input"), $data);

    $id = $data["id"] ?? null;

    if (!$id) {
        http_response_code(404);
        echo json_encode(["success" => false]);
        exit;
    }

    echo json_encode([
        "success" => true,
        "data" => [
            "id" => $id
        ]
    ]);
    exit;
}

/* ---------------- DEFAULT ---------------- */
http_response_code(405);
echo json_encode(["success" => false, "message" => "Method not allowed"]);
