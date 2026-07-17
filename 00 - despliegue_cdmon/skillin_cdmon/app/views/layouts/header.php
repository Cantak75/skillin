<?php
$currentUser = Auth::user();
$currentPath = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
function navActive(string $needle, string $currentPath): string {
    return str_contains($currentPath, $needle) ? 'active' : '';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($title) ? htmlspecialchars($title) . ' · ' : '' ?>Skillin</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>
<div class="app-shell">
    <aside class="sidebar">
        <div class="brand"><img src="<?= BASE_URL ?>/assets/img/logo-skillin-fondo-osc.png" alt="Skillin"></div>
        <nav>
            <a href="<?= BASE_URL ?>/dashboard" class="<?= navActive('dashboard', $currentPath) ?>">
                <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9.5 12 3l9 6.5V20a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1V9.5Z"/></svg>
                Dashboard
            </a>

            <?php if (Auth::isTrabajador()): ?>
                <a href="<?= BASE_URL ?>/juegos" class="<?= navActive('juegos', $currentPath) ?>">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="6" width="20" height="12" rx="6"/><line x1="6" y1="12" x2="10" y2="12"/><line x1="8" y1="10" x2="8" y2="14"/><circle cx="15" cy="13" r="1"/><circle cx="18" cy="11" r="1"/></svg>
                    Mis juegos
                </a>
                <a href="<?= BASE_URL ?>/progreso" class="<?= navActive('progreso', $currentPath) ?>">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 17 9 11 13 15 21 6"/><polyline points="14 6 21 6 21 13"/></svg>
                    Mi progreso
                </a>
            <?php endif; ?>

            <?php if (Auth::hasAnyRole('rrhh', 'administrador')): ?>
                <a href="<?= BASE_URL ?>/rrhh/usuarios" class="<?= navActive('rrhh/usuarios', $currentPath) ?>">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    Plantilla
                </a>
                <a href="<?= BASE_URL ?>/rrhh/juegos" class="<?= navActive('rrhh/juegos', $currentPath) ?>">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="6" width="20" height="12" rx="6"/><line x1="6" y1="12" x2="10" y2="12"/><line x1="8" y1="10" x2="8" y2="14"/><circle cx="15" cy="13" r="1"/><circle cx="18" cy="11" r="1"/></svg>
                    Catálogo de juegos
                </a>
                <a href="<?= BASE_URL ?>/rrhh/asignaciones" class="<?= navActive('rrhh/asignaciones', $currentPath) ?>">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="6" y="3" width="12" height="4" rx="1"/><path d="M6 5H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1"/><line x1="8" y1="12" x2="16" y2="12"/><line x1="8" y1="16" x2="16" y2="16"/></svg>
                    Asignaciones
                </a>
                <a href="<?= BASE_URL ?>/rrhh/informes" class="<?= navActive('rrhh/informes', $currentPath) ?>">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="4" y1="20" x2="4" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="20" y1="20" x2="20" y2="14"/></svg>
                    Informes
                </a>
            <?php endif; ?>

            <?php if (Auth::isAdministrador()): ?>
                <a href="<?= BASE_URL ?>/admin/empresas" class="<?= navActive('admin/empresas', $currentPath) ?>">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="2" width="10" height="20" rx="1"/><rect x="14" y="9" width="6" height="13" rx="1"/><line x1="7" y1="6" x2="7" y2="6"/><line x1="11" y1="6" x2="11" y2="6"/><line x1="7" y1="10" x2="7" y2="10"/><line x1="11" y1="10" x2="11" y2="10"/><line x1="7" y1="14" x2="7" y2="14"/><line x1="11" y1="14" x2="11" y2="14"/></svg>
                    Empresas
                </a>
            <?php endif; ?>

            <a href="<?= BASE_URL ?>/perfil" class="<?= navActive('perfil', $currentPath) ?>">
                <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                Mi perfil
            </a>
        </nav>
        <div class="user-box">
            <?php if (!empty($currentUser['foto'])): ?>
                <img src="<?= BASE_URL ?>/uploads/avatars/<?= htmlspecialchars($currentUser['foto']) ?>" alt="" class="avatar">
            <?php else: ?>
                <div class="avatar avatar-placeholder"><?= htmlspecialchars(mb_strtoupper(mb_substr($currentUser['nombre'], 0, 1))) ?></div>
            <?php endif; ?>
            <strong><?= htmlspecialchars($currentUser['nombre'] . ' ' . $currentUser['apellidos']) ?></strong>
            <?= htmlspecialchars($currentUser['email']) ?>
            <?php $rolEtiquetas = ['rrhh' => 'RRHH', 'administrador' => 'Administrador', 'trabajador' => 'Trabajador']; ?>
            <div><span class="rol-badge"><?= $rolEtiquetas[$currentUser['rol']] ?? 'Trabajador' ?></span></div>
            <a href="<?= BASE_URL ?>/logout" class="logout-link">Cerrar sesión →</a>
        </div>
    </aside>
    <main class="main">
