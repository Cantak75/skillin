<?php
/**
 * UsuarioController
 * RF6: CRUD de usuarios (crear, consultar, actualizar y desactivar) - solo RRHH.
 */
class UsuarioController extends Controller
{
    public function index(): void
    {
        $this->requireRole('rrhh');
        $user = Auth::user();
        $idEmpresa = $this->empresaSeleccionada($user);

        $busqueda = trim((string)$this->input('q', ''));
        $usuarioModel = new Usuario();
        $trabajadores = Auth::isAdministrador()
            ? $usuarioModel->listarPorEmpresa($idEmpresa, $busqueda)
            : $usuarioModel->listarTrabajadoresPorEmpresa($idEmpresa, $busqueda);

        $this->view('rrhh/usuarios', [
            'title'         => 'Gestión de plantilla',
            'trabajadores'  => $trabajadores,
            'busqueda'      => $busqueda,
            'csrf'          => $this->csrfToken(),
            'empresas'      => Auth::isAdministrador() ? (new Empresa())->all() : [],
            'empresaActual' => $idEmpresa,
        ]);
    }

    public function crear(): void
    {
        $this->requireRole('rrhh');
        if (!$this->isPost() || !$this->checkCsrf()) {
            $this->redirect('rrhh/usuarios');
        }

        $user = Auth::user();
        $usuarioModel = new Usuario();

        $email = trim((string)$this->input('email'));
        if ($usuarioModel->emailExists($email)) {
            $this->redirect('rrhh/usuarios?error=email_duplicado');
            return;
        }

        $idEmpresa = $this->empresaSeleccionada($user);

        // Solo el administrador puede elegir empresa o dar de alta una nueva al crear el usuario
        if (Auth::isAdministrador()) {
            $nuevaEmpresaNombre = trim((string)$this->input('nueva_empresa_nombre'));
            $empresaModel = new Empresa();

            if ($nuevaEmpresaNombre !== '') {
                if ($empresaModel->nombreExists($nuevaEmpresaNombre)) {
                    $this->redirect('rrhh/usuarios?error=empresa_duplicada');
                    return;
                }
                $sector = trim((string)$this->input('nueva_empresa_sector'));
                $idEmpresa = $empresaModel->create($nuevaEmpresaNombre, $sector ?: null);
            } else {
                $idEmpresaPost = (int)$this->input('id_empresa', 0);
                if ($idEmpresaPost > 0 && $empresaModel->find($idEmpresaPost)) {
                    $idEmpresa = $idEmpresaPost;
                }
            }
        }

        $rolesPermitidos = Auth::isAdministrador()
            ? ['trabajador', 'rrhh', 'administrador']
            : ['trabajador', 'rrhh'];

        $usuarioModel->create([
            'nombre'       => trim((string)$this->input('nombre')),
            'apellidos'    => trim((string)$this->input('apellidos')),
            'email'        => $email,
            'contrasena'   => (string)$this->input('password', 'Skillin2026!'),
            'rol'          => in_array($this->input('rol'), $rolesPermitidos, true) ? $this->input('rol') : 'trabajador',
            'departamento' => trim((string)$this->input('departamento')),
            'id_empresa'   => $idEmpresa,
        ]);

        $this->redirect('rrhh/usuarios');
    }

    public function actualizar(string $id): void
    {
        $this->requireRole('rrhh');
        if (!$this->isPost() || !$this->checkCsrf()) {
            $this->redirect('rrhh/usuarios');
        }

        $usuarioModel = new Usuario();
        $objetivo = $usuarioModel->find((int)$id);
        if (!$objetivo || !$this->puedeGestionar(Auth::user(), $objetivo)) {
            http_response_code(403);
            $this->viewOnly('errors/403');
            return;
        }

        $usuarioModel->update((int)$id, [
            'nombre'       => trim((string)$this->input('nombre')),
            'apellidos'    => trim((string)$this->input('apellidos')),
            'email'        => trim((string)$this->input('email')),
            'departamento' => trim((string)$this->input('departamento')),
        ]);

        $this->redirect('rrhh/usuarios');
    }

    /** Activar / desactivar cuenta en lugar de borrado físico (RF6) */
    public function toggleActivo(string $id): void
    {
        $this->requireRole('rrhh');
        if ($this->isPost() && $this->checkCsrf()) {
            $usuarioModel = new Usuario();
            $objetivo = $usuarioModel->find((int)$id);
            if (!$objetivo || !$this->puedeGestionar(Auth::user(), $objetivo)) {
                http_response_code(403);
                $this->viewOnly('errors/403');
                return;
            }
            $usuarioModel->setActivo((int)$id, !(bool)$objetivo['activo']);
        }
        $this->redirect('rrhh/usuarios');
    }

    public function eliminar(string $id): void
    {
        $this->requireRole('rrhh');
        if ($this->isPost() && $this->checkCsrf()) {
            $usuarioModel = new Usuario();
            $objetivo = $usuarioModel->find((int)$id);
            if (!$objetivo || !$this->puedeGestionar(Auth::user(), $objetivo)) {
                http_response_code(403);
                $this->viewOnly('errors/403');
                return;
            }
            $usuarioModel->delete((int)$id);
        }
        $this->redirect('rrhh/usuarios');
    }

    /** ¿Puede $actor modificar/activar/eliminar la cuenta $objetivo? */
    private function puedeGestionar(array $actor, array $objetivo): bool
    {
        if ($actor['rol'] === 'administrador') {
            return true;
        }
        return $actor['rol'] === 'rrhh'
            && $objetivo['id_empresa'] == $actor['id_empresa']
            && $objetivo['rol'] === 'trabajador';
    }
}
