<?php
/**
 * AsignacionController
 * RF5: los administradores pueden asignar uno o varios juegos
 * a usuarios o grupos específicos de trabajadores.
 */
class AsignacionController extends Controller
{
    public function index(): void
    {
        $this->requireRole('rrhh');
        $user = Auth::user();
        $idEmpresa = $this->empresaSeleccionada($user);

        $trabajadores = (new Usuario())->listarTrabajadoresPorEmpresa($idEmpresa);
        $juegos = (new Juego())->all(true);
        $asignaciones = (new AsignacionJuego())->listarPorEmpresa($idEmpresa);

        $this->view('rrhh/asignaciones', [
            'title'         => 'Asignación de juegos',
            'trabajadores'  => $trabajadores,
            'juegos'        => $juegos,
            'asignaciones'  => $asignaciones,
            'csrf'          => $this->csrfToken(),
            'empresas'      => Auth::isAdministrador() ? (new Empresa())->all() : [],
            'empresaActual' => $idEmpresa,
        ]);
    }

    public function asignar(): void
    {
        $this->requireRole('rrhh');
        if (!$this->isPost() || !$this->checkCsrf()) {
            $this->redirect('rrhh/asignaciones');
        }

        $user = Auth::user();
        $idJuego = (int)$this->input('id_juego');
        $fechaLimite = $this->input('fecha_limite') ?: null;
        $idsUsuarios = $_POST['trabajadores'] ?? [];

        if ($idJuego > 0 && !empty($idsUsuarios)) {
            $asignacionModel = new AsignacionJuego();

            // No se puede volver a asignar un juego a quien ya lo tiene pendiente,
            // en progreso o caducado; sí se puede si solo tiene instancias completadas.
            $idsValidos = [];
            $omitidos = 0;
            foreach ($idsUsuarios as $idUsuario) {
                if ($asignacionModel->tienePendiente((int)$idUsuario, $idJuego)) {
                    $omitidos++;
                    continue;
                }
                $idsValidos[] = $idUsuario;
            }

            if (!empty($idsValidos)) {
                $asignacionModel->asignarMultiple($idsValidos, $idJuego, $fechaLimite, $user['id_usuario']);
            }

            if ($omitidos > 0) {
                $this->redirect('rrhh/asignaciones?aviso=omitidos&n=' . $omitidos);
                return;
            }
        }

        $this->redirect('rrhh/asignaciones');
    }

    public function eliminar(string $id): void
    {
        $this->requireRole('rrhh');
        if ($this->isPost() && $this->checkCsrf()) {
            (new AsignacionJuego())->delete((int)$id);
        }
        $this->redirect('rrhh/asignaciones');
    }
}
