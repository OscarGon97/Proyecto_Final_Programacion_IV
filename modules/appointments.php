<div id="appointments" class="tab-content <?php echo $tab === 'appointments' ? 'active' : ''; ?>">
    <h2>Pacientes y Citas</h2>
    <form method="post" style="border:1px solid #ddd;padding:12px;margin-bottom:16px;">
        <input type="hidden" name="action" value="add_appointment">
        <h4>Crear cita</h4>
        <div class="form-group"><label>Paciente</label><select name="patient_id" required><option value="">Selecciona</option><?php foreach($patients_data as $p){echo '<option value="'.htmlspecialchars($p['id_patient']).'">'.htmlspecialchars($p['full_name']).'</option>';} ?></select></div>
        <div class="form-group"><label>Médico</label><select name="assigned_user" required><option value="">Selecciona</option><?php foreach($users_data as $u){echo '<option value="'.htmlspecialchars($u['id_user']).'">'.htmlspecialchars($u['full_name']).'</option>';} ?></select></div>
        <div class="form-group"><label>Fecha y hora</label><input type="datetime-local" name="scheduled_at" required></div>
        <div class="form-group"><label>Motivo</label><input type="text" name="reason" required></div>
        <button class="btn btn-primary" type="submit">Guardar cita</button>
    </form>

    <table>
        <thead><tr><th>ID</th><th>Paciente</th><th>Médico</th><th>Programada</th><th>Estado</th><th>Motivo</th><th>Acciones</th></tr></thead>
        <tbody>
            <?php foreach ($appointments_data as $a): ?>
                <tr>
                    <td><?php echo htmlspecialchars($a['id_appointment']); ?></td>
                    <td><?php echo htmlspecialchars($a['patient_name']); ?></td>
                    <td><?php echo htmlspecialchars($a['doctor_name']); ?></td>
                    <td><?php echo htmlspecialchars($a['appointment_date'] . ' ' . $a['appointment_time']); ?></td>
                    <td><?php echo htmlspecialchars($a['status']); ?></td>
                    <td><?php echo htmlspecialchars($a['reason']); ?></td>
                    <td>
                        <?php if (strtolower($a['status']) !== 'attended' && strtolower($a['status']) !== 'completed'): ?>
                            <form method="post" style="display:inline;"><input type="hidden" name="action" value="close_appointment"><input type="hidden" name="appointment_id" value="<?php echo htmlspecialchars($a['id_appointment']); ?>"><input type="text" name="diagnostic" placeholder="Diagnóstico" required><input type="text" name="treatment" placeholder="Tratamiento" required><button class="btn btn-primary" type="submit">Cerrar</button></form>
                        <?php else: ?>
                            <span>Finalizada</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
