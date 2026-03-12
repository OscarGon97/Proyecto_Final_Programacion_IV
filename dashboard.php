<?php
session_start();
// iniciar sesión y conexión a base de datos
require_once 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// permisos basados en rol (se configuran en login.php al autenticar)
$user_role = $_SESSION['user_role'] ?? 4;

$all_tabs = [
    'authentication' => 'Autenticación y Seguridad',
    'appointments' => 'Pacientes y Citas',
    'inventory' => 'Inventario',
    'sales' => 'Ventas y Cobros',
];

$allowed_tabs = [];
switch ($user_role) {
    case 1: // Admin
        $allowed_tabs = array_keys($all_tabs);
        break;
    case 2: // Odontólogo
        $allowed_tabs = ['appointments', 'inventory'];
        break;
    case 3: // Bodega
        $allowed_tabs = ['inventory'];
        break;
    case 4: // Recepción
        $allowed_tabs = ['appointments', 'sales'];
        break;
    default:
        $allowed_tabs = ['appointments'];
        break;
}

$tab = isset($_GET['tab']) ? $_GET['tab'] : ($allowed_tabs[0] ?? 'authentication');
if (!in_array($tab, $allowed_tabs, true)) {
    $tab = $allowed_tabs[0] ?? 'authentication';
}

