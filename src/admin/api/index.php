<?php
session_start();
header('Content-Type: application/json');
if(empty($_SESSION['is_admin'])||$_SESSION['is_admin']!=1){http_response_code(403);echo json_encode(['success'=>false,'message'=>'Forbidden']);exit;}
require_once __DIR__.'/../config/db.php';
$method=$_SERVER['REQUEST_METHOD'];
switch($method){
    case 'GET': $stmt=$pdo->query("SELECT id,name,email,is_admin FROM users ORDER BY id"); echo json_encode(['success'=>true,'data'=>$stmt->fetchAll(PDO::FETCH_ASSOC)]); break;
    case 'POST': $in=json_decode(file_get_contents('php://input'),true); $stmt=$pdo->prepare("INSERT INTO users (name,email,password,is_admin) VALUES (?,?,?,?)"); if($stmt->execute([$in['name'],$in['email'],password_hash($in['password'],PASSWORD_DEFAULT),(int)($in['is_admin']??0)])){http_response_code(201);echo json_encode(['success'=>true,'message'=>'User added']);}else{http_response_code(500);echo json_encode(['success'=>false,'message'=>'DB error']);} break;
    case 'PUT': $in=json_decode(file_get_contents('php://input'),true); $stmt=$pdo->prepare("UPDATE users SET name=?,email=?,is_admin=? WHERE id=?"); if($stmt->execute([$in['name'],$in['email'],(int)($in['is_admin']??0),(int)$in['id']])){echo json_encode(['success'=>true,'message'=>'User updated']);}else{http_response_code(500);echo json_encode(['success'=>false,'message'=>'Update failed']);} break;
    case 'DELETE': $id=(int)($_GET['id']??0); $stmt=$pdo->prepare("DELETE FROM users WHERE id=?"); if($stmt->execute([$id])){echo json_encode(['success'=>true,'message'=>'User deleted']);}else{http_response_code(500);echo json_encode(['success'=>false,'message'=>'Delete failed']);} break;
    default: http_response_code(405); echo json_encode(['success'=>false,'message'=>'Method not allowed']);
}
