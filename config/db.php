<?php
$host = '127.0.0.1';
$dbname = 'white_care_db';
$user = 'root';
$pass = ''; // en Laragon normalmente va vacío

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $user,
        $pass
    );

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    die("Error de conexión a MySQL: " . $e->getMessage());
}