$flash = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['action'])) {
    $action = $_POST['action'];
    try {
        switch ($action) {
            case 'add_appointment':
                $patientId = (int)($_POST['patient_id'] ?? 0);
                $dentistId = (int)($_POST['assigned_user'] ?? $_SESSION['user_id']);
                $scheduledAt = $_POST['scheduled_at'] ?? '';
                $reason = trim($_POST['reason'] ?? '');
                if (!$patientId || !$dentistId || !$scheduledAt) {
                    throw new Exception('Faltan datos para crear cita.');
                }
                $dt = DateTime::createFromFormat('Y-m-d\TH:i', $scheduledAt);
                if (!$dt) {
                    throw new Exception('Formato de fecha/hora inválido para cita.');
                }
                $date = $dt->format('Y-m-d');
                $time = $dt->format('H:i:s');
                $stmt = $pdo->prepare("INSERT INTO appointments (id_patient, id_dentist_user, appointment_date, appointment_time, reason, id_appointment_status, duration_minutes, registration_date) VALUES (:patient_id, :dentist_id, :date, :time, :reason, 1, 30, NOW())");
                $stmt->execute([':patient_id'=>$patientId, ':dentist_id'=>$dentistId, ':date'=>$date, ':time'=>$time, ':reason'=>$reason]);
                $flash = 'Cita registrada correctamente.';
                $tab = 'appointments';
                break;
            case 'close_appointment':
                $appointmentId = (int)($_POST['appointment_id'] ?? 0);
                $diagnostic = trim($_POST['diagnostic'] ?? '');
                $treatment = trim($_POST['treatment'] ?? '');
                if (!$appointmentId || !$diagnostic || !$treatment) {
                    throw new Exception('Datos de cierre incompletos.');
                }
                $pdo->prepare("UPDATE appointments SET id_appointment_status = 2 WHERE id_appointment = :id")->execute([':id'=>$appointmentId]);
                $stmt = $pdo->prepare("INSERT INTO medical_histories (id_patient, id_appointment, diagnosis, treatment, notes, requires_control, next_control_date) SELECT id_patient, id_appointment, :diagnostic, :treatment, '', FALSE, NULL FROM appointments WHERE id_appointment = :id");
                $stmt->execute([':id'=>$appointmentId, ':diagnostic'=>$diagnostic, ':treatment'=>$treatment]);
                $flash = 'Cita cerrada y historial médico generado.';
                $tab = 'appointments';
                break;
            case 'add_batch':
                $productId = (int)($_POST['product_id'] ?? 0);
                $quantity = (int)($_POST['quantity'] ?? 0);
                $expiry = $_POST['expiry_date'] ?? null;
                $batchNumber = trim($_POST['batch_number'] ?? '');
                $initialQty = $quantity;
                if (!$productId || $quantity <= 0 || !$expiry || !$batchNumber) {
                    throw new Exception('Datos de lote incompletos.');
                }
                $stmt = $pdo->prepare("INSERT INTO batches (id_product, batch_number, entry_date, expiration_date, initial_quantity, current_quantity) VALUES (:product_id, :batch_number, CURDATE(), :expiration_date, :initial_quantity, :current_quantity)");
                $stmt->execute([':product_id'=>$productId, ':batch_number'=>$batchNumber, ':expiration_date'=>$expiry, ':initial_quantity'=>$initialQty, ':current_quantity'=>$initialQty]);
                $flash = 'Lote agregado con éxito.';
                $tab = 'inventory';
                break;
            case 'add_inventory_movement':
                $batchId = (int)($_POST['batch_id'] ?? 0);
                $movementType = (int)($_POST['movement_type'] ?? 0);
                $quantity = (int)($_POST['movement_quantity'] ?? 0);
                $justification = trim($_POST['movement_reason'] ?? '');
                if (!$batchId || $quantity <= 0 || !$justification || !$movementType) {
                    throw new Exception('Datos de movimiento inválidos.');
                }
                $qtySigned = $movementType === 2 ? -$quantity : $quantity; // asumimos 2=Sale/out
                $pdo->prepare("INSERT INTO inventory_movements (id_user, id_batch, id_movement_type, quantity, justification) VALUES (:id_user, :id_batch, :id_type, :quantity, :justification)")->execute([':id_user'=>$_SESSION['user_id'], ':id_batch'=>$batchId, ':id_type'=>$movementType, ':quantity'=>$qtySigned, ':justification'=>$justification]);
                $pdo->prepare("UPDATE batches SET current_quantity = current_quantity + :adjust WHERE id_batch = :id_batch")->execute([':adjust'=>$qtySigned, ':id_batch'=>$batchId]);
                $flash = 'Movimiento registrado y stock actualizado.';
                $tab = 'inventory';
                break;
            case 'create_sale':
                $patientId = (int)($_POST['sale_patient_id'] ?? 0);
                $batchId = (int)($_POST['sale_batch_id'] ?? 0);
                $saleQty = (int)($_POST['sale_quantity'] ?? 0);
                $price = (float)($_POST['sale_price'] ?? 0);
                if (!$patientId || !$batchId || $saleQty <= 0 || $price <= 0) {
                    throw new Exception('Datos de venta incompletos.');
                }
                $available = (int)$pdo->query("SELECT current_quantity FROM batches WHERE id_batch = $batchId")->fetchColumn();
                if ($saleQty > $available) {
                    throw new Exception('Stock insuficiente en lote seleccionado.');
                }
                $pdo->beginTransaction();
                $subtotal = $saleQty * $price;
                $tax = 0;
                $total = $subtotal + $tax;
                $pdo->prepare("INSERT INTO sales (id_patient, id_user, id_appointment, subtotal, tax, total, payment_method, id_sale_status) VALUES (:id_patient, :id_user, NULL, :subtotal, :tax, :total, 'Cash', 1)")->execute([':id_patient'=>$patientId, ':id_user'=>$_SESSION['user_id'], ':subtotal'=>$subtotal, ':tax'=>$tax, ':total'=>$total]);
                $saleId = $pdo->lastInsertId();
                $batchProductId = (int)$pdo->query("SELECT id_product FROM batches WHERE id_batch = $batchId")->fetchColumn();
                $pdo->prepare("INSERT INTO sale_details (id_sale, id_product, id_movement, quantity, unit_price, subtotal) VALUES (:id_sale, :id_product, :id_movement, :quantity, :unit_price, :subtotal)")->execute([':id_sale'=>$saleId, ':id_product'=>$batchProductId, ':id_movement'=>NULL, ':quantity'=>$saleQty, ':unit_price'=>$price, ':subtotal'=>$subtotal]);
                $pdo->prepare("INSERT INTO inventory_movements (id_user, id_batch, id_movement_type, quantity, justification) VALUES (:id_user, :id_batch, 2, :quantity, 'Sale')")->execute([':id_user'=>$_SESSION['user_id'], ':id_batch'=>$batchId, ':quantity'=>-$saleQty]);
                $movementId = $pdo->lastInsertId();
                $pdo->prepare("UPDATE batches SET current_quantity = current_quantity - :d WHERE id_batch = :id_batch")->execute([':d'=>$saleQty, ':id_batch'=>$batchId]);
                $pdo->prepare("UPDATE sale_details SET id_movement = :id_movement WHERE id_sale = :id_sale")->execute([':id_movement'=>$movementId, ':id_sale'=>$saleId]);
                $pdo->commit();
                $flash = 'Venta registrada correctamente.';
                $tab = 'sales';
                break;
        }
    } catch (Exception $e) {
        if ($pdo->inTransaction()) { $pdo->rollBack(); }
        $flash = 'Error: ' . $e->getMessage();
    }
    header('Location: dashboard.php?tab=' . urlencode($tab) . '&msg=' . urlencode($flash));
    exit;
}
$flash = isset($_GET['msg']) ? $_GET['msg'] : '';

