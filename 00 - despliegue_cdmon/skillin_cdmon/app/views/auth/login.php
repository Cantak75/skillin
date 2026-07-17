<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión · Skillin</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>
<div class="auth-wrapper">
    <div class="auth-card">
        <img src="<?= BASE_URL ?>/assets/img/logo-skillin-pref.png" alt="Skillin" class="auth-logo">
        <p class="subtitle">Entrena competencias. Mide el progreso.</p>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if (isset($_GET['ok']) && $_GET['ok'] === 'password_restablecida'): ?>
            <div class="alert alert-success">Contraseña actualizada. Ya puedes iniciar sesión.</div>
        <?php endif; ?>

        <form method="POST" action="<?= BASE_URL ?>/login">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">

            <label for="email">Correo electrónico</label>
            <input type="email" id="email" name="email" required autofocus placeholder="tu@empresa.com">

            <label for="password">Contraseña</label>
            <input type="password" id="password" name="password" required placeholder="••••••••">

            <button type="submit" class="btn btn-primary btn-block">Iniciar sesión</button>
        </form>

        <div class="auth-footer">
            ¿No tienes cuenta? <a href="<?= BASE_URL ?>/registro">Regístrate como trabajador</a><br>
            ¿Ya estás registrado pero olvidaste tu contraseña? <a href="<?= BASE_URL ?>/recuperar">Recupérala por email</a>
        </div>
    </div>
</div>
</body>
</html>
