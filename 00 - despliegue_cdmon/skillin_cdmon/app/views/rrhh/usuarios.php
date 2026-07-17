<div class="topbar">
    <h1>Gestión de plantilla</h1>
    <form method="GET" action="<?= BASE_URL ?>/rrhh/usuarios" style="display:flex; gap:8px;">
        <?php if (Auth::isAdministrador()): ?>
            <select name="empresa" onchange="this.form.submit()" style="margin:0;">
                <?php foreach ($empresas as $e): ?>
                    <option value="<?= $e['id_empresa'] ?>" <?= $e['id_empresa'] == $empresaActual ? 'selected' : '' ?>><?= htmlspecialchars($e['nombre']) ?></option>
                <?php endforeach; ?>
            </select>
        <?php endif; ?>
        <input type="text" name="q" placeholder="Buscar por nombre o email…" value="<?= htmlspecialchars($busqueda) ?>" style="margin:0; width:240px;">
        <button class="btn btn-outline btn-sm" type="submit">Buscar</button>
    </form>
</div>

<?php if (isset($_GET['error']) && $_GET['error'] === 'email_duplicado'): ?>
    <div class="alert alert-error">Ya existe un usuario con ese correo electrónico.</div>
<?php endif; ?>
<?php if (isset($_GET['error']) && $_GET['error'] === 'empresa_duplicada'): ?>
    <div class="alert alert-error">Ya existe una empresa con ese nombre.</div>
<?php endif; ?>

<div class="grid grid-2">
    <div class="card" style="grid-column: span 2;">
        <h2>Añadir nuevo trabajador / RRHH</h2>
        <form method="POST" action="<?= BASE_URL ?>/rrhh/usuarios/crear">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
            <div class="grid grid-4" style="gap:12px;">
                <div><label>Nombre</label><input type="text" name="nombre" required></div>
                <div><label>Apellidos</label><input type="text" name="apellidos" required></div>
                <div><label>Email</label><input type="email" name="email" required></div>
                <div><label>Departamento</label><input type="text" name="departamento"></div>
            </div>

            <?php if (Auth::isAdministrador()): ?>
                <div class="grid grid-2" style="gap:12px; align-items:end;">
                    <div id="wrapperEmpresaSelect">
                        <label>Empresa</label>
                        <select name="id_empresa">
                            <?php foreach ($empresas as $e): ?>
                                <option value="<?= $e['id_empresa'] ?>" <?= $e['id_empresa'] == $empresaActual ? 'selected' : '' ?>><?= htmlspecialchars($e['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label style="visibility:hidden;">Acción</label>
                        <label style="font-weight:400; display:flex; align-items:center; gap:6px; margin-bottom:16px; color:var(--texto);">
                            <input type="checkbox" style="width:auto; margin:0;"
                                   onchange="wrapperEmpresaSelect.style.display = this.checked ? 'none' : ''; camposNuevaEmpresa.style.display = this.checked ? '' : 'none';">
                            + Dar de alta una empresa nueva
                        </label>
                    </div>
                </div>
                <div id="camposNuevaEmpresa" class="grid grid-2" style="gap:12px; display:none;">
                    <div><label>Nombre de la nueva empresa</label><input type="text" name="nueva_empresa_nombre"></div>
                    <div><label>Sector</label><input type="text" name="nueva_empresa_sector"></div>
                </div>
            <?php endif; ?>

            <div class="grid grid-3" style="gap:12px; align-items:end;">
                <div><label>Rol</label>
                    <select name="rol">
                        <option value="trabajador">Trabajador</option>
                        <option value="rrhh">RRHH</option>
                        <?php if (Auth::isAdministrador()): ?>
                            <option value="administrador">Administrador</option>
                        <?php endif; ?>
                    </select>
                </div>
                <div><label>Contraseña inicial</label><input type="text" name="password" value="Skillin2026!" required></div>
                <div><label style="visibility:hidden;">Acción</label><button class="btn btn-primary btn-block" type="submit" style="margin-bottom:16px; border:1px solid transparent; font-size:14.5px;">Crear cuenta</button></div>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <h2>Plantilla (<?= count($trabajadores) ?>)</h2>
    <?php if (empty($trabajadores)): ?>
        <div class="empty-state">No se encontraron trabajadores.</div>
    <?php else: ?>
        <table>
            <thead><tr><th>Nombre</th><th>Email</th><th>Departamento</th><?php if (Auth::isAdministrador()): ?><th>Rol</th><?php endif; ?><th>Estado</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($trabajadores as $t): ?>
                <tr>
                    <td><?= htmlspecialchars($t['nombre'] . ' ' . $t['apellidos']) ?></td>
                    <td><?= htmlspecialchars($t['email']) ?></td>
                    <td><?= htmlspecialchars($t['departamento'] ?: '—') ?></td>
                    <?php if (Auth::isAdministrador()): ?>
                        <td><?= ['trabajador' => 'Trabajador', 'rrhh' => 'RRHH', 'administrador' => 'Administrador'][$t['rol']] ?? ucfirst($t['rol']) ?></td>
                    <?php endif; ?>
                    <td>
                        <?php if ($t['activo']): ?>
                            <span class="badge badge-completado">Activo</span>
                        <?php else: ?>
                            <span class="badge badge-caducado">Desactivado</span>
                        <?php endif; ?>
                    </td>
                    <td style="white-space:nowrap;">
                        <form method="POST" action="<?= BASE_URL ?>/rrhh/usuarios/<?= $t['id_usuario'] ?>/toggle" style="display:inline;">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                            <button class="btn btn-outline btn-sm" type="submit"><?= $t['activo'] ? 'Desactivar' : 'Activar' ?></button>
                        </form>
                        <form method="POST" action="<?= BASE_URL ?>/rrhh/usuarios/<?= $t['id_usuario'] ?>/eliminar" style="display:inline;" onsubmit="return confirm('¿Eliminar definitivamente este usuario?');">
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
