<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer contraseña · Skillin</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>
<div class="auth-wrapper">
    <div class="auth-card">
        <img src="<?= BASE_URL ?>/assets/img/logo-skillin-pref.png" alt="Skillin" class="auth-logo">
        <p class="subtitle">Elige una nueva contraseña</p>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($token): ?>
            <form method="POST" action="<?= BASE_URL ?>/recuperar/reset/<?= htmlspecialchars($token) ?>">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                <label for="nueva">Nueva contraseña</label>
                <input type="password" id="nueva" name="nueva" required minlength="8">
                <label for="nueva2">Repite la nueva contraseña</label>
                <input type="password" id="nueva2" name="nueva2" required minlength="8">
                <button type="submit" class="btn btn-primary btn-block">Restablecer contraseña</button>
            </form>
        <?php else: ?>
            <div class="auth-footer">
                <a href="<?= BASE_URL ?>/recuperar">Solicita un nuevo enlace</a>
            </div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>