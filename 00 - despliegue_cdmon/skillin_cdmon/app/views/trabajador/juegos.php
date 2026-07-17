<div class="topbar">
    <h1>Mis juegos</h1>
</div>

<?php if (empty($asignaciones)): ?>
    <div class="card"><div class="empty-state">No tienes juegos asignados todavía.</div></div>
<?php else: ?>
    <div class="grid grid-3">
        <?php foreach ($asignaciones as $a): ?>
            <div class="card game-card">
                <?php if (!empty($a['imagen'])): ?>
                    <img src="<?= BASE_URL ?>/uploads/juegos/<?= htmlspecialchars($a['imagen']) ?>" alt="" class="game-card-img">
                <?php else: ?>
                    <div class="game-card-img game-card-img-placeholder">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="6" width="20" height="12" rx="6"/><line x1="6" y1="12" x2="10" y2="12"/><line x1="8" y1="10" x2="8" y2="14"/><circle cx="15" cy="13" r="1"/><circle cx="18" cy="11" r="1"/></svg>
                    </div>
                <?php endif; ?>
                <span class="badge badge-<?= $a['dificultad'] ?>"><?= ucfirst($a['dificultad']) ?></span>
                <h3 style="margin-top:10px;"><?= htmlspecialchars($a['titulo']) ?></h3>
                <p style="color:var(--texto-suave); font-size:13.5px; min-height:40px;"><?= htmlspecialchars($a['descripcion']) ?></p>
                <p style="font-size:13px;"><strong>Competencia:</strong> <?= htmlspecialchars($a['tipo_competencia']) ?></p>
                <p style="font-size:13px;"><span class="badge badge-<?= $a['estado'] ?>"><?= ucfirst(str_replace('_',' ',$a['estado'])) ?></span></p>
                <?php if ($a['estado'] !== 'completado'): ?>
                    <a class="btn btn-primary btn-block" href="<?= BASE_URL ?>/juegos/jugar/<?= $a['id_asignacion'] ?>">Jugar ahora</a>
                <?php else: ?>
                    <a class="btn btn-outline btn-block" href="<?= BASE_URL ?>/juegos/jugar/<?= $a['id_asignacion'] ?>">Volver a intentar</a>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
