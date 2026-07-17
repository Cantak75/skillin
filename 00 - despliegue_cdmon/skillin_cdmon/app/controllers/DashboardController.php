<?php
/**
 * DashboardController
 * Muestra un panel contextual diferente según el rol (RNF2, arquitectura Nivel 1).
 */
class DashboardController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();

        if (Auth::isAdministrador()) {
            $this->rrhh(); // el administrador ve el mismo panel, sobre la empresa que elija
        } elseif (Auth::isRrhh()) {
            $this->rrhh();
        } else {
            $this->trabajador();
        }
    }

    private function trabajador(): void
    {
        $user = Auth::user();
        $asignacionModel = new AsignacionJuego();
        $resultadoModel = new Resultado();

        $asignaciones = $asignacionModel->listarPorUsuario($user['id_usuario']);
        $ultimoResultado = $resultadoModel->ultimoResultado($user['id_usuario']);
        $totalPartidas = $resultadoModel->contarPartidasUsuario($user['id_usuario']);

        $pendientes = array_filter($asignaciones, fn($a) => $a['estado'] !== 'completado');

        $this->view('trabajador/dashboard', [
            'title'            => 'Mi panel',
            'asignaciones'     => $asignaciones,
            'pendientes'       => $pendientes,
            'ultimoResultado'  => $ultimoResultado,
            'totalPartidas'    => $totalPartidas,
        ]);
    }

    private function rrhh(): void
    {
        $user = Auth::user();
        $idEmpresa = $this->empresaSeleccionada($user);
        $usuarioModel = new Usuario();
        $resultadoModel = new Resultado();
        $asignacionModel = new AsignacionJuego();

        $trabajadores = $usuarioModel->listarTrabajadoresPorEmpresa($idEmpresa);
        $estadisticas = $resultadoModel->estadisticasEmpresa($idEmpresa);
        $asignaciones = $asignacionModel->listarPorEmpresa($idEmpresa);

        $pendientesGlobal = array_filter($asignaciones, fn($a) => $a['estado'] === 'pendiente');

        $this->view('rrhh/dashboard', [
            'title'         => 'Panel RRHH',
            'trabajadores'  => $trabajadores,
            'estadisticas'  => $estadisticas,
            'asignaciones'  => array_slice($asignaciones, 0, 8),
            'totalPendientes' => count($pendientesGlobal),
            'totalTrabajadores' => count($trabajadores),
            'empresas'      => Auth::isAdministrador() ? (new Empresa())->all() : [],
            'empresaActual' => $idEmpresa,
        ]);
    }
}
