<div id="inventory" class="tab-content <?php echo $tab === 'inventory' ? 'active' : ''; ?>">
    <h2>Inventario</h2>
    <form method="post" style="border:1px solid #ddd;padding:12px;margin-bottom:16px;">
        <input type="hidden" name="action" value="add_batch">
        <h4>Agregar lote</h4>
        <div class="form-group"><label>Producto</label><select name="product_id" required><option value="">Selecciona</option><?php foreach($products_data as $p){echo '<option value="'.htmlspecialchars($p['id_product']).'">'.htmlspecialchars($p['product_name']).'</option>';} ?></select></div>
        <div class="form-group"><label>Batch</label><input type="text" name="batch_number" required></div>
        <div class="form-group"><label>Cantidad</label><input type="number" step="0.01" name="quantity" required></div>
        <div class="form-group"><label>Fecha de vencimiento</label><input type="date" name="expiry_date" required></div>
        <button class="btn btn-primary" type="submit">Agregar lote</button>
    </form>

    <table>
        <thead><tr><th>ID</th><th>Producto</th><th>Inicial</th><th>Actual</th><th>Vence</th></tr></thead>
        <tbody><?php foreach ($batches_data as $b): ?><tr><td><?php echo htmlspecialchars($b['id_batch']); ?></td><td><?php echo htmlspecialchars($b['product_name'] ?: 'ID '.$b['id_product']); ?></td><td><?php echo htmlspecialchars($b['initial_quantity']); ?></td><td><?php echo htmlspecialchars($b['current_quantity']); ?></td><td><?php echo htmlspecialchars($b['expiration_date']); ?></td></tr><?php endforeach; ?></tbody>
    </table>

    <h4 style="margin-top:18px;">Movimientos</h4>
    <form method="post" style="border:1px solid #ddd;padding:12px;margin-bottom:16px;">
        <input type="hidden" name="action" value="add_inventory_movement">
        <div class="form-group"><label>Lote</label><select name="batch_id" required><option value="">Selecciona</option><?php foreach($batches_data as $b){echo '<option value="'.htmlspecialchars($b['id_batch']).'">'.htmlspecialchars($b['id_batch'].' - '.($b['product_name']?:$b['id_product']).' ('.(float)$b['current_quantity'].')').'</option>';} ?></select></div>
        <div class="form-group"><label>Tipo</label><select name="movement_type" required><option value="1">Entrada</option><option value="2">Salida</option></select></div>
        <div class="form-group"><label>Cantidad</label><input type="number" step="0.01" name="movement_quantity" required></div>
        <div class="form-group"><label>Justificación</label><input type="text" name="movement_reason" required></div>
        <button class="btn btn-primary" type="submit">Registrar movimiento</button>
    </form>

    <table>
        <thead><tr><th>ID</th><th>Lote</th><th>Producto</th><th>Cantidad</th><th>Tipo</th><th>Justificación</th><th>Usuario</th><th>Fecha</th></tr></thead>
        <tbody><?php foreach ($movements_data as $m): ?><tr><td><?php echo htmlspecialchars($m['id_movement']); ?></td><td><?php echo htmlspecialchars($m['batch_id']); ?></td><td><?php echo htmlspecialchars($m['product_name']); ?></td><td><?php echo htmlspecialchars($m['quantity']); ?></td><td><?php echo htmlspecialchars($m['type']); ?></td><td><?php echo htmlspecialchars($m['justification']); ?></td><td><?php echo htmlspecialchars($m['user_name']); ?></td><td><?php echo htmlspecialchars($m['movement_date']); ?></td></tr><?php endforeach; ?></tbody>
    </table>
</div>
