<?php
/**
 * Router
 * Enrutador ligero: transforma una URL del tipo
 *   /skillin/public/juegos/jugar/3
 * en la llamada  (new JuegoController())->jugar(3)
 *
 * También soporta el formato clásico ?c=juego&a=jugar&id=3
 */
class Router
{
    private array $routes = [];

    public function get(string $path, string $handler): void
    {
        $this->add('GET', $path, $handler);
    }

    public function post(string $path, string $handler): void
    {
        $this->add('POST', $path, $handler);
    }

    public function any(string $path, string $handler): void
    {
        $this->add('GET', $path, $handler);
        $this->add('POST', $path, $handler);
    }

    private function add(string $method, string $path, string $handler): void
    {
        $pattern = preg_replace('#\{[a-zA-Z_]+\}#', '([^/]+)', trim($path, '/'));
        $this->routes[] = [
            'method'  => $method,
            'pattern' => '#^' . $pattern . '$#',
            'handler' => $handler,
        ];
    }

    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // Quita el prefijo de la carpeta pública (BASE_URL) de la ruta
        $base = trim(str_replace('/index.php', '', BASE_URL), '/');
        $path = trim($uri, '/');
        if ($base !== '' && str_starts_with($path, $base)) {
            $path = trim(substr($path, strlen($base)), '/');
        }

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }
            if (preg_match($route['pattern'], $path, $matches)) {
                array_shift($matches);
                [$controllerName, $action] = explode('@', $route['handler']);

                $controllerFile = __DIR__ . '/../controllers/' . $controllerName . '.php';
                if (!file_exists($controllerFile)) {
                    $this->notFound();
                    return;
                }
                require_once $controllerFile;

                $controller = new $controllerName();
                if (!method_exists($controller, $action)) {
                    $this->notFound();
                    return;
                }
                call_user_func_array([$controller, $action], $matches);
                return;
            }
        }

        $this->notFound();
    }

    private function notFound(): void
    {
        http_response_code(404);
        require __DIR__ . '/../views/errors/404.php';
    }
}
