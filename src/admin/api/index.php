<?php
session_start();
header("Content-Type: application/json");

// ----------------------
// Seeded users (IMPORTANT)
// ----------------------
if (!isset($_SESSION['users'])) {
    $_SESSION['users'] = [
        [
            "id" => 1,
            "name" => "Ali",
            "email" => "ali@stu.uob.edu.bh",
            "password" => "password123",
            "is_admin" => 1
        ],
        [
            "id" => 2,
            "name" => "Fatema",
            "email" => "fatema@stu.uob.edu.bh",
            "password" => "password123",
            "is_admin" => 0
        ]
    ];
}

function jsonResponse($data, $status = 200)
{
    http_response_code($status);
    echo json_encode($data);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$users = &$_SESSION['users'];

// ----------------------
// GET
// ----------------------
if ($method === "GET") {

    // search
    if (isset($_GET['search'])) {
        $keyword = strtolower($_GET['search']);
        $filtered = array_values(array_filter($users, function ($u) use ($keyword) {
            return strpos(strtolower($u['name']), $keyword) !== false ||
                   strpos(strtolower($u['email']), $keyword) !== false;
        }));

        foreach ($filtered as &$u) {
            unset($u['password']);
        }

        jsonResponse([
            "success" => true,
            "data" => $filtered
        ]);
    }

    // get by id
    if (isset($_GET['id'])) {
        foreach ($users as $u) {
            if ($u['id'] == $_GET['id']) {
                unset($u['password']);
                jsonResponse([
                    "success" => true,
                    "data" => $u
                ]);
            }
        }
        jsonResponse(["success" => false], 404);
    }

    // all users
    $result = [];
    foreach ($users as $u) {
        unset($u['password']);
        $result[] = $u;
    }

    jsonResponse([
        "success" => true,
        "data" => $result
    ]);
}

// ----------------------
// POST (CREATE + change password)
// ----------------------
if ($method === "POST") {

    $input = $_POST ?: json_decode(file_get_contents("php://input"), true);

    // change password
    if (isset($_GET['action']) && $_GET['action'] === "change_password") {

        foreach ($users as &$u) {
            if ($u['id'] == $input['id']) {

                if ($input['current_password'] !== $u['password']) {
                    jsonResponse(["success" => false], 401);
                }

                if (strlen($input['new_password']) < 8) {
                    jsonResponse(["success" => false], 400);
                }

                $u['password'] = $input['new_password'];
                jsonResponse(["success" => true]);
            }
        }

        jsonResponse(["success" => false], 404);
    }

    // create user
    if (
        !isset($input['name']) ||
        !isset($input['email']) ||
        !isset($input['password'])
    ) {
        jsonResponse(["success" => false], 400);
    }

    if (strlen($input['password']) < 8) {
        jsonResponse(["success" => false], 400);
    }

    foreach ($users as $u) {
        if ($u['email'] === $input['email']) {
            jsonResponse(["success" => false], 409);
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

    jsonResponse([
        "success" => true,
        "data" => ["id" => $newId]
    ], 201);
}

// ----------------------
// PUT (UPDATE)
// ----------------------
if ($method === "PUT") {

    $input = json_decode(file_get_contents("php://input"), true);

    foreach ($users as &$u) {
        if ($u['id'] == $input['id']) {

            // duplicate email check
            if (isset($input['email'])) {
                foreach ($users as $other) {
                    if ($other['email'] === $input['email'] && $other['id'] != $input['id']) {
                        jsonResponse(["success" => false], 409);
                    }
                }
                $u['email'] = $input['email'];
            }

            if (isset($input['name'])) {
                $u['name'] = $input['name'];
            }

            jsonResponse(["success" => true]);
        }
    }

    jsonResponse(["success" => false], 404);
}

// ----------------------
// DELETE
// ----------------------
if ($method === "DELETE") {

    $id = $_GET['id'] ?? null;

    foreach ($users as $i => $u) {
        if ($u['id'] == $id) {
            array_splice($users, $i, 1);
            jsonResponse(["success" => true]);
        }
    }

    jsonResponse(["success" => false], 404);
}

// ----------------------
// NOT ALLOWED
// ----------------------
jsonResponse(["success" => false], 405);
