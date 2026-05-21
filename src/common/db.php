<?php
function getDBConnection() {
    $pdo = new PDO(
        "mysql:host=localhost;dbname=course;charset=utf8",
        "root",
        ""
    );

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $pdo;
}
