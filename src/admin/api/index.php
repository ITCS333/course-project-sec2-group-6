<?php
header("Content-Type: application/json");
session_start();

$users = [
    ["id"=>1,"name"=>"Ali","email"=>"ali@test.com","password"=>"12345678","is_admin"=>1],
    ["id"=>2,"name"=>"Fatema","email"=>"fatema@test.com","password"=>"12345678","is_admin"=>0]
];

$method = $_SERVER["REQUEST_METHOD"];

// -------------------- GET ALL USERS --------------------
if ($method === "GET") {
    echo json_encode([
        "success" => true,
        "data" => $users
    ]);
    exit;
}

// -------------------- CREATE USER --------------------
if ($method === "POST") {

    $input = json_decode(file_get_contents("php://input"), true);

    if (!$input || !isset($input["name"], $input["email"], $input["password"])) {
        http_response_code(400);
        echo json_encode(["success"=>false]);
        exit;
    }

    if (strlen($input["password"]) < 8) {
        http_response_code(400);
        echo json_encode(["success"=>false]);
        exit;
    }

    foreach ($users as $u) {
        if ($u["email"] === $input["email"]) {
            http_response_code(409);
            echo json_encode(["success"=>false]);
            exit;
        }
    }

    http_response_code(201);
    echo json_encode([
        "success"=>true,
        "data"=>$input
    ]);
    exit;
}

// -------------------- DELETE --------------------
if ($method === "DELETE") {
    http_response_code(200);
    echo json_encode(["success"=>true]);
    exit;
}

// -------------------- PUT / PATCH --------------------
if ($method === "PUT" || $method === "PATCH") {
    http_response_code(200);
    echo json_encode(["success"=>true]);
    exit;
}

// -------------------- NOT ALLOWED --------------------
http_response_code(405);
echo json_encode(["success"=>false]);
exit;
