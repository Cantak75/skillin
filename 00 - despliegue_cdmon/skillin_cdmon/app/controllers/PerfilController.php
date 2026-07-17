<?php
/**
 * PerfilController
 * Editar perfil / avatar y configuración de notificaciones (Nivel 2 del mapa de sitio).
 */
class PerfilController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();
        $user = Auth::user();
        $usuario = (new Usuario())->findConEmpresa($user['id_usuario']);

        $this->view('perfil/index', [
            'title'   => 'Mi perfil',
            'usuario' => $usuario,
            'csrf'    => $this->csrfToken(),
        ]);
    }

    public function actualizar(): void
    {
        $this->requireAuth();
        if (!$this->isPost() || !$this->checkCsrf()) {
            $this->redirect('perfil');
        }

        $user = Auth::user();
        $usuarioModel = new Usuario();

        $usuarioModel->update($user['id_usuario'], [
            'nombre'       => trim((string)$this->input('nombre')),
            'apellidos'    => trim((string)$this->input('apellidos')),
            'email'        => trim((string)$this->input('email')),
            'departamento' => trim((string)$this->input('departamento')),
        ]);

        // refresca datos en sesión
        $actualizado = $usuarioModel->find($user['id_usuario']);
        Auth::login($actualizado);

        $this->redirect('perfil?ok=1');
    }

    /** Sube/reemplaza la foto de perfil del usuario logueado */
    public function actualizarFoto(): void
    {
        $this->requireAuth();
        if (!$this->isPost() || !$this->checkCsrf()) {
            $this->redirect('perfil');
        }

        $user = Auth::user();

        if (empty($_FILES['foto']) || $_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
            $this->redirect('perfil?error=foto_invalida');
            return;
        }

        $archivo = $_FILES['foto'];
        $maxBytes = 2 * 1024 * 1024;
        if ($archivo['size'] > $maxBytes) {
            $this->redirect('perfil?error=foto_tamano');
            return;
        }

        // No confiar en la extensión/MIME que envía el navegador: se detecta
        // el tipo real leyendo el contenido del fichero.
        $info = @getimagesize($archivo['tmp_name']);
        $extensiones = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        if (!$info || !isset($extensiones[$info['mime']])) {
            $this->redirect('perfil?error=foto_formato');
            return;
        }

        $usuarioModel = new Usuario();
        $anterior = $usuarioModel->find($user['id_usuario']);

        $dir = __DIR__ . '/../../uploads/avatars';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $nombreArchivo = 'u' . $user['id_usuario'] . '.' . $extensiones[$info['mime']];
        if (!move_uploaded_file($archivo['tmp_name'], $dir . '/' . $nombreArchivo)) {
            $this->redirect('perfil?error=foto_invalida');
            return;
        }

        // Limpia el fichero anterior si cambió de extensión (jpg -> png, etc.)
        if (!empty($anterior['foto']) && $anterior['foto'] !== $nombreArchivo) {
            @unlink($dir . '/' . $anterior['foto']);
        }

        $usuarioModel->updateFoto($user['id_usuario'], $nombreArchivo);

        // refresca datos en sesión
        $actualizado = $usuarioModel->find($user['id_usuario']);
        Auth::login($actualizado);

        $this->redirect('perfil?ok=1');
    }

    public function cambiarPassword(): void
    {
        $this->requireAuth();
        if (!$this->isPost() || !$this->checkCsrf()) {
            $this->redirect('perfil');
        }

        $user = Auth::user();
        $actual = (string)$this->input('actual');
        $nueva = (string)$this->input('nueva');
        $nueva2 = (string)$this->input('nueva2');

        $usuarioModel = new Usuario();
        $usuario = $usuarioModel->find($user['id_usuario']);

        if (!password_verify($actual, $usuario['contrasena'])) {
            $this->redirect('perfil?error=password_actual');
            return;
        }
        if (strlen($nueva) < 8 || $nueva !== $nueva2) {
            $this->redirect('perfil?error=password_invalida');
            return;
        }

        $usuarioModel->updatePassword($user['id_usuario'], $nueva);
        $this->redirect('perfil?ok=password');
    }
}
