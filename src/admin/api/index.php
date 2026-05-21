<?php
header("Content-Type: application/json");

$users = [
    ["id"=>1,"name"=>"Ali Hassan","email"=>"ali@test.com","is_admin"=>1],
    ["id"=>2,"name"=>"Fatema Ahmed","email"=>"fatema@test.com","is_admin"=>0]
];

$method = $_SERVER["REQUEST_METHOD"];

// GET ALL
if ($method === "GET") {
    echo json_encode([
        "success"=>true,
        "data"=>$users
    ]);
    exit;
}

// CREATE
if ($method === "POST") {

    $input = json_decode(file_get_contents("php://input"), true);

    if (!$input || !isset($input["name"],$input["email"],$input["password"])) {
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
    echo json_encode(["success"=>true,"data"=>$input]);
    exit;
}

// DELETE
if ($method === "DELETE") {
    echo json_encode(["success"=>true]);
    exit;
}

// UPDATE
if ($method === "PUT" || $method === "PATCH") {
    echo json_encode(["success"=>true]);
    exit;
}

// METHOD NOT ALLOWED
http_response_code(405);
echo json_encode(["success"=>false]);
