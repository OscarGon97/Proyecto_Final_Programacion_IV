<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login con MySQL</title>
    <style>
.box {
    width: 350px;
    background: white;
    padding: 30px;
    border-radius: 8px;
    margin: 40px auto;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.box h1 {
    text-align: left;
    color: #1a1a1a;
    font-size: 32px;
    font-weight: 700;
    margin: 0 0 24px 0;
    padding: 0;
}

.box label {
    display: block;
    margin-bottom: 8px;
    color: #333;
    font-size: 14px;
    font-weight: 500;
}

.box input {
    display: block;
    width: 100%;
    padding: 12px;
    margin: 0 0 16px 0;
    border: 1px solid #d0d0d0;
    border-radius: 4px;
    box-sizing: border-box;
    font-size: 14px;
    font-family: inherit;
}

.box input:focus {
    outline: none;
    border-color: #957698;
    box-shadow: 0 0 0 3px rgba(0, 74, 97, 0.1);
}

.box button {
    display: block;
    width: 100%;
    padding: 12px;
    background: #957698;
    border: none;
    color: white;
    font-weight: 700;
    cursor: pointer;
    border-radius: 4px;
    margin-bottom: 12px;
    font-size: 15px;
    transition: background-color 0.2s;
}

.box button:last-of-type {
    margin-bottom: 0;
}

.box button:hover {
    background: #957698;
}

    </style>
</head>
<body>
    <div class="box">
        <h2>Iniciar Sesión</h2>

        <?php if (isset($_GET['error'])): ?>
            <div class="error"></div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <input type="text" name="username" placeholder="Usuario" required>
            <input type="password" name="password" placeholder="Contraseña" required>
            <button type="submit">Ingresar</button>
        </form>
    </div>
</body>
</html>