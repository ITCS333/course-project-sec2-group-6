<?php
session_start();
header("Content-Type: application/json");

// -------------------- SEED USERS --------------------
if (!isset($_SESSION['users'])) {
    $_SESSION['users'] = [
        [
            "id" => 1,
            "name" => "Ali Hassan",
            "email" => "ali@stu.uob.edu.bh",
            "password" => "password",
            "is_admin" => 1
        ],
        [
            "id" => 2,
            "name" => "Fatema Ahmed",
            "email" => "fatema@stu.uob.edu.bh",
            "password" => "password",
            "is_admin" => 0
        ]
    ];
}

$users = &$_SESSION['users'];
$method = $_SERVER['REQUEST_METHOD'];

// -------------------- RESPONSE HELPER --------------------
function respond($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data);
    exit;
}

// -------------------- GET --------------------
if ($method === "GET") {

    if (isset($_GET['id'])) {
        foreach ($users as $u) {
            if ($u['id'] == $_GET['id']) {
                unset($u['password']);
                respond(["success" => true, "data" => $u]);
            }
        }
        respond(["success" => false], 404);
    }

    $result = [];
    foreach ($users as $u) {
        unset($u['password']);
        $result[] = $u;
    }

    respond(["success" => true, "data" => $result]);
}

// -------------------- POST (CREATE + CHANGE PASSWORD) --------------------
if ($method === "POST") {

    $input = json_decode(file_get_contents("php://input"), true);

    // CHANGE PASSWORD
    if (isset($_GET['action']) && $_GET['action'] === "change_password") {

        foreach ($users as &$u) {
            if ($u['id'] == $input['id']) {

                if ($input['current_password'] !== $u['password']) {
                    respond(["success" => false], 401);
                }

                if (strlen($input['new_password']) < 8) {
                    respond(["success" => false], 400);
                }

                $u['password'] = $input['new_password'];
                respond(["success" => true]);
            }
        }

        respond(["success" => false], 404);
    }

    // CREATE USER
    if (
        !isset($input['name']) ||
        !isset($input['email']) ||
        !isset($input['password'])
    ) {
        respond(["success" => false], 400);
    }

    if (strlen($input['password']) < 8) {
        respond(["success" => false], 400);
    }

    foreach ($users as $u) {
        if ($u['email'] === $input['email']) {
            respond(["success" => false], 409);
        }
    }

    $newId = max(array_column($users, 'id')) + 1;

    $users[] = [
        "id" => $newId,
        "name" => $input['name'],
        "email" => $input['email'],
        "password" => $input['password'],
        "is_admin" => $input['is_admin'] ?? 0
    ];

    respond(["success" => true, "data" => ["id" => $newId]], 201);
}

// -------------------- PUT --------------------
if ($method === "PUT") {

    $input = json_decode(file_get_contents("php://input"), true);

    foreach ($users as &$u) {
        if ($u['id'] == $input['id']) {

            if (isset($input['email'])) {
                foreach ($users as $other) {
                    if ($other['email'] === $input['email'] && $other['id'] != $input['id']) {
                        respond(["success" => false], 409);
                    }
                }
                $u['email'] = $input['email'];
            }

            if (isset($input['name'])) {
                $u['name'] = $input['name'];
            }

            unset($u); // important fix
            respond(["success" => true]);
        }
    }

    respond(["success" => false], 404);
}

// -------------------- DELETE --------------------
if ($method === "DELETE") {

    $id = $_GET['id'] ?? null;

    foreach ($users as $i => $u) {
        if ($u['id'] == $id) {
            array_splice($users, $i, 1);
            respond(["success" => true]);
        }
    }

    respond(["success" => false], 404);
}

respond(["success" => false], 405);
