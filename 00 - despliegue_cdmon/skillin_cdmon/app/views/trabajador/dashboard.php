<div class="topbar">
    <h1>Hola, <?= htmlspecialchars(Auth::user()['nombre']) ?> 👋</h1>
</div>

<div class="grid grid-3">
    <div class="stat-card">
        <div class="valor"><?= count($pendientes) ?></div>
        <div class="etiqueta">Juegos pendientes</div>
    </div>
    <div class="stat-card">
        <div class="valor"><?= $totalPartidas ?></div>
        <div class="etiqueta">Partidas jugadas</div>
    </div>
    <div class="stat-card">
        <div class="valor"><?= $ultimoResultado ? $ultimoResultado['puntuacion'] : '—' ?></div>
        <div class="etiqueta">Última puntuación<?= $ultimoResultado ? ' · ' . htmlspecialchars($ultimoResultado['juego_titulo']) : '' ?></div>
    </div>
</div>

<div class="card">
    <h2>Tus tareas asignadas</h2>
    <?php if (empty($asignaciones)): ?>
        <div class="empty-state">Todavía no tienes juegos asignados. RRHH te asignará entrenamientos pronto.</div>
    <?php else: ?>
        <table>
            <thead>
                <tr><th>Juego</th><th>Dificultad</th><th>Fecha límite</th><th>Estado</th><th></th></tr>
            </thead>
            <tbody>
            <?php foreach (array_slice($asignaciones, 0, 6) as $a): ?>
                <tr>
                    <td><?= htmlspecialchars($a['titulo']) ?></td>
                    <td><span class="badge badge-<?= $a['dificultad'] ?>"><?= ucfirst($a['dificultad']) ?></span></td>
                    <td><?= $a['fecha_limite'] ? htmlspecialchars($a['fecha_limite']) : '—' ?></td>
                    <td><span class="badge badge-<?= $a['estado'] ?>"><?= ucfirst(str_replace('_', ' ', $a['estado'])) ?></span></td>
                    <td>
                        <?php if ($a['estado'] !== 'completado'): ?>
                            <a class="btn btn-primary btn-sm" href="<?= BASE_URL ?>/juegos/jugar/<?= $a['id_asignacion'] ?>">Jugar</a>
                        <?php else: ?>
                            <a class="btn btn-outline btn-sm" href="<?= BASE_URL ?>/progreso">Ver resultado</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <p style="margin-top:14px;"><a href="<?= BASE_URL ?>/juegos">Ver todos mis juegos →</a></p>
    <?php endif; ?>
</div>
