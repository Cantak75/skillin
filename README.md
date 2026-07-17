# Skillin — Proyecto Intermodular DAW
# 

Plataforma web gamificada para la evaluación y entrenamiento de competencias
profesionales en empresas mediante *serious games*. Desarrollado en PHP
(MVC, sin framework) con MySQL/MariaDB.

Autor: Constantino Alexopoulos Real · IES Albarregas · DAW Dual

> Este README es una guía rápida de instalación y visión general. Para el
> detalle completo de arquitectura, modelo de datos y cada módulo, consulta
> **`Manual técnico.pdf`**; para una guía paso a paso de uso de la
> aplicación (con capturas), consulta **`Manual de usuario.pdf`**.

---

## 1. Estructura del proyecto

```
skillin/
├── app/
│   ├── controllers/                    Controladores (uno por recurso)
│   │   ├── AuthController.php          Login, registro, logout, recuperación de contraseña
│   │   ├── DashboardController.php     Panel según rol
│   │   ├── PerfilController.php        Datos propios, avatar, contraseña
│   │   ├── UsuarioController.php       CRUD de usuarios (RRHH / Administrador)
│   │   ├── JuegoController.php         Catálogo (gestión) + ejecución de juegos
│   │   ├── AsignacionController.php    Asignación de juegos a trabajadores
│   │   ├── InformeController.php       Informes y analítica
│   │   └── EmpresaController.php       Alta de empresas (solo Administrador)
│   ├── core/                           Núcleo: Router, Controller base, Auth, Mailer
│   ├── models/                         Modelos (Usuario, Empresa, Juego, AsignacionJuego,
│   │                                   Resultado, Informe, PasswordReset)
│   └── views/                          Vistas por área (auth, trabajador, rrhh, admin, perfil, juegos, layouts)
├── config/
│   ├── config.php                      Configuración general (BASE_URL, sesiones...)
│   ├── database.php                    Conexión PDO a MySQL/MariaDB
│   └── mail.php                        Credenciales SMTP (recuperación de contraseña)
├── database/
│   ├── skillindb.sql                   Script de creación de la BD + datos de prueba
│   └── seed_passwords.php              Genera los hashes bcrypt reales de las cuentas demo
├── Manual técnico.pdf                  Manual técnico completo (arquitectura, BD, rutas, seguridad...)
├── Manual de usuario.pdf               Manual de usuario completo (con capturas, por rol)
└── public/
    ├── index.php                       Front Controller (punto de entrada único)
    ├── .htaccess                       Reescritura de URLs (Apache + mod_rewrite)
    ├── assets/
    │   ├── css/style.css               Estilos (paleta corporativa Skillin)
    │   └── img/                        Logos
    └── uploads/
        ├── avatars/                    Fotos de perfil subidas por los usuarios
        └── juegos/                     Imágenes de cabecera de cada juego
```

Patrón **MVC**: las peticiones entran por `public/index.php`, el `Router`
las despacha a un método de un `Controller`, éste usa los `Models` (PDO +
SQL preparado) para acceder a los datos y renderiza una `View`.

## 2. Requisitos

- PHP 8.1 o superior con extensión `pdo_mysql`
- MySQL 5.7+ / MariaDB 10.4+
- Apache con `mod_rewrite` (o Nginx equivalente) — o el servidor embebido de PHP para pruebas

## 3. Instalación

### 3.1 Base de datos

```bash
mysql -u root -p < database/skillindb.sql
```

> El script incluye `SET NAMES utf8mb4;`, por lo que los acentos se
> importan correctamente sin importar el locale del cliente `mysql`. Crea
> (`DROP`/`CREATE`) una base de datos llamada `skillindb`, igual que el
> `DBNAME` configurado por defecto en `config/database.php` — si cambias
> uno de los dos nombres, cambia también el otro para que sigan
> coincidiendo.

El script ya deja las contraseñas de estas cuentas con un hash bcrypt real
de `1234`, así que se puede iniciar sesión nada más importarlo. Si prefieres
regenerar los hashes o editar la lista de cuentas, `database/seed_passwords.php`
sigue disponible:

```bash
php database/seed_passwords.php
```

Cuentas de prueba resultantes:

| Rol            | Nombre                       | Email                                   | Empresa                    | Contraseña |
|-----------------|-------------------------------|-------------------------------------------|-----------------------------|------------|
| Administrador   | Constantino Alexopoulos Real  | tak@tagtak.com                            | Skillin                     | 1234       |
| RRHH            | Laura Martín Sánchez          | laura.martin@albaconstrucciones.com       | Alba Construcciones S.L.    | 1234       |
| RRHH            | Carlos Ruiz Pérez             | carlos.ruiz@innotech.com                  | Innotech S.L.               | 1234       |
| Trabajador      | Ana García López              | ana.garcia@innotech.com                   | Innotech S.L.               | 1234       |
| Trabajador      | Juan Chacón Paz               | juan.chacon@albaconstrucciones.com        | Alba Construcciones S.L.    | 1234       |
| Trabajador      | Eva Flores Rojo               | eva.flores@albaconstrucciones.com         | Alba Construcciones S.L.    | 1234       |
| Trabajador      | Elsa Cantero Fernández        | elsa.cantero@albaconstrucciones.com       | Alba Construcciones S.L.    | 1234       |

Tras el primer acceso, cada usuario puede cambiar su contraseña desde "Mi perfil".

### 3.2 Configuración de conexión

Edita `config/database.php` con tus credenciales (host, base de datos, usuario, contraseña):

