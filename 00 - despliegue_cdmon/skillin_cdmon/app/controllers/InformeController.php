<?php
/**
 * InformeController
 * RF7: generar y visualizar informes tabulares/gráficos de rendimiento
 * de un grupo de trabajadores en un juego concreto.
 */
class InformeController extends Controller
{
    public function index(): void
    {
        $this->requireRole('rrhh');
        $user = Auth::user();
        $idEmpresa = $this->empresaSeleccionada($user);

        $juegos = (new Juego())->all();
        $idJuego = (int)$this->input('id_juego', 0);

        $rendimiento = [];
        $juegoSeleccionado = null;
        if ($idJuego > 0) {
            $juegoModel = new Juego();
            $juegoSeleccionado = $juegoModel->find($idJuego);
            $rendimiento = (new Resultado())->rendimientoPorJuego($idJuego, $idEmpresa);
        }

        $informes = (new Informe())->listarPorEmpresa($idEmpresa);
        $estadisticas = (new Resultado())->estadisticasEmpresa($idEmpresa);

        $this->view('rrhh/informes', [
            'title'              => 'Informes y analíticas',
            'juegos'             => $juegos,
            'idJuego'            => $idJuego,
            'juegoSeleccionado'  => $juegoSeleccionado,
            'rendimiento'        => $rendimiento,
            'informes'           => $informes,
            'estadisticas'       => $estadisticas,
            'csrf'               => $this->csrfToken(),
            'empresas'           => Auth::isAdministrador() ? (new Empresa())->all() : [],
            'empresaActual'      => $idEmpresa,
        ]);
    }

    public function generar(): void
    {
        $this->requireRole('rrhh');
        if (!$this->isPost() || !$this->checkCsrf()) {
            $this->redirect('rrhh/informes');
        }

        $user = Auth::user();
        $tipo = trim((string)$this->input('tipo', 'rendimiento'));
        $observaciones = trim((string)$this->input('observaciones', ''));

        (new Informe())->crear($user['id_usuario'], $this->empresaSeleccionada($user), $tipo, $observaciones ?: null);

        $this->redirect('rrhh/informes');
    }

    /** Exportación simple a CSV del rendimiento de un juego (RF7 "exportar") */
    public function exportarCsv(string $idJuego): void
    {
        $this->requireRole('rrhh');
        $user = Auth::user();

        $juegoModel = new Juego();
        $juego = $juegoModel->find((int)$idJuego);
        if (!$juego) {
            $this->redirect('rrhh/informes');
            return;
        }

        $rendimiento = (new Resultado())->rendimientoPorJuego((int)$idJuego, $this->empresaSeleccionada($user));

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="informe_' . $juego['slug'] . '.csv"');

        $out = fopen('php://output', 'w');
        fputcsv($out, ['Trabajador', 'Departamento', 'Puntuación', 'Tiempo (s)', 'Fecha']);
        foreach ($rendimiento as $r) {
            fputcsv($out, [
                $r['nombre'] . ' ' . $r['apellidos'],
                $r['departamento'],
                $r['puntuacion'],
                $r['tiempo_empleado'],
                $r['fecha_realizacion'],
            ]);
        }
        fclose($out);
        exit;
    }
}