// Carga de datos para los módulos
$users_data = $pdo->query("SELECT id_user, full_name, email, id_role, active FROM users ORDER BY full_name")->fetchAll(PDO::FETCH_ASSOC);
$products_data = $pdo->query("SELECT id_product, product_name FROM products ORDER BY product_name")->fetchAll(PDO::FETCH_ASSOC);
$patients_data = $pdo->query("SELECT id_patient, CONCAT(first_name, ' ', last_name) AS full_name, id_card, phone FROM patients ORDER BY first_name")->fetchAll(PDO::FETCH_ASSOC);
$appointments_data = $pdo->query("SELECT a.id_appointment, CONCAT(p.first_name, ' ', p.last_name) AS patient_name, u.full_name AS doctor_name, a.appointment_date, a.appointment_time, s.status_name AS status, a.reason FROM appointments a LEFT JOIN patients p ON a.id_patient = p.id_patient LEFT JOIN users u ON a.id_dentist_user = u.id_user LEFT JOIN appointment_statuses s ON a.id_appointment_status = s.id_status ORDER BY a.appointment_date DESC, a.appointment_time DESC")->fetchAll(PDO::FETCH_ASSOC);
$batches_data = $pdo->query("SELECT b.id_batch, b.id_product, b.batch_number, b.current_quantity, b.expiration_date, b.initial_quantity, p.product_name FROM batches b LEFT JOIN products p ON b.id_product = p.id_product ORDER BY b.expiration_date ASC")->fetchAll(PDO::FETCH_ASSOC);
$movements_data = $pdo->query("SELECT im.id_movement, b.id_batch AS batch_id, p.product_name AS product_name, im.quantity, mt.type_name AS type, im.justification, im.movement_date, u.full_name AS user_name FROM inventory_movements im LEFT JOIN batches b ON im.id_batch = b.id_batch LEFT JOIN products p ON b.id_product = p.id_product LEFT JOIN users u ON im.id_user = u.id_user LEFT JOIN movement_types mt ON im.id_movement_type = mt.id_type ORDER BY im.movement_date DESC")->fetchAll(PDO::FETCH_ASSOC);
$sales_data = $pdo->query("SELECT s.id_sale, s.id_patient, CONCAT(p.first_name,' ',p.last_name) AS patient_name, s.total, s.sale_date, u.full_name AS user_name, ss.status_name AS status FROM sales s LEFT JOIN patients p ON s.id_patient = p.id_patient LEFT JOIN users u ON s.id_user = u.id_user LEFT JOIN sale_statuses ss ON s.id_sale_status = ss.id_status ORDER BY s.sale_date DESC")->fetchAll(PDO::FETCH_ASSOC);
$sale_details = $pdo->query("SELECT sd.id_sale, p.product_name, sd.quantity, sd.unit_price, sd.subtotal, sd.id_movement FROM sale_details sd LEFT JOIN products p ON sd.id_product = p.id_product")->fetchAll(PDO::FETCH_ASSOC);

