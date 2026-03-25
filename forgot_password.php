<?php
session_start();
require_once 'config/db.php';

$mensaje = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $inputCode = trim($_POST['code']);

    if (!empty($inputCode)) {
        // Buscamos al usuario por su código de recuperación único
        $stmt = $pdo->prepare("SELECT id_user FROM users WHERE recovery_code = :code LIMIT 1");
        $stmt->execute([':code' => $inputCode]);
        $user = $stmt->fetch();

        if ($user) {
            // Guardamos el ID del usuario en la sesión para que 'reset_new_password.php' 
            // sepa a quién le estamos cambiando la clave.
            $_SESSION['reset_user_id'] = $user['id_user'];
            
            // Redirigimos a la página de nueva contraseña
            header("Location: reset_new_password.php");
            exit;
        } else {
            $mensaje = "The recovery code is invalid.";
        }
    } else {
        $mensaje = "Please enter the 4-digit code.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recover Access</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background-color: #f0f2f5; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .card { background: white; padding: 40px; border-radius: 15px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); width: 100%; max-width: 320px; text-align: center; }
        h2 { color: #333; margin-top: 0; }
        input { 
            width: 100%; padding: 15px; margin: 20px 0; border: 2px solid #eee; border-radius: 8px; 
            box-sizing: border-box; font-size: 24px; text-align: center; letter-spacing: 10px; font-weight: bold;
        }
        input:focus { border-color: #007bff; outline: none; }
        button { 
            width: 100%; padding: 12px; background-color: #007bff; color: white; border: none; 
            border-radius: 8px; cursor: pointer; font-weight: bold; font-size: 16px; 
        }
        button:hover { background-color: #0056b3; }
        .msg { color: #721c24; background: #f8d7da; padding: 10px; border-radius: 5px; font-size: 13px; margin-bottom: 15px; }
        p { color: #666; font-size: 14px; }
    </style>
</head>
<body>

<div class="card">
    <h2>Security</h2>
    
    <?php if ($mensaje): ?>
        <div class="msg"><?php echo $mensaje; ?></div>
    <?php endif; ?>

    <p>Enter your 4-digit code to change your password.</p>
    
    <form method="POST">
        <input type="password" 
               name="code" 
               placeholder="****" 
               maxlength="4" 
               pattern="\d*" 
               inputmode="numeric" 
               required>
        
        <button type="submit">Validate Code</button>
    </form>

    <br>
    <a href="login.php" style="font-size: 13px; color: #888; text-decoration: none;">Cancel</a>
</div>

</body>
</html>