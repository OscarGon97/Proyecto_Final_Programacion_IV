<div id="authentication" class="tab-content <?php echo $tab === 'authentication' ? 'active' : ''; ?>">
    <h2>Autenticación y Seguridad</h2>
    <p>Rol actual: <strong><?php echo htmlspecialchars($_SESSION['role_name'] ?? 'N/A'); ?></strong></p>
    <table>
        <thead>
            <tr><th>ID</th><th>Nombre</th><th>Email</th><th>Rol</th><th>Activo</th></tr>
        </thead>
        <tbody>
            <?php foreach ($users_data as $u): ?>
                <tr>
                    <td><?php echo htmlspecialchars($u['id_user']); ?></td>
                    <td><?php echo htmlspecialchars($u['full_name']); ?></td>
                    <td><?php echo htmlspecialchars($u['email']); ?></td>
                    <td><?php echo htmlspecialchars($u['id_role']); ?></td>
                    <td><?php echo $u['active'] == 1 ? 'Sí' : 'No'; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
