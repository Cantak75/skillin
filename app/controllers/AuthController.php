<?php
/**
 * AuthController
 * RF1: autenticación mediante correo y contraseña.
 */
class AuthController extends Controller
{
    public function loginForm(): void
    {
        if (Auth::check()) {
            $this->redirect('dashboard');
        }
        $this->viewOnly('auth/login', ['csrf' => $this->csrfToken(), 'error' => null]);
    }

    public function login(): void
    {
        if (!$this->isPost()) {
            $this->redirect('login');
        }

        if (!$this->checkCsrf()) {
            $this->viewOnly('auth/login', ['csrf' => $this->csrfToken(), 'error' => 'Sesión caducada, inténtalo de nuevo.']);
            return;
        }

        $email = trim((string)$this->input('email'));
        $password = (string)$this->input('password');

        if ($email === '' || $password === '') {
            $this->viewOnly('auth/login', ['csrf' => $this->csrfToken(), 'error' => 'Rellena todos los campos.']);
            return;
        }

        if (Auth::attempt($email, $password)) {
            $this->redirect('dashboard');
            return;
        }

        $this->viewOnly('auth/login', [
            'csrf'  => $this->csrfToken(),
            'error' => 'Credenciales incorrectas o cuenta desactivada.',
        ]);
    }

    public function registerForm(): void
    {
        if (Auth::check()) {
            $this->redirect('dashboard');
        }
        $empresas = (new Empresa())->all();
        $this->viewOnly('auth/register', ['csrf' => $this->csrfToken(), 'error' => null, 'empresas' => $empresas]);
    }

    /**
     * Registro público. Por defecto los usuarios que se registran son
     * "trabajador" de la empresa seleccionada; los administradores/RRHH
     * se dan de alta desde el panel de gestión (RF6) por un RRHH existente.
     */
    public function register(): void
    {
        $empresas = (new Empresa())->all();

        if (!$this->isPost() || !$this->checkCsrf()) {
            $this->viewOnly('auth/register', ['csrf' => $this->csrfToken(), 'error' => 'Sesión caducada.', 'empresas' => $empresas]);
            return;
        }

        $nombre      = trim((string)$this->input('nombre'));
        $apellidos   = trim((string)$this->input('apellidos'));
        $email       = trim((string)$this->input('email'));
        $password    = (string)$this->input('password');
        $password2   = (string)$this->input('password2');
        $idEmpresa   = (int)$this->input('id_empresa');
        $departamento = trim((string)$this->input('departamento'));

        $errores = [];
        if ($nombre === '' || $apellidos === '' || $email === '') {
            $errores[] = 'Todos los campos obligatorios deben rellenarse.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errores[] = 'El correo electrónico no es válido.';
        }
        if (strlen($password) < 8) {
            $errores[] = 'La contraseña debe tener al menos 8 caracteres.';
        }
        if ($password !== $password2) {
            $errores[] = 'Las contraseñas no coinciden.';
        }
        if ($idEmpresa <= 0) {
            $errores[] = 'Selecciona una empresa.';
        }

        $usuarioModel = new Usuario();
        if (empty($errores) && $usuarioModel->emailExists($email)) {
            $errores[] = 'Ya existe una cuenta con ese correo electrónico.';
        }

        if (!empty($errores)) {
            $this->viewOnly('auth/register', [
                'csrf' => $this->csrfToken(), 'error' => implode(' ', $errores), 'empresas' => $empresas,
            ]);
            return;
        }

        $idUsuario = $usuarioModel->create([
            'nombre'       => $nombre,
            'apellidos'    => $apellidos,
            'email'        => $email,
            'contrasena'   => $password,
            'rol'          => 'trabajador',
            'departamento' => $departamento ?: null,
            'id_empresa'   => $idEmpresa,
        ]);

        $usuario = $usuarioModel->find($idUsuario);
        Auth::login($usuario);
        $this->redirect('dashboard');
    }

    public function logout(): void
    {
        Auth::logout();
        $this->redirect('login');
    }

