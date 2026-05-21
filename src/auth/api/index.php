<?php
header("Content-Type: application/json");
require_once "../config/db.php";

$pdo = getDBConnection();
$method = $_SERVER['REQUEST_METHOD'];

function send($data,$status=200){
    http_response_code($status);
    echo json_encode($status<400 ? ["success"=>true,"data"=>$data] : ["success"=>false,"message"=>$data]);
    exit;
}

// GET
if($method==="GET"){
    $stmt=$pdo->query("SELECT id,name,email,is_admin FROM users");
    send($stmt->fetchAll(PDO::FETCH_ASSOC));
}

// POST
if($method==="POST"){
    $data=json_decode(file_get_contents("php://input"),true);

    if(!$data['name']||!$data['email']||!$data['password']){
        send("Missing fields",400);
    }

    $hash=password_hash($data['password'],PASSWORD_DEFAULT);

    $stmt=$pdo->prepare("INSERT INTO users(name,email,password,is_admin) VALUES(?,?,?,?)");
    $stmt->execute([$data['name'],$data['email'],$hash,$data['is_admin']??0]);

    send("User created",201);
}

// DELETE
if($method==="DELETE"){
    $id=$_GET['id']??0;
    $stmt=$pdo->prepare("DELETE FROM users WHERE id=?");
    $stmt->execute([$id]);
    send("Deleted");
}
?>
