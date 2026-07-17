<?php
/**
 * EmpresaController
 * Alta de empresas en la plataforma - exclusivo del rol administrador.
 */
class EmpresaController extends Controller
{
    public function index(): void
    {
        $this->requireRole('administrador');
        $empresaModel = new Empresa();

        $this->view('admin/empresas', [
            'title'    => 'Empresas',
            'empresas' => $empresaModel->all(),
            'csrf'     => $this->csrfToken(),
        ]);
    }

    public function crear(): void
    {
        $this->requireRole('administrador');
        if (!$this->isPost() || !$this->checkCsrf()) {
            $this->redirect('admin/empresas');
        }

        $nombre = trim((string)$this->input('nombre'));
        $sector = trim((string)$this->input('sector'));

        if ($nombre === '') {
            $this->redirect('admin/empresas?error=nombre_requerido');
            return;
        }

        $empresaModel = new Empresa();
        if ($empresaModel->nombreExists($nombre)) {
            $this->redirect('admin/empresas?error=nombre_duplicado');
            return;
        }

        $empresaModel->create($nombre, $sector ?: null);
        $this->redirect('admin/empresas?ok=1');
    }
}