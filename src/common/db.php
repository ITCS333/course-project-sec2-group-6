<?php
function getDBConnection(){
    $pdo = new PDO("mysql:host=localhost;dbname=course","admin","password123");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $pdo;
}
?>
