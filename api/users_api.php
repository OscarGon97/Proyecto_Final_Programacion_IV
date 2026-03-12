<?php
header("Content-Type: application/json");
require_once "../config/db.php";

$method = $_SERVER['REQUEST_METHOD'];

if ($method == "GET") {

    $stmt = $pdo->query("
        SELECT id_user, full_name, email, id_role
        FROM users
    ");

    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
}

