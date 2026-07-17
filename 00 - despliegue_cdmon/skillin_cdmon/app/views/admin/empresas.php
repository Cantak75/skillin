<div class="topbar">
    <h1>Empresas</h1>
</div>

<?php if (isset($_GET['ok'])): ?>
    <div class="alert alert-success">Empresa creada correctamente.</div>
<?php endif; ?>
<?php if (isset($_GET['error']) && $_GET['error'] === 'nombre_requerido'): ?>
    <div class="alert alert-error">El nombre de la empresa es obligatorio.</div>
<?php endif; ?>
<?php if (isset($_GET['error']) && $_GET['error'] === 'nombre_duplicado'): ?>
    <div class="alert alert-error">Ya existe una empresa con ese nombre.</div>
<?php endif; ?>

<div class="card">
    <h2>Añadir nueva empresa</h2>
    <form method="POST" action="<?= BASE_URL ?>/admin/empresas/crear">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
        <div class="grid grid-3" style="gap:12px; align-items:end;">
            <div><label>Nombre</label><input type="text" name="nombre" required></div>
            <div><label>Sector</label><input type="text" name="sector"></div>
            <div><button class="btn btn-primary btn-block" type="submit">Crear empresa</button></div>
        </div>
    </form>
</div>

<div class="card">
    <h2>Empresas registradas (<?= count($empresas) ?>)</h2>
    <?php if (empty($empresas)): ?>
        <div class="empty-state">No hay empresas registradas.</div>
    <?php else: ?>
        <table>
            <thead><tr><th>Nombre</th><th>Sector</th><th>Fecha de alta</th></tr></thead>
            <tbody>
            <?php foreach ($empresas as $e): ?>
                <tr>
                    <td><?= htmlspecialchars($e['nombre']) ?></td>
                    <td><?= htmlspecialchars($e['sector'] ?: '—') ?></td>
                    <td><?= htmlspecialchars($e['fecha_registro']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>