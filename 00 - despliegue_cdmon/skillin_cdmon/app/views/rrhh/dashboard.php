<div class="topbar">
    <h1>Panel RRHH</h1>
    <?php if (Auth::isAdministrador()): ?>
        <form method="GET" action="<?= BASE_URL ?>/dashboard">
            <select name="empresa" onchange="this.form.submit()" style="margin:0;">
                <?php foreach ($empresas as $e): ?>
                    <option value="<?= $e['id_empresa'] ?>" <?= $e['id_empresa'] == $empresaActual ? 'selected' : '' ?>><?= htmlspecialchars($e['nombre']) ?></option>
                <?php endforeach; ?>
            </select>
        </form>
    <?php endif; ?>
</div>

<div class="grid grid-3">
    <div class="stat-card">
        <div class="valor"><?= $totalTrabajadores ?></div>
        <div class="etiqueta">Trabajadores activos</div>
    </div>
    <div class="stat-card">
        <div class="valor"><?= $totalPendientes ?></div>
        <div class="etiqueta">Asignaciones pendientes</div>
    </div>
    <div class="stat-card">
        <div class="valor"><?= array_sum(array_column($estadisticas, 'total_partidas')) ?></div>
        <div class="etiqueta">Partidas jugadas (total)</div>
    </div>
</div>

<div class="grid grid-2">
    <div class="card">
        <h2>Rendimiento medio por juego</h2>
        <?php if (empty($estadisticas)): ?>
            <div class="empty-state">Todavía no hay resultados registrados.</div>
        <?php else: ?>
            <table>
                <thead><tr><th>Juego</th><th>Partidas</th><th>Media</th></tr></thead>
                <tbody>
                <?php foreach ($estadisticas as $e): ?>
                    <tr>
                        <td><?= htmlspecialchars($e['titulo']) ?></td>
                        <td><?= $e['total_partidas'] ?></td>
                        <td><strong><?= $e['media_puntuacion'] ?></strong> pts</td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <div class="card">
        <h2>Últimas asignaciones</h2>
        <?php if (empty($asignaciones)): ?>
            <div class="empty-state">No se han asignado juegos todavía.</div>
        <?php else: ?>
            <table>
                <thead><tr><th>Trabajador</th><th>Juego</th><th>Estado</th></tr></thead>
                <tbody>
                <?php foreach ($asignaciones as $a): ?>
                    <tr>
                        <td><?= htmlspecialchars($a['nombre'] . ' ' . $a['apellidos']) ?></td>
                        <td><?= htmlspecialchars($a['juego_titulo']) ?></td>
                        <td><span class="badge badge-<?= $a['estado'] ?>"><?= ucfirst(str_replace('_',' ',$a['estado'])) ?></span></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
        <p style="margin-top:14px;"><a href="<?= BASE_URL ?>/rrhh/asignaciones">Gestionar asignaciones →</a></p>
    </div>
</div>
