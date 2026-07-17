<?php
/**
 * Configuración general de Skillin
 */

// Cambia esto según dónde despliegues la app (subcarpeta o raíz)
define('BASE_URL', '/skillin/public');

define('APP_NAME', 'Skillin');

// Duración de la sesión en segundos (2 horas)
define('SESSION_LIFETIME', 7200);

date_default_timezone_set('Europe/Madrid');

session_set_cookie_params(SESSION_LIFETIME);
session_start();
