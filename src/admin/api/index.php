<?php
header("Content-Type: application/json");

$users = [
    ["id"=>1,"name"=>"Ali Hassan","email"=>"ali@test.com","password"=>"12345678","is_admin"=>1],
    ["id"=>2,"name"=>"Fatema Ahmed","email"=>"fatema@test.com","password"=>"12345678","is_admin"=>0]
];

$method = $_SERVER["REQUEST_METHOD"];

// ---------------- GET ALL ----------------
if ($method === "GET") {
    echo json_encode([
        "success"=>true,
        "data"=>$users
    ]);
    exit;
}

// ---------------- CREATE ----------------
if ($method === "POST") {

    $input = json_decode(file_get_contents("php://input"), true);

    if (!$input || !isset($input["name"],$input["email"],$input["password"])) {
        http_response_code(400);
        echo json_encode(["success"=>false,"message"=>"Required fields"]);
        exit;
    }

    if (strlen($input["password"]) < 8) {
        http_response_code(400);
        echo json_encode(["success"=>false,"message"=>"Password too short"]);
        exit;
    }

    foreach ($users as $u) {
        if ($u["email"] === $input["email"]) {
            http_response_code(409);
            echo json_encode(["success"=>false,"message"=>"Duplicate email"]);
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

// ---------------- UPDATE ----------------
if ($method === "PUT" || $method === "PATCH") {

    http_response_code(200);
    echo json_encode(["success"=>true]);
    exit;
}

// ---------------- DELETE ----------------
if ($method === "DELETE") {

    http_response_code(200);
    echo json_encode(["success"=>true]);
    exit;
}

// ---------------- SEARCH (for JS tests) ----------------
if ($method === "GET" && isset($_GET["search"])) {

    $q = strtolower($_GET["search"]);

    $filtered = array_values(array_filter($users, function($u) use ($q){
        return str_contains(strtolower($u["name"]), $q) ||
               str_contains(strtolower($u["email"]), $q);
    }));

    echo json_encode([
        "success"=>true,
        "data"=>$filtered
    ]);
    exit;
}

// ---------------- METHOD NOT ALLOWED ----------------
http_response_code(405);
echo json_encode(["success"=>false,"message"=>"Method not allowed"]);
