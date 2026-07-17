<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar contraseña · Skillin</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>
<div class="auth-wrapper">
    <div class="auth-card">
        <img src="<?= BASE_URL ?>/assets/img/logo-skillin-pref.png" alt="Skillin" class="auth-logo">
        <p class="subtitle">Recupera el acceso a tu cuenta</p>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if (!empty($mensaje)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($mensaje) ?></div>
        <?php endif; ?>

        <?php if (empty($mensaje)): ?>
            <form method="POST" action="<?= BASE_URL ?>/recuperar">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                <label for="email">Correo electrónico</label>
                <input type="email" id="email" name="email" required autofocus placeholder="tu@empresa.com">
                <button type="submit" class="btn btn-primary btn-block">Enviar enlace de recuperación</button>
            </form>
        <?php endif; ?>

        <div class="auth-footer">
            <a href="<?= BASE_URL ?>/login">← Volver a iniciar sesión</a>
        </div>
    </div>
</div>
</body>
</html>