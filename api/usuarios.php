<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once '../config/db.php';

$method = $_SERVER['REQUEST_METHOD'];
$response = ['success' => false, 'message' => '', 'data' => null];

try {
    switch ($method) {
        case 'GET':
            // Obtener usuario(s)
            if (isset($_GET['id'])) {
                // Obtener un usuario específico
                $sql = "SELECT id_users,id_role, full_name, email, password_hash , phone , active, registration_date, last_login, failed_attempts
                        FROM users WHERE id_user = :id";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':id', $_GET['id'], PDO::PARAM_INT);
                $stmt->execute();
                $users = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($users) {
                    $response['success'] = true;
                    $response['users'] = $users;
                } else {
                    $response['message'] = 'Usuario no encontrado';
                }
            } else {
                // Obtener todos los usuarios
                $sql = "SELECT id_users, id_role, full_name, email, password_hash, phone, active, registration_date, last_login, failed_attempts
                        FROM users ORDER BY full_name ASC";
                $stmt = $pdo->query($sql);
                $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $response['success'] = true;
                $response['users'] = $users;
            }
            break;

        case 'POST':
            // Crear nuevo usuario
            $data = json_decode(file_get_contents('php://input'), true);

            if (!$data || !isset($data['full_name'])) {
                $response['message'] = 'Datos incompletos';
                break;
            }

            // Validar contraseña para nuevos usuarios
            if (empty($data['password'])) {
                $response['message'] = 'La contraseña es obligatoria para nuevos usuarios';
                break;
            }

            $password_hash = password_hash($data['password'], PASSWORD_BCRYPT);
    
            $sql = "INSERT INTO users (id_users, id_role, full_name, email, password_hash, phone, active, registration_date, last_login, failed_attempts)
                    VALUES (:id_users, :id_role, :full_name, :email, :password_hash, :phone, :active, :registration_date, :last_login, :failed_attempts)";

            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':id_users', $data['id_users']);
            $stmt->bindParam(':id_role', $data['id_role']);
            $stmt->bindParam(':full_name', $data['full_name']);
            $stmt->bindParam(':email', $data['email']);
            $stmt->bindParam(':phone', $data['phone']);
            $stmt->bindParam(':active', $data['active'], PDO::PARAM_INT);
            $stmt->bindParam(':password_hash', $password_hash);
            $stmt->bindParam(':registration_date', $data['registration_date']);
            $stmt->bindParam(':last_login', $data['last_login']);
            $stmt->bindParam(':failed_attempts', $data['failed_attempts'], PDO::PARAM_INT);

            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'Usuario creado correctamente';
                $response['id_user'] = $pdo->lastInsertId();
            } else {
                $response['message'] = 'Error al crear el usuario';
            }
            break;

        case 'PUT':
            // Actualizar usuario
            $data = json_decode(file_get_contents('php://input'), true);

            if (!$data || !isset($data['id_user']) || !isset($data['full_name'])) {
                $response['message'] = 'Datos incompletos';
                break;
            }

            // Verificar si el usuario existe
            $checkSql = "SELECT id_user FROM users WHERE id_user = :id";
            $checkStmt = $pdo->prepare($checkSql);
            $checkStmt->bindParam(':id', $data['id_user'], PDO::PARAM_INT);
            $checkStmt->execute();

            if (!$checkStmt->fetch()) {
                $response['message'] = 'Usuario no encontrado';
                break;
            }

            // Preparar la consulta de actualización
            $params = [
                ':id_users' => $data['id_user'],
                ':full_name' => $data['full_name'],
                ':email' => $data['email'],
                ':phone' => $data['phone'],
                ':id_role' => $data['id_role'],
                ':active' => $data['active']
                'registration_date' => $data['registration_date'],
                'last_login' => $data['last_login'],
                'failed_attempts' => $data['failed_attempts']

            ];

            $sql = "UPDATE users SET
                    full_name = :full_name,
                    email = :email,
                    phone = :phone,
                    id_role = :id_role,
                    active = :active,
                    registration_date = :registration_date,
                    last_login = :last_login,
                    failed_attempts = :failed_attempts";

            // Solo actualizar contraseña si se proporciona
            if (!empty($data['password'])) {
                $sql .= ", password_hash = :password_hash";
                $params[':password_hash'] = password_hash($data['password'], PASSWORD_BCRYPT);
            }

            $sql .= " WHERE id_users = :id_users";

            $stmt = $pdo->prepare($sql);

            if ($stmt->execute($params)) {
                $response['success'] = true;
                $response['message'] = 'Usuario actualizado correctamente';
            } else {
                $response['message'] = 'Error al actualizar el usuario';
            }
            break;

        case 'DELETE':
            // Eliminar usuario
            if (!isset($_GET['id'])) {
                $response['message'] = 'ID de usuario requerido';
                break;
            }

            // Verificar si el usuario existe
            $checkSql = "SELECT id_user FROM users WHERE id_user = :id";
            $checkStmt = $pdo->prepare($checkSql);
            $checkStmt->bindParam(':id', $_GET['id'], PDO::PARAM_INT);
            $checkStmt->execute();

            if (!$checkStmt->fetch()) {
                $response['message'] = 'Usuario no encontrado';
                break;
            }

            $sql = "DELETE FROM users WHERE id_user = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':id', $_GET['id'], PDO::PARAM_INT);

            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'Usuario eliminado correctamente';
            } else {
                $response['message'] = 'Error al eliminar el usuario';
            }
            break;

        default:
            $response['message'] = 'Método HTTP no soportado';
            break;
    }
} catch (Exception $e) {
    $response['message'] = 'Error del servidor: ' . $e->getMessage();
    error_log('Error en API usuarios: ' . $e->getMessage());
}

echo json_encode($response);
?>