```php
private const HOST    = 'localhost';
private const DBNAME  = 'skillindb';
private const USER    = 'root';
private const PASS    = '';
```

### 3.3 BASE_URL

En `config/config.php`, ajusta `BASE_URL` según dónde despliegues la carpeta
`public/` (raíz del dominio o subcarpeta):

```php
define('BASE_URL', '/skillin/public'); // o '' si sirves public/ como raíz
```

### 3.4 Envío de email (recuperación de contraseña)

Edita `config/mail.php` con los datos SMTP de tu proveedor (host, usuario,
contraseña) para que funcione "Recuperar contraseña". Mientras el `host`
mantenga el valor de ejemplo, la aplicación sigue funcionando con
normalidad, pero el correo no llega (queda constancia en el log de
Apache/PHP). Ejemplos de configuración habituales en los comentarios del
propio fichero.

### 3.5 Levantar el proyecto

**Opción A — servidor embebido de PHP (rápido, para pruebas):**

```bash
php -S localhost:8080 -t public
```

Y pon `define('BASE_URL', '');` en `config/config.php`.

**Opción B — Apache/XAMPP/WAMP:**

Copia la carpeta `skillin/` dentro de `htdocs/`, apunta el `DocumentRoot`
(o un `Alias`) a `skillin/public`, y asegúrate de que `AllowOverride All`
está activo para que `.htaccess` funcione.

## 4. Funcionalidades implementadas (según RF de la propuesta)

- **RF1/RNF1** — Login con email + contraseña, hash bcrypt, protección CSRF, sesiones seguras y recuperación de contraseña por email (token de un solo uso).
- **RF2** — Interfaz y rutas diferenciadas por rol (`trabajador` / `rrhh` / `administrador`), verificado en cada controlador (`requireRole`).
- **RF3** — El trabajador ve su catálogo de juegos asignados y los ejecuta (`/juegos`, `/juegos/jugar/{id}`).
- **RF4** — Cada partida guarda puntuación, tiempo y fecha (`resultado`), visible en "Mi progreso".
- **RF5** — RRHH/Administrador asigna un juego a uno o varios trabajadores a la vez (`/rrhh/asignaciones`), con bloqueo automático de reasignaciones mientras exista una instancia pendiente/en curso/caducada del mismo juego para ese trabajador.
- **RF6** — CRUD de usuarios: crear, editar, activar/desactivar y eliminar (`/rrhh/usuarios`); avatar de perfil subible por cada usuario.
- **RF7** — Informes tabulares y gráficos (Chart.js) por juego, con exportación CSV (`/rrhh/informes`).
- **RNF2/RNF4** — Interfaz responsive (CSS Grid + media queries), paleta Skillin (azul marino + turquesa), iconografía SVG monocolor en el menú.
- **RNF5** — Código organizado en MVC, con modelos y controladores documentados.

### Rol Administrador (multiempresa)

Por encima de RRHH existe un rol **Administrador**: tiene acceso a las
mismas pantallas de gestión que RRHH, pero sin quedar limitado a una sola
empresa — puede elegir con qué empresa trabajar desde un selector, dar de
alta usuarios en cualquier empresa (o crear una empresa nueva sobre la
marcha desde el propio alta de usuario), y gestiona la sección exclusiva
**Empresas** (`/admin/empresas`). El detalle completo de permisos por rol
está en `MANUAL_TECNICO.docx`.

### Los 3 *serious games* implementados

1. **Quiz de Seguridad Laboral** — preguntas tipo test sobre normativa y buenas prácticas.
2. **Memoria de Procesos** — juego de memoria por parejas (retención de pasos de un proceso).
3. **Tiempo de Reacción** — mide la rapidez de respuesta ante un estímulo visual.

Los tres juegos se ejecutan 100% en el cliente (JavaScript) y envían el
resultado final al servidor vía `fetch()` a `POST /juegos/resultado`. Cada
juego del catálogo puede llevar una imagen de cabecera propia, gestionable
desde `/rrhh/juegos`.

## 5. Seguridad

- Contraseñas con `password_hash()` (bcrypt); nunca se guardan ni comparan en claro.
- Sentencias preparadas PDO en todos los modelos (sin concatenación SQL), con prepares reales (`PDO::ATTR_EMULATE_PREPARES = false`).
- Token CSRF en todos los formularios POST.
- Verificación de rol en cada acción sensible (`requireAuth` / `requireRole`) y de pertenencia a empresa (RRHH no puede tocar datos de otra empresa aunque manipule la URL).
- Cuentas desactivables sin borrado físico de historial (`usuario.activo`).
- Recuperación de contraseña con token de un solo uso, hash SHA-256 en BD, expiración a 1 hora y mensaje de respuesta uniforme (no revela qué correos están registrados).
- Subida de ficheros (avatares e imágenes de juego) validada por contenido real (`getimagesize()`, no por extensión ni MIME del navegador), con límite de tamaño y nombre de fichero generado por el servidor.

## 6. Notas de diseño (coherencia con el modelo E/R entregado)

El esquema `skillindb.sql` refleja las entidades del modelo Entidad-Relación
de la Tarea 03: `EMPRESA`, `USUARIO`, `JUEGO`, `RESULTADO`,
`ASIGNACION_JUEGO` (entidad intermedia N:M) e `INFORME`, normalizado hasta
3FN, tal como se justificó en dicho documento. Posteriormente se ha añadido
la tabla `PASSWORD_RESET` (tokens de recuperación de contraseña) como
extensión funcional no contemplada en el modelo E/R original de la Tarea
03.