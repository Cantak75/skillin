<?php
/**
 * Configuración general de Skillin
 */

// El subdominio "skillin" cuelga directamente de web/skillin/, así que la
// app vive en la raíz de esa URL (https://skillin.tudominio.com/).
define('BASE_URL', '');

define('APP_NAME', 'Skillin');

// Duración de la sesión en segundos (2 horas)
define('SESSION_LIFETIME', 7200);

date_default_timezone_set('Europe/Madrid');

session_set_cookie_params(SESSION_LIFETIME);
session_start();
