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

$users =& $_SESSION['users'];

function jsonResponse($data, $code = 200) {
    http_response_code($code);
    echo json_encode($data);
    exit;
}

$method = $_SERVER["REQUEST_METHOD"];

/* ===================== GET ===================== */
if ($method === "GET") {

    // GET by search
    if (isset($_GET['search'])) {
        $q = strtolower($_GET['search']);

        $result = array_values(array_filter($users, function($u) use ($q) {
            return str_contains(strtolower($u['name']), $q) ||
                   str_contains(strtolower($u['email']), $q);
        }));

        foreach ($result as &$u) unset($u['password']);

        jsonResponse(["success" => true, "data" => $result]);
    }

    // GET by id
    if (isset($_GET['id'])) {
        foreach ($users as $u) {
            if ($u['id'] == $_GET['id']) {
                unset($u['password']);
                jsonResponse(["success" => true, "data" => $u]);
            }
        }
        jsonResponse(["success" => false], 404);
    }

    // GET all
    $result = [];
    foreach ($users as $u) {
        unset($u['password']);
        $result[] = $u;
    }

    jsonResponse(["success" => true, "data" => $result]);
}

/* ===================== POST ===================== */
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
    if (!isset($input['name'], $input['email'], $input['password'])) {
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

    jsonResponse(["success" => true, "data" => ["id" => $newId]], 201);
}

/* ===================== PUT ===================== */
if ($method === "PUT") {

    $input = json_decode(file_get_contents("php://input"), true);

    foreach ($users as &$u) {
        if ($u['id'] == $input['id']) {

            if (isset($input['email'])) {
                foreach ($users as $o) {
                    if ($o['email'] === $input['email'] && $o['id'] != $input['id']) {
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

/* ===================== DELETE ===================== */
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

jsonResponse(["success" => false], 405);