    public function recuperarForm(): void
    {
        if (Auth::check()) {
            $this->redirect('dashboard');
        }
        $this->viewOnly('auth/recuperar', ['csrf' => $this->csrfToken(), 'mensaje' => null, 'error' => null]);
    }

    /**
     * Genera un token de un solo uso y envía el enlace de recuperación por
     * email. Siempre muestra el mismo mensaje exista o no la cuenta, para no
     * revelar qué correos están registrados.
     */
    public function recuperar(): void
    {
        if (!$this->isPost() || !$this->checkCsrf()) {
            $this->redirect('recuperar');
        }

        $email = trim((string)$this->input('email'));
        $mensaje = 'Si existe una cuenta con ese correo, te hemos enviado un enlace para restablecer la contraseña. Revisa también la carpeta de spam.';

        $usuarioModel = new Usuario();
        $usuario = $usuarioModel->findByEmail($email);

        if ($usuario && (int)$usuario['activo'] === 1) {
            $token = bin2hex(random_bytes(32));
            $tokenHash = hash('sha256', $token);
            $expiraEn = date('Y-m-d H:i:s', time() + 3600);

            $resetModel = new PasswordReset();
            $resetModel->invalidarPendientes((int)$usuario['id_usuario']);
            $resetModel->crear((int)$usuario['id_usuario'], $tokenHash, $expiraEn);

            $esquema = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
            $enlace = rtrim($esquema . $_SERVER['HTTP_HOST'] . BASE_URL, '/') . '/recuperar/reset/' . $token;

            $cuerpo = '<p>Hola ' . htmlspecialchars($usuario['nombre']) . ',</p>'
                . '<p>Hemos recibido una solicitud para restablecer tu contraseña de Skillin. Este enlace caduca en 1 hora:</p>'
                . '<p><a href="' . htmlspecialchars($enlace) . '">' . htmlspecialchars($enlace) . '</a></p>'
                . '<p>Si no has sido tú, puedes ignorar este correo: tu contraseña seguirá siendo la misma.</p>';

            (new Mailer())->enviar($usuario['email'], $usuario['nombre'], 'Recupera tu contraseña - Skillin', $cuerpo);
        }

        $this->viewOnly('auth/recuperar', ['csrf' => $this->csrfToken(), 'mensaje' => $mensaje, 'error' => null]);
    }

    public function resetForm(string $token): void
    {
        $resetModel = new PasswordReset();
        $registro = $resetModel->buscarValidoPorHash(hash('sha256', $token));

        if (!$registro) {
            $this->viewOnly('auth/reset', [
                'csrf' => $this->csrfToken(), 'token' => null,
                'error' => 'El enlace no es válido o ha caducado. Solicita uno nuevo.',
            ]);
            return;
        }

        $this->viewOnly('auth/reset', ['csrf' => $this->csrfToken(), 'token' => $token, 'error' => null]);
    }

    public function reset(string $token): void
    {
        if (!$this->isPost() || !$this->checkCsrf()) {
            $this->redirect('recuperar');
        }

        $resetModel = new PasswordReset();
        $registro = $resetModel->buscarValidoPorHash(hash('sha256', $token));

        if (!$registro) {
            $this->viewOnly('auth/reset', [
                'csrf' => $this->csrfToken(), 'token' => null,
                'error' => 'El enlace no es válido o ha caducado. Solicita uno nuevo.',
            ]);
            return;
        }

        $nueva = (string)$this->input('nueva');
        $nueva2 = (string)$this->input('nueva2');

        if (strlen($nueva) < 8 || $nueva !== $nueva2) {
            $this->viewOnly('auth/reset', [
                'csrf' => $this->csrfToken(), 'token' => $token,
                'error' => 'La contraseña debe tener al menos 8 caracteres y coincidir en ambos campos.',
            ]);
            return;
        }

        (new Usuario())->updatePassword((int)$registro['id_usuario'], $nueva);
        $resetModel->marcarUsado((int)$registro['id_reset']);

        $this->redirect('login?ok=password_restablecida');
    }
}
