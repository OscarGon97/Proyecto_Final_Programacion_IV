<?php
require_once 'config/db.php';

$stmt = $pdo->query("SELECT * FROM roles");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<pre>";
print_r($rows);
echo "</pre>";