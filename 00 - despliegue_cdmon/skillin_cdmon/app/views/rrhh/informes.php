<div class="topbar">
    <h1>Informes y analíticas</h1>
</div>

<div class="card">
    <h2>Rendimiento por juego</h2>
    <form method="GET" action="<?= BASE_URL ?>/rrhh/informes" style="display:flex; gap:10px; align-items:end;">
        <?php if (Auth::isAdministrador()): ?>
            <div>
                <label>Empresa</label>
                <select name="empresa" onchange="this.form.submit()">
                    <?php foreach ($empresas as $e): ?>
                        <option value="<?= $e['id_empresa'] ?>" <?= $e['id_empresa'] == $empresaActual ? 'selected' : '' ?>><?= htmlspecialchars($e['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php endif; ?>
        <div style="flex:1;">
            <label>Selecciona un juego</label>
            <select name="id_juego" onchange="this.form.submit()">
                <option value="">— Elegir juego —</option>
                <?php foreach ($juegos as $j): ?>
                    <option value="<?= $j['id_juego'] ?>" <?= $idJuego == $j['id_juego'] ? 'selected' : '' ?>><?= htmlspecialchars($j['titulo']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php if ($idJuego): ?>
            <div>
                <label style="visibility:hidden;">Acción</label>
                <a class="btn btn-outline" href="<?= BASE_URL ?>/rrhh/informes/exportar/<?= $idJuego ?>" style="display:block; text-align:center; margin-bottom:16px; border-width:1px; font-size:14.5px;">⬇ Exportar CSV</a>
            </div>
        <?php endif; ?>
    </form>

    <?php if ($juegoSeleccionado): ?>
        <?php if (empty($rendimiento)): ?>
            <div class="empty-state" style="margin-top:20px;">Todavía no hay resultados para "<?= htmlspecialchars($juegoSeleccionado['titulo']) ?>".</div>
        <?php else: ?>
            <div class="grid grid-2" style="margin-top:20px;">
                <div>
                    <canvas id="graficoRendimiento" height="220"></canvas>
                </div>
                <div style="max-height:280px; overflow-y:auto;">
                    <table>
                        <thead><tr><th>Trabajador</th><th>Depto.</th><th>Puntuación</th><th>Tiempo</th></tr></thead>
                        <tbody>
                        <?php foreach ($rendimiento as $r): ?>
                            <tr>
                                <td><?= htmlspecialchars($r['nombre'] . ' ' . $r['apellidos']) ?></td>
                                <td><?= htmlspecialchars($r['departamento'] ?: '—') ?></td>
                                <td><strong><?= $r['puntuacion'] ?></strong></td>
                                <td><?= $r['tiempo_empleado'] ?> s</td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
            <script>
                const ctx = document.getElementById('graficoRendimiento');
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: <?= json_encode(array_map(fn($r) => $r['nombre'], $rendimiento)) ?>,
                        datasets: [{
                            label: 'Puntuación',
                            data: <?= json_encode(array_map(fn($r) => (int)$r['puntuacion'], $rendimiento)) ?>,
                            backgroundColor: '#14b8a6'
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: { legend: { display: false } },
                        scales: { y: { beginAtZero: true } }
                    }
                });
            </script>
        <?php endif; ?>
    <?php endif; ?>
</div>

<div class="grid grid-2">
    <div class="card">
        <h2>Media general por juego</h2>
        <?php if (empty($estadisticas)): ?>
            <div class="empty-state">Sin datos todavía.</div>
        <?php else: ?>
            <canvas id="graficoGeneral" height="200"></canvas>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
            <script>
                new Chart(document.getElementById('graficoGeneral'), {
                    type: 'bar',
                    data: {
                        labels: <?= json_encode(array_map(fn($e) => $e['titulo'], $estadisticas)) ?>,
                        datasets: [{
                            label: 'Media de puntuación',
                            data: <?= json_encode(array_map(fn($e) => (float)$e['media_puntuacion'], $estadisticas)) ?>,
                            backgroundColor: '#0f3554'
                        }]
                    },
                    options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
                });
            </script>
        <?php endif; ?>
    </div>

    <div class="card">
        <h2>Generar informe formal</h2>
        <form method="POST" action="<?= BASE_URL ?>/rrhh/informes/generar">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
            <label>Tipo de informe</label>
            <select name="tipo">
                <option value="rendimiento">Rendimiento</option>
                <option value="participacion">Participación</option>
                <option value="competencias">Competencias entrenadas</option>
            </select>
            <label>Observaciones</label>
            <textarea name="observaciones" rows="3" placeholder="Notas u observaciones del informe..."></textarea>
            <button class="btn btn-primary" type="submit">Guardar informe</button>
        </form>

        <h3 style="margin-top:20px;">Informes generados</h3>
        <?php if (empty($informes)): ?>
            <div class="empty-state">Todavía no se han generado informes.</div>
        <?php else: ?>
            <table>
                <thead><tr><th>Fecha</th><th>Tipo</th><th>Generado por</th></tr></thead>
                <tbody>
                <?php foreach (array_slice($informes, 0, 6) as $i): ?>
                    <tr>
                        <td><?= htmlspecialchars($i['fecha_generacion']) ?></td>
                        <td><?= ucfirst(htmlspecialchars($i['tipo'])) ?></td>
                        <td><?= htmlspecialchars($i['nombre'] . ' ' . $i['apellidos']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
