<?php
header("Content-Type: application/json");
require_once "../config/db.php";

$method = $_SERVER['REQUEST_METHOD'];

if ($method == "GET") {

    $stmt = $pdo->query("
        SELECT b.*, p.product_name
        FROM batches b
        JOIN products p ON b.product_id = p.id_product
    ");

    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
}

if ($method == "POST") {

    $data = json_decode(file_get_contents("php://input"), true);

    $sql = "INSERT INTO batches
            (product_id, batch_number, quantity)
            VALUES (:product, :batch, :qty)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ":product" => $data["product_id"],
        ":batch" => $data["batch_number"],
        ":qty" => $data["quantity"]
    ]);

    echo json_encode(["message"=>"Lote agregado"]);
}