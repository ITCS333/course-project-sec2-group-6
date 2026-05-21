<?php
header("Content-Type: application/json");

session_start();

$file = "users.json";

/* -----------------------------
   Helper: load users safely
------------------------------*/
function loadUsers($file)
{
    if (!file_exists($file)) {
        file_put_contents($file, json_encode([]));
    }

    $data = json_decode(file_get_contents($file), true);

    return is_array($data) ? $data : [];
}

/* -----------------------------
   Helper: save users
------------------------------*/
function saveUsers($file, $users)
{
    file_put_contents($file, json_encode($users, JSON_PRETTY_PRINT));
}

/* -----------------------------
   Load data
------------------------------*/
$users = loadUsers($file);

$method = $_SERVER["REQUEST_METHOD"];

/* =============================
   ROUTER
=============================*/
switch ($method) {

    /* ----------------- GET ALL USERS ----------------- */
    case "GET":

        if (isset($_GET["id"])) {

            $id = intval($_GET["id"]);

            foreach ($users as $user) {
                if ($user["id"] == $id) {
                    http_response_code(200);
                    echo json_encode(["success" => true, "data" => $user]);
                    exit;
                }
            }

            http_response_code(404);
            echo json_encode(["success" => false, "message" => "User not found"]);
            exit;
        }

        http_response_code(200);
        echo json_encode(["success" => true, "data" => $users]);
        exit;

    /* ----------------- CREATE USER ----------------- */
    case "POST":

        $data = json_decode(file_get_contents("php://input"), true);

        if (!$data) {
            $data = $_POST;
        }

        if (!isset($data["name"], $data["email"], $data["password"])) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Missing fields"]);
            exit;
        }

        if (strlen($data["password"]) < 8) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Password too short"]);
            exit;
        }

        foreach ($users as $u) {
            if ($u["email"] === $data["email"]) {
                http_response_code(409);
                echo json_encode(["success" => false, "message" => "Email already exists"]);
                exit;
            }
        }

        $newUser = [
            "id" => count($users) + 1,
            "name" => $data["name"],
            "email" => $data["email"],
            "password" => $data["password"],
            "is_admin" => isset($data["is_admin"]) ? intval($data["is_admin"]) : 0
        ];

        $users[] = $newUser;
        saveUsers($file, $users);

        http_response_code(201);
        echo json_encode(["success" => true, "data" => $newUser]);
        exit;

    /* ----------------- UPDATE USER ----------------- */
    case "PUT":

        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data["id"])) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "ID required"]);
            exit;
        }

        foreach ($users as &$user) {
            if ($user["id"] == $data["id"]) {

                if (isset($data["name"])) {
                    $user["name"] = $data["name"];
                }

                if (isset($data["email"])) {

                    foreach ($users as $u) {
                        if ($u["email"] === $data["email"] && $u["id"] != $data["id"]) {
                            http_response_code(409);
                            echo json_encode(["success" => false, "message" => "Email exists"]);
                            exit;
                        }
                    }

                    $user["email"] = $data["email"];
                }

                saveUsers($file, $users);

                http_response_code(200);
                echo json_encode(["success" => true, "data" => $user]);
                exit;
            }
        }

        http_response_code(404);
        echo json_encode(["success" => false, "message" => "User not found"]);
        exit;

    /* ----------------- DELETE USER ----------------- */
    case "DELETE":

        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data["id"])) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "ID required"]);
            exit;
        }

        foreach ($users as $index => $user) {
            if ($user["id"] == $data["id"]) {

                array_splice($users, $index, 1);
                saveUsers($file, $users);

                http_response_code(200);
                echo json_encode(["success" => true, "message" => "Deleted"]);
                exit;
            }
        }

        http_response_code(404);
        echo json_encode(["success" => false, "message" => "User not found"]);
        exit;

    /* ----------------- METHOD NOT ALLOWED ----------------- */
    default:
        http_response_code(405);
        echo json_encode(["success" => false, "message" => "Method not allowed"]);
        exit;
}
