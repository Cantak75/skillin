<?php
/**
 * Skillin - Plataforma web gamificada para la evaluación y entrenamiento
 * de competencias profesionales en empresas.
 *
 * Proyecto Intermodular DAW · IES Albarregas · DAW Dual
 * Autor: Constantino Alexopoulos Real
 *
 * index.php - Front Controller
 * Todas las peticiones pasan por aquí (ver .htaccess).
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

require_once __DIR__ . '/app/core/Auth.php';
require_once __DIR__ . '/app/core/Controller.php';
require_once __DIR__ . '/app/core/Router.php';
require_once __DIR__ . '/app/core/Mailer.php';

// Modelos
foreach (glob(__DIR__ . '/app/models/*.php') as $modelo) {
    require_once $modelo;
}

$router = new Router();

// ------------------------------------------------------------------
// Rutas públicas
// ------------------------------------------------------------------
$router->get('/', 'AuthController@loginForm');
$router->get('login', 'AuthController@loginForm');
$router->post('login', 'AuthController@login');
$router->get('registro', 'AuthController@registerForm');
$router->post('registro', 'AuthController@register');
$router->get('logout', 'AuthController@logout');

$router->get('recuperar', 'AuthController@recuperarForm');
$router->post('recuperar', 'AuthController@recuperar');
$router->get('recuperar/reset/{token}', 'AuthController@resetForm');
$router->post('recuperar/reset/{token}', 'AuthController@reset');

// ------------------------------------------------------------------
// Dashboard (según rol)
// ------------------------------------------------------------------
$router->get('dashboard', 'DashboardController@index');

// ------------------------------------------------------------------
// Área Trabajador
// ------------------------------------------------------------------
$router->get('juegos', 'JuegoController@catalogo');
$router->get('progreso', 'JuegoController@progreso');
$router->get('juegos/jugar/{id}', 'JuegoController@jugar');
$router->post('juegos/resultado', 'JuegoController@guardarResultado');

// ------------------------------------------------------------------
// Perfil (común)
// ------------------------------------------------------------------
$router->get('perfil', 'PerfilController@index');
$router->post('perfil/actualizar', 'PerfilController@actualizar');
$router->post('perfil/foto', 'PerfilController@actualizarFoto');
$router->post('perfil/password', 'PerfilController@cambiarPassword');

// ------------------------------------------------------------------
// Área RRHH / Administración
// ------------------------------------------------------------------
$router->get('rrhh/usuarios', 'UsuarioController@index');
$router->post('rrhh/usuarios/crear', 'UsuarioController@crear');
$router->post('rrhh/usuarios/{id}/actualizar', 'UsuarioController@actualizar');
$router->post('rrhh/usuarios/{id}/toggle', 'UsuarioController@toggleActivo');
$router->post('rrhh/usuarios/{id}/eliminar', 'UsuarioController@eliminar');

$router->get('rrhh/juegos', 'JuegoController@gestion');
$router->post('rrhh/juegos/crear', 'JuegoController@crear');
$router->post('rrhh/juegos/{id}/actualizar', 'JuegoController@actualizar');
$router->post('rrhh/juegos/{id}/imagen', 'JuegoController@actualizarImagen');
$router->post('rrhh/juegos/{id}/eliminar', 'JuegoController@eliminar');

$router->get('rrhh/asignaciones', 'AsignacionController@index');
$router->post('rrhh/asignaciones/asignar', 'AsignacionController@asignar');
$router->post('rrhh/asignaciones/{id}/eliminar', 'AsignacionController@eliminar');

$router->get('rrhh/informes', 'InformeController@index');
$router->post('rrhh/informes/generar', 'InformeController@generar');
$router->get('rrhh/informes/exportar/{id}', 'InformeController@exportarCsv');

// ------------------------------------------------------------------
// Área Administrador
// ------------------------------------------------------------------
$router->get('admin/empresas', 'EmpresaController@index');
$router->post('admin/empresas/crear', 'EmpresaController@crear');

$router->dispatch();
