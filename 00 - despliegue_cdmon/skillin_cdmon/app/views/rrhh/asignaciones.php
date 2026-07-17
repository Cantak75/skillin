<div class="topbar">
    <h1>Asignación de juegos</h1>
    <?php if (Auth::isAdministrador()): ?>
        <form method="GET" action="<?= BASE_URL ?>/rrhh/asignaciones">
            <select name="empresa" onchange="this.form.submit()" style="margin:0;">
                <?php foreach ($empresas as $e): ?>
                    <option value="<?= $e['id_empresa'] ?>" <?= $e['id_empresa'] == $empresaActual ? 'selected' : '' ?>><?= htmlspecialchars($e['nombre']) ?></option>
                <?php endforeach; ?>
            </select>
        </form>
    <?php endif; ?>
</div>

<?php if (isset($_GET['aviso']) && $_GET['aviso'] === 'omitidos'): ?>
    <div class="alert alert-error">
        <?= (int)($_GET['n'] ?? 0) ?> trabajador(es) no se han asignado porque ya tienen ese juego pendiente, en curso o caducado. Solo se puede reasignar un juego cuando todas sus asignaciones previas están completadas.
    </div>
<?php endif; ?>

<div class="card">
    <h2>Nueva asignación</h2>
    <p style="font-size:13.5px; color:var(--texto-suave);">Selecciona un juego y uno o varios trabajadores (RF5: asignación individual o por grupo).</p>
    <form method="POST" action="<?= BASE_URL ?>/rrhh/asignaciones/asignar">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">

        <div class="grid grid-2" style="gap:20px;">
            <div>
                <label>Juego</label>
                <select name="id_juego" required>
                    <option value="">Selecciona un juego…</option>
                    <?php foreach ($juegos as $j): ?>
                        <option value="<?= $j['id_juego'] ?>"><?= htmlspecialchars($j['titulo']) ?></option>
                    <?php endforeach; ?>
                </select>

                <label>Fecha límite (opcional)</label>
                <input type="date" name="fecha_limite">
            </div>

            <div>
                <label>Trabajadores</label>
                <div style="max-height:220px; overflow-y:auto; border:1px solid var(--gris-borde); border-radius:8px; padding:10px;">
                    <?php if (empty($trabajadores)): ?>
                        <p style="font-size:13.5px; color:var(--texto-suave); margin:0;">No hay trabajadores registrados.</p>
                    <?php endif; ?>
                    <?php foreach ($trabajadores as $t): ?>
                        <label style="font-weight:400; display:flex; align-items:center; gap:8px; margin-bottom:8px;">
                            <input type="checkbox" name="trabajadores[]" value="<?= $t['id_usuario'] ?>" style="width:auto; margin:0;">
                            <?= htmlspecialchars($t['nombre'] . ' ' . $t['apellidos']) ?>
                            <span style="color:var(--texto-suave); font-size:12.5px;">(<?= htmlspecialchars($t['departamento'] ?: 'Sin depto.') ?>)</span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <button class="btn btn-primary" type="submit" style="margin-top:16px;">Asignar juego</button>
    </form>
</div>

<div class="card">
    <h2>Asignaciones activas</h2>
    <?php if (empty($asignaciones)): ?>
        <div class="empty-state">Todavía no hay asignaciones.</div>
    <?php else: ?>
        <table>
            <thead><tr><th>Trabajador</th><th>Juego</th><th>Fecha límite</th><th>Estado</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($asignaciones as $a): ?>
                <tr>
                    <td><?= htmlspecialchars($a['nombre'] . ' ' . $a['apellidos']) ?></td>
                    <td><?= htmlspecialchars($a['juego_titulo']) ?></td>
                    <td><?= $a['fecha_limite'] ? htmlspecialchars($a['fecha_limite']) : '—' ?></td>
                    <td><span class="badge badge-<?= $a['estado'] ?>"><?= ucfirst(str_replace('_',' ',$a['estado'])) ?></span></td>
                    <td>
                        <form method="POST" action="<?= BASE_URL ?>/rrhh/asignaciones/<?= $a['id_asignacion'] ?>/eliminar" onsubmit="return confirm('¿Eliminar esta asignación?');">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                            <button class="btn btn-danger btn-sm" type="submit">Eliminar</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
