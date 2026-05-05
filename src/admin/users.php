<?php
session_start();
require_once "../../config/db.php";

header("Content-Type: application/json");

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    echo json_encode([
        "success" => false,
        "message" => "Unauthorized"
    ]);
    exit;
}

try {
    $db = getDBConnection();

    $stmt = $db->query("SELECT id, name, email, is_admin FROM users");
    $users = $stmt->fetchAll();

    echo json_encode([
        "success" => true,
        "users" => $users
    ]);

} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "message" => "Server error"
    ]);
}
?>
