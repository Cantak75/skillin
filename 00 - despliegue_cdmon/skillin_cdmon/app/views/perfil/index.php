<div class="topbar">
    <h1>Mi perfil</h1>
</div>

<?php if (isset($_GET['ok'])): ?>
    <div class="alert alert-success">Cambios guardados correctamente.</div>
<?php endif; ?>
<?php if (isset($_GET['error']) && $_GET['error'] === 'password_actual'): ?>
    <div class="alert alert-error">La contraseña actual no es correcta.</div>
<?php endif; ?>
<?php if (isset($_GET['error']) && $_GET['error'] === 'password_invalida'): ?>
    <div class="alert alert-error">La nueva contraseña debe tener al menos 8 caracteres y coincidir en ambos campos.</div>
<?php endif; ?>
<?php if (isset($_GET['error']) && $_GET['error'] === 'foto_tamano'): ?>
    <div class="alert alert-error">La imagen no puede superar los 2 MB.</div>
<?php endif; ?>
<?php if (isset($_GET['error']) && $_GET['error'] === 'foto_formato'): ?>
    <div class="alert alert-error">Formato no soportado. Usa una imagen JPG, PNG o WEBP.</div>
<?php endif; ?>
<?php if (isset($_GET['error']) && $_GET['error'] === 'foto_invalida'): ?>
    <div class="alert alert-error">No se pudo subir la imagen. Inténtalo de nuevo.</div>
<?php endif; ?>

<div class="card">
    <h2>Foto de perfil</h2>
    <div style="display:flex; align-items:center; gap:24px;">
        <?php if (!empty($usuario['foto'])): ?>
            <img src="<?= BASE_URL ?>/uploads/avatars/<?= htmlspecialchars($usuario['foto']) ?>" alt="Foto de perfil" class="avatar avatar-lg">
        <?php else: ?>
            <div class="avatar avatar-lg avatar-placeholder"><?= htmlspecialchars(mb_strtoupper(mb_substr($usuario['nombre'], 0, 1))) ?></div>
        <?php endif; ?>
        <form method="POST" action="<?= BASE_URL ?>/perfil/foto" enctype="multipart/form-data" style="flex:1; margin:0;">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
            <label>Selecciona una imagen (JPG, PNG o WEBP, máx. 2 MB)</label>
            <input type="file" name="foto" accept="image/jpeg,image/png,image/webp" required>
            <button class="btn btn-primary" type="submit">Actualizar foto</button>
        </form>
    </div>
</div>

<div class="grid grid-2">
    <div class="card">
        <h2>Datos personales</h2>
        <form method="POST" action="<?= BASE_URL ?>/perfil/actualizar">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
            <label>Nombre</label>
            <input type="text" name="nombre" value="<?= htmlspecialchars($usuario['nombre']) ?>" required>
            <label>Apellidos</label>
            <input type="text" name="apellidos" value="<?= htmlspecialchars($usuario['apellidos']) ?>" required>
            <label>Email</label>
            <input type="email" name="email" value="<?= htmlspecialchars($usuario['email']) ?>" required>
            <label>Empresa</label>
            <input type="text" value="<?= htmlspecialchars($usuario['nombre_empresa']) ?>" disabled>
            <label>Departamento</label>
            <input type="text" name="departamento" value="<?= htmlspecialchars($usuario['departamento'] ?? '') ?>">
            <button class="btn btn-primary" type="submit">Guardar cambios</button>
        </form>
    </div>

    <div class="card">
        <h2>Cambiar contraseña</h2>
        <form method="POST" action="<?= BASE_URL ?>/perfil/password">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
            <label>Contraseña actual</label>
            <input type="password" name="actual" required>
            <label>Nueva contraseña</label>
            <input type="password" name="nueva" required minlength="8">
            <label>Repite nueva contraseña</label>
            <input type="password" name="nueva2" required minlength="8">
            <button class="btn btn-primary" type="submit">Actualizar contraseña</button>
        </form>
    </div>
</div>