?>
<!-- inicio documento HTML -->
<!DOCTYPE html>
<html lang="es">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <style>
        /* Estilos generales */
        * { margin: 0; padding: 0; }
        body { font-family: Arial; background: #f0f0f0; }
        .header { background: #957698; color: white; padding: 15px; display: flex; justify-content: space-between; }
        .tabs { background: white; display: flex; border-bottom: 1px solid #ddd; }
        .tab-btn { padding: 10px 15px; background: none; border: none; cursor: pointer; border-bottom: 3px solid transparent; }
        .tab-btn.active { color: #007bff; border-bottom-color: #007bff; }
        .container { max-width: 1000px; margin: 0 auto; background: white; }
        .content { padding: 20px; }
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        .logout-btn { background: #dc3545; color: white; padding: 8px 15px; border: none; cursor: pointer; text-decoration: none; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f8f9fa; font-weight: bold; }
        tr:hover { background: #f5f5f5; }
        .stock-bajo { color: #dc3545; font-weight: bold; }
        .stock-ok { color: #28a745; }
        .badge { padding: 4px 8px; border-radius: 4px; font-size: 12px; }
        .badge-blue { background: #e7f3ff; color: #0056b3; }
        .badge-green { background: #e8f5e9; color: #2e7d32; }
        .badge-orange { background: #fff3e0; color: #e65100; }
        /* Estilo del modal */
        .modal { display: none; position: fixed; z-index: 1; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.4); }
        .modal-content { background-color: #fefefe; margin: 5% auto; padding: 0; border: 1px solid #888; width: 90%; max-width: 500px; border-radius: 8px; }
        .modal-header { padding: 15px 20px; background: #957698; color: white; border-radius: 8px 8px 0 0; }
        .modal-body { padding: 20px; }
        .modal-footer { padding: 15px 20px; background: #f8f9fa; border-radius: 0 0 8px 8px; text-align: right; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input, .form-group select { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        .btn { padding: 8px 15px; border: none; border-radius: 4px; cursor: pointer; margin-left: 5px; }
        .btn-primary { background: #007bff; color: white; }
        .btn-secondary { background: #6c757d; color: white; }
        .btn-danger { background: #dc3545; color: white; }
    </style>
</head>




<body>
    <!-- encabezado con logo y nombre de usuario -->
    <div class="header">
        <h2>WhiteCare</h2>
        <div>
            <span><?php echo htmlspecialchars($_SESSION['fullname'] ?? $_SESSION['username']); ?></span>
            <a href="logout.php" class="logout-btn">Salir</a>
        </div>
    </div>

    <!-- navegación de pestañas -->
    <div class="tabs">
        <?php foreach ($allowed_tabs as $key): ?>
            <button class="tab-btn <?php echo $tab === $key ? 'active' : ''; ?>" onclick="tab('<?php echo $key; ?>')"><?php echo htmlspecialchars($all_tabs[$key]); ?></button>
        <?php endforeach; ?>
    </div>




    <!-- contenedor principal -->
    <div class="container">
        <div class="content">
            <?php if (!empty($flash)): ?>
                <div style="background:#d4edda;border:1px solid #c3e6cb;padding:12px;margin-bottom:16px;color:#155724;border-radius:4px;"><?php echo htmlspecialchars($flash); ?></div>
            <?php endif; ?>

            <?php include __DIR__ . '/modules/authentication.php'; ?>
            <?php include __DIR__ . '/modules/appointments.php'; ?>
            <?php include __DIR__ . '/modules/inventory.php'; ?>
            <?php include __DIR__ . '/modules/sales.php'; ?>


  <script>
const tabLabels = {
 "authentication":"Autenticación y Seguridad",
 "appointments":"Pacientes y Citas",
 "inventory":"Inventario",
 "sales":"Ventas y Cobros"
};

function tab(value){

 const sections = ['authentication','appointments','inventory','sales'];

 sections.forEach(section => {
   const el = document.getElementById(section);
   if(el) el.style.display = section === value ? 'block' : 'none';
 });

 history.replaceState(null,null,'?tab='+value);
}

tab('authentication');
</script>
    </script>

   <script>
    fetch("api/appointments_api.php")
    .then(res => res.json())
    .then(data => console.log(data))

    fetch("api/appointments_api.php", {
  method: "POST",
  headers: {
    "Content-Type": "application/json"
  },
  body: JSON.stringify({
    patient_id: 1,
    assigned_user: 2,
    appointment_date: "2026-03-20"
  })
  })
   </script>

</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</html>
