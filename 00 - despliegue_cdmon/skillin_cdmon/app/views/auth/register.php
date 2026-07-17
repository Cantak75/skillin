<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear cuenta · Skillin</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>
<div class="auth-wrapper">
    <div class="auth-card" style="max-width: 480px;">
        <img src="<?= BASE_URL ?>/assets/img/logo-skillin-pref.png" alt="Skillin" class="auth-logo">
        <p class="subtitle">Crea tu cuenta de trabajador</p>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="<?= BASE_URL ?>/registro">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">

            <div class="grid grid-2" style="gap:12px;">
                <div>
                    <label for="nombre">Nombre</label>
                    <input type="text" id="nombre" name="nombre" required>
                </div>
                <div>
                    <label for="apellidos">Apellidos</label>
                    <input type="text" id="apellidos" name="apellidos" required>
                </div>
            </div>

            <label for="email">Correo electrónico</label>
            <input type="email" id="email" name="email" required>

            <label for="id_empresa">Empresa</label>
            <select id="id_empresa" name="id_empresa" required>
                <option value="">Selecciona tu empresa…</option>
                <?php foreach ($empresas as $empresa): ?>
                    <option value="<?= $empresa['id_empresa'] ?>"><?= htmlspecialchars($empresa['nombre']) ?></option>
                <?php endforeach; ?>
            </select>

            <label for="departamento">Departamento (opcional)</label>
            <input type="text" id="departamento" name="departamento" placeholder="Producción, Logística...">

            <div class="grid grid-2" style="gap:12px;">
                <div>
                    <label for="password">Contraseña</label>
                    <input type="password" id="password" name="password" required minlength="8">
                </div>
                <div>
                    <label for="password2">Repite contraseña</label>
                    <input type="password" id="password2" name="password2" required minlength="8">
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Crear cuenta</button>
        </form>

        <div class="auth-footer">
            ¿Ya tienes cuenta? <a href="<?= BASE_URL ?>/login">Inicia sesión</a>
        </div>
    </div>
</div>
</body>
</html>
