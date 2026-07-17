<?php
/**
 * Auth
 * Gestión de autenticación basada en sesiones PHP.
 * RNF1: contraseñas con hashing seguro (bcrypt) y protección de rutas.
 */
class Auth
{
    public static function attempt(string $email, string $password): bool
    {
        $usuarioModel = new Usuario();
        $usuario = $usuarioModel->findByEmail($email);

        if (!$usuario) {
            return false;
        }

        if ((int)$usuario['activo'] === 0) {
            return false; // cuenta desactivada por RRHH
        }

        if (!password_verify($password, $usuario['contrasena'])) {
            return false;
        }

        self::login($usuario);
        return true;
    }

    public static function login(array $usuario): void
    {
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id_usuario'   => $usuario['id_usuario'],
            'nombre'       => $usuario['nombre'],
            'apellidos'    => $usuario['apellidos'],
            'email'        => $usuario['email'],
            'rol'          => $usuario['rol'],
            'departamento' => $usuario['departamento'],
            'foto'         => $usuario['foto'] ?? null,
            'id_empresa'   => $usuario['id_empresa'],
        ];
    }

    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
    }

    public static function check(): bool
    {
        return isset($_SESSION['user']);
    }

    public static function user(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    public static function id(): ?int
    {
        return $_SESSION['user']['id_usuario'] ?? null;
    }

    public static function isRrhh(): bool
    {
        return self::check() && $_SESSION['user']['rol'] === 'rrhh';
    }

    public static function isTrabajador(): bool
    {
        return self::check() && $_SESSION['user']['rol'] === 'trabajador';
    }

    public static function isAdministrador(): bool
    {
        return self::check() && $_SESSION['user']['rol'] === 'administrador';
    }

    /** true si el usuario logueado tiene alguno de los roles indicados */
    public static function hasAnyRole(string ...$roles): bool
    {
        return self::check() && in_array($_SESSION['user']['rol'], $roles, true);
    }
}
