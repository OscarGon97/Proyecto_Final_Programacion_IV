<?php
header("Content-Type: application/json");
require_once "../config/db.php";

$accion = $_GET['action'] ?? 'logs';

// filtros (usuarios, acciones, tablas, mensajes de error)
if ($accion === 'filters') {
    $tipo = $_GET['type'] ?? 'activity';
    try {
        if ($tipo === 'activity') {
            // Usuarios con activity_logs
            $users = $pdo->query("SELECT DISTINCT u.id_user, u.full_name 
                                  FROM activity_logs al 
                                  JOIN users u ON al.id_user = u.id_user 
                                  ORDER BY u.full_name")->fetchAll(PDO::FETCH_ASSOC);
            // Acciones distintas
            $actions = $pdo->query("SELECT DISTINCT action FROM activity_logs ORDER BY action")->fetchAll(PDO::FETCH_COLUMN);
            // Tablas afectadas
            $tables = $pdo->query("SELECT DISTINCT affected_table FROM activity_logs ORDER BY affected_table")->fetchAll(PDO::FETCH_COLUMN);
            echo json_encode(["users" => $users, "actions" => $actions, "tables" => $tables]);
        } else { // error logs
            // Usuarios con error_logs
            $users = $pdo->query("SELECT DISTINCT u.id_user, u.full_name 
                                  FROM error_logs el 
                                  JOIN users u ON el.id_user = u.id_user 
                                  ORDER BY u.full_name")->fetchAll(PDO::FETCH_ASSOC);
            $errorMessages = $pdo->query("SELECT DISTINCT error_message FROM error_logs ORDER BY error_message")->fetchAll(PDO::FETCH_COLUMN);
            echo json_encode(["users" => $users, "errorMessages" => $errorMessages]);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["error" => "Error en BD: " . $e->getMessage()]);
    }
    exit;
}

// Parámetros de consulta
$logType = $_GET['type'] ?? 'activity';
$userId = isset($_GET['user_id']) && $_GET['user_id'] !== '' ? (int)$_GET['user_id'] : null;
$actionFilter = $_GET['action_filter'] ?? null;
$tableFilter = $_GET['table'] ?? null;
$errorMessage = $_GET['error_message'] ?? null;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 100;
$offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;

try {
    if ($logType === 'activity') {
        // Consulta para activity_logs
        $sql = "SELECT al.id_log, al.id_user, al.action, al.affected_table, al.record_id, 
                       al.old_value, al.new_value, al.activity_date,
                       u.full_name as user_name 
                FROM activity_logs al
                LEFT JOIN users u ON al.id_user = u.id_user
                WHERE 1=1";

        if ($userId) {
            $sql .= " AND al.id_user = " . (int)$userId;
        }
        if ($actionFilter) {
            $sql .= " AND al.action = " . $pdo->quote($actionFilter);
        }
        if ($tableFilter) {
            $sql .= " AND al.affected_table = " . $pdo->quote($tableFilter);
        }

        $sql .= " ORDER BY al.activity_date DESC LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
        $data = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        // Conteo total
        $countSql = "SELECT COUNT(*) FROM activity_logs al WHERE 1=1";
        if ($userId)      $countSql .= " AND al.id_user = " . (int)$userId;
        if ($actionFilter) $countSql .= " AND al.action = " . $pdo->quote($actionFilter);
        if ($tableFilter)  $countSql .= " AND al.affected_table = " . $pdo->quote($tableFilter);
        $total = $pdo->query($countSql)->fetchColumn();

        echo json_encode(["data" => $data, "total" => $total]);
    } 
    else { // error logs
        $sql = "SELECT el.id_error, el.id_user, el.error_date, 
                       el.procedure_name, el.error_code, el.error_message,
                       u.full_name as user_name 
                FROM error_logs el
                LEFT JOIN users u ON el.id_user = u.id_user
                WHERE 1=1";

        if ($userId) {
            $sql .= " AND el.id_user = " . (int)$userId;
        }
        if ($errorMessage) {
            $sql .= " AND el.error_message = " . $pdo->quote($errorMessage);
        }

        $sql .= " ORDER BY el.error_date DESC LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
        $data = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        $countSql = "SELECT COUNT(*) FROM error_logs el WHERE 1=1";
        if ($userId)        $countSql .= " AND el.id_user = " . (int)$userId;
        if ($errorMessage)  $countSql .= " AND el.error_message = " . $pdo->quote($errorMessage);
        $total = $pdo->query($countSql)->fetchColumn();

        echo json_encode(["data" => $data, "total" => $total]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Fallo en la base de datos: " . $e->getMessage()]);
}
?>