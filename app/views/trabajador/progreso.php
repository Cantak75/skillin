<div class="topbar">
    <h1>Mi progreso</h1>
</div>

<div class="card">
    <h2>Historial de partidas</h2>
    <?php if (empty($historial)): ?>
        <div class="empty-state">Aún no has completado ningún juego.</div>
    <?php else: ?>
        <table>
            <thead>
                <tr><th>Juego</th><th>Puntuación</th><th>Tiempo empleado</th><th>Fecha</th></tr>
            </thead>
            <tbody>
            <?php foreach ($historial as $h): ?>
                <tr>
                    <td><?= htmlspecialchars($h['juego_titulo']) ?></td>
                    <td><strong><?= $h['puntuacion'] ?></strong> pts</td>
                    <td><?= $h['tiempo_empleado'] ?> s</td>
                    <td><?= htmlspecialchars($h['fecha_realizacion']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
