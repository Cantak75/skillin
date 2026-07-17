<div class="topbar">
    <h1>Catálogo de juegos</h1>
</div>

<div class="card">
    <h2>Añadir juego</h2>
    <form method="POST" action="<?= BASE_URL ?>/rrhh/juegos/crear" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf ?? '') ?>">
        <div class="grid grid-2" style="gap:12px;">
            <div><label>Título</label><input type="text" name="titulo" required></div>
            <div><label>Slug del motor (quiz / memoria / reaccion)</label>
                <select name="slug" required>
                    <option value="quiz">quiz</option>
                    <option value="memoria">memoria</option>
                    <option value="reaccion">reaccion</option>
                </select>
            </div>
        </div>
        <label>Descripción</label>
        <textarea name="descripcion" rows="2"></textarea>
        <div class="grid grid-3" style="gap:12px; align-items:end;">
            <div><label>Tipo de competencia</label><input type="text" name="tipo_competencia" placeholder="Memoria, atención..."></div>
            <div><label>Dificultad</label>
                <select name="dificultad">
                    <option value="facil">Fácil</option>
                    <option value="media">Media</option>
                    <option value="dificil">Difícil</option>
                </select>
            </div>
            <div><label><input type="checkbox" name="activo" value="1" checked style="width:auto; display:inline-block; margin-right:6px;">Activo</label>
            </div>
        </div>
        <div class="grid grid-2" style="gap:12px; align-items:end;">
            <div>
                <label>Imagen de cabecera (JPG, PNG o WEBP, máx. 2 MB)</label>
                <label for="imagenNueva" class="file-upload-link">📷 Seleccionar imagen…</label>
                <span id="imagenNuevaNombre" class="file-upload-name"></span>
                <input type="file" id="imagenNueva" name="imagen" accept="image/jpeg,image/png,image/webp"
                       onchange="document.getElementById('imagenNuevaNombre').textContent = this.files[0] ? this.files[0].name : '';">
            </div>
            <div><button class="btn btn-primary btn-block" type="submit">Guardar juego</button></div>
        </div>
    </form>
</div>

<div class="card">
    <h2>Juegos existentes (<?= count($juegos) ?>)</h2>
    <table>
        <thead><tr><th>Imagen</th><th>Título</th><th>Dificultad</th><th>Competencia</th><th>Estado</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($juegos as $j): ?>
            <tr>
                <td>
                    <form method="POST" action="<?= BASE_URL ?>/rrhh/juegos/<?= $j['id_juego'] ?>/imagen" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf ?? '') ?>">
                        <label for="imagenJuego<?= $j['id_juego'] ?>" class="game-thumb-upload" title="Cambiar imagen">
                            <?php if (!empty($j['imagen'])): ?>
                                <img src="<?= BASE_URL ?>/uploads/juegos/<?= htmlspecialchars($j['imagen']) ?>" alt="" class="game-thumb">
                            <?php else: ?>
                                <div class="game-thumb game-thumb-placeholder">+ Subir</div>
                            <?php endif; ?>
                        </label>
                        <input type="file" id="imagenJuego<?= $j['id_juego'] ?>" name="imagen"
                               accept="image/jpeg,image/png,image/webp" onchange="this.form.submit()">
                    </form>
                </td>
                <td><?= htmlspecialchars($j['titulo']) ?></td>
                <td><span class="badge badge-<?= $j['dificultad'] ?>"><?= ucfirst($j['dificultad']) ?></span></td>
                <td><?= htmlspecialchars($j['tipo_competencia'] ?: '—') ?></td>
                <td><?= $j['activo'] ? '<span class="badge badge-completado">Activo</span>' : '<span class="badge badge-caducado">Inactivo</span>' ?></td>
                <td style="white-space:nowrap;">
                    <form method="POST" action="<?= BASE_URL ?>/rrhh/juegos/<?= $j['id_juego'] ?>/eliminar" style="display:inline;" onsubmit="return confirm('¿Eliminar este juego del catálogo?');">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf ?? '') ?>">
                        <button class="btn btn-danger btn-sm" type="submit">Eliminar</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
