<?php
/**
 * Controller
 * Clase base de la que heredan todos los controladores de la aplicación.
 * Proporciona utilidades comunes: renderizado de vistas, redirecciones,
 * respuestas JSON y comprobación de autenticación/roles.
 */
abstract class Controller
{
    protected PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Renderiza una vista dentro del layout principal.
     */
    protected function view(string $view, array $data = []): void
    {
        extract($data);
        $viewPath = __DIR__ . '/../views/' . $view . '.php';

        if (!file_exists($viewPath)) {
            http_response_code(500);
            die("Vista no encontrada: {$view}");
        }

        require __DIR__ . '/../views/layouts/header.php';
        require $viewPath;
        require __DIR__ . '/../views/layouts/footer.php';
    }

    /**
     * Renderiza una vista SIN el layout (páginas de login, errores, etc.)
     */
    protected function viewOnly(string $view, array $data = []): void
    {
        extract($data);
        $viewPath = __DIR__ . '/../views/' . $view . '.php';

        if (!file_exists($viewPath)) {
            http_response_code(500);
            die("Vista no encontrada: {$view}");
        }

        require $viewPath;
    }

    protected function redirect(string $url): void
    {
        header('Location: ' . BASE_URL . '/' . ltrim($url, '/'));
        exit;
    }

    protected function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit;
    }

    /** Exige que el usuario haya iniciado sesión */
    protected function requireAuth(): void
    {
        if (!Auth::check()) {
            $this->redirect('login');
        }
    }

    /** Exige un rol concreto ('rrhh' o 'trabajador'); administrador siempre pasa */
    protected function requireRole(string $rol): void
    {
        $this->requireAuth();
        $rolActual = Auth::user()['rol'];
        if ($rolActual !== $rol && $rolActual !== 'administrador') {
            http_response_code(403);
            $this->viewOnly('errors/403');
            exit;
        }
    }

    /**
     * Empresa sobre la que actúa el usuario actual: la suya propia, salvo que
     * sea administrador, en cuyo caso puede elegir cualquiera vía ?empresa=.
     */
    protected function empresaSeleccionada(array $user): int
    {
        if (Auth::isAdministrador()) {
            return (int)$this->input('empresa', $user['id_empresa']);
        }
        return (int)$user['id_empresa'];
    }

    protected function isPost(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    protected function input(string $key, $default = null)
    {
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }

    protected function csrfToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    protected function checkCsrf(): bool
    {
        $token = $this->input('csrf_token');
        return $token && isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
}
