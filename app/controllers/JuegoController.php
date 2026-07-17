<?php
/**
 * JuegoController
 * RF3: el trabajador selecciona y ejecuta serious games asignados.
 * RF6 (para juegos): RRHH gestiona el catálogo de juegos disponibles.
 */
class JuegoController extends Controller
{
    /** Catálogo de juegos asignados al trabajador logueado */
    public function catalogo(): void
    {
        $this->requireAuth();
        $user = Auth::user();

        $asignacionModel = new AsignacionJuego();
        $asignaciones = $asignacionModel->listarPorUsuario($user['id_usuario']);

        $this->view('trabajador/juegos', [
            'title'        => 'Mis juegos',
            'asignaciones' => $asignaciones,
        ]);
    }

    /** Historial y estadísticas personales del trabajador ("Mi progreso") */
    public function progreso(): void
    {
        $this->requireAuth();
        $user = Auth::user();

        $resultadoModel = new Resultado();
        $historial = $resultadoModel->historialPorUsuario($user['id_usuario']);

        $this->view('trabajador/progreso', [
            'title'     => 'Mi progreso',
            'historial' => $historial,
        ]);
    }

    /** Pantalla donde se ejecuta el serious game (Nivel 3) */
    public function jugar(string $idAsignacion): void
    {
        $this->requireAuth();
        $user = Auth::user();

        $asignacionModel = new AsignacionJuego();
        $juegoModel = new Juego();

        $asignacion = $asignacionModel->find((int)$idAsignacion);
        if (!$asignacion || (int)$asignacion['id_usuario'] !== (int)$user['id_usuario']) {
            http_response_code(404);
            $this->viewOnly('errors/404');
            return;
        }

        $juego = $juegoModel->find((int)$asignacion['id_juego']);
        if (!$juego) {
            http_response_code(404);
            $this->viewOnly('errors/404');
            return;
        }

        $asignacionModel->marcarEstado($asignacion['id_asignacion'], 'en_progreso');

        $vista = match ($juego['slug']) {
            'quiz'     => 'juegos/quiz',
            'memoria'  => 'juegos/memoria',
            'reaccion' => 'juegos/reaccion',
            default    => null,
        };

        if (!$vista) {
            http_response_code(404);
            $this->viewOnly('errors/404');
            return;
        }

        $this->view($vista, [
            'title'      => $juego['titulo'],
            'juego'      => $juego,
            'asignacion' => $asignacion,
            'csrf'       => $this->csrfToken(),
        ]);
    }

    /** Endpoint AJAX: guarda el resultado de una partida (RF4) */
    public function guardarResultado(): void
    {
        $this->requireAuth();

        if (!$this->isPost()) {
            $this->json(['ok' => false, 'error' => 'Método no permitido'], 405);
        }

        $user = Auth::user();
        $idJuego = (int)$this->input('id_juego');
        $idAsignacion = (int)$this->input('id_asignacion');
        $puntuacion = (int)$this->input('puntuacion');
        $tiempo = (int)$this->input('tiempo');

        if ($idJuego <= 0 || $puntuacion < 0 || $tiempo < 0) {
            $this->json(['ok' => false, 'error' => 'Datos inválidos'], 422);
        }

        $resultadoModel = new Resultado();
        $asignacionModel = new AsignacionJuego();

        $idResultado = $resultadoModel->registrar(
            $user['id_usuario'],
            $idJuego,
            $puntuacion,
            $tiempo,
            $idAsignacion ?: null
        );

        if ($idAsignacion) {
            $asignacionModel->marcarEstado($idAsignacion, 'completado');
        }

        $this->json(['ok' => true, 'id_resultado' => $idResultado]);
    }

    // ----------------------------------------------------------------
    // Gestión del catálogo de juegos (RRHH)
    // ----------------------------------------------------------------

    public function gestion(): void
    {
        $this->requireRole('rrhh');
        $juegos = (new Juego())->all();
        $this->view('rrhh/juegos', ['title' => 'Gestión de juegos', 'juegos' => $juegos, 'csrf' => $this->csrfToken()]);
    }

    public function crear(): void
    {
        $this->requireRole('rrhh');

        if (!$this->isPost() || !$this->checkCsrf()) {
            $this->redirect('rrhh/juegos');
        }

        $juegoModel = new Juego();
        $idJuego = $juegoModel->create([
            'titulo'           => trim((string)$this->input('titulo')),
            'descripcion'      => trim((string)$this->input('descripcion')),
            'tipo_competencia' => trim((string)$this->input('tipo_competencia')),
            'dificultad'       => $this->input('dificultad', 'facil'),
            'slug'             => $this->input('slug'),
            'activo'           => $this->input('activo') ? 1 : 0,
        ]);

        if (!empty($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
            $this->guardarImagen($idJuego, $_FILES['imagen']);
        }

        $this->redirect('rrhh/juegos');
    }

    /** Sube/reemplaza la imagen de cabecera de un juego del catálogo */
    public function actualizarImagen(string $id): void
    {
        $this->requireRole('rrhh');
        if (!$this->isPost() || !$this->checkCsrf()) {
            $this->redirect('rrhh/juegos');
        }

        if (!empty($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
            $this->guardarImagen((int)$id, $_FILES['imagen']);
        }

        $this->redirect('rrhh/juegos');
    }

    /** Valida y guarda la imagen subida para un juego; ignora silenciosamente si no es válida */
    private function guardarImagen(int $idJuego, array $archivo): void
    {
        $maxBytes = 2 * 1024 * 1024;
        if ($archivo['size'] > $maxBytes) {
            return;
        }

        // No confiar en la extensión/MIME del navegador: se detecta leyendo el fichero.
        $info = @getimagesize($archivo['tmp_name']);
        $extensiones = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        if (!$info || !isset($extensiones[$info['mime']])) {
            return;
        }

        $juegoModel = new Juego();
        $anterior = $juegoModel->find($idJuego);

        $dir = __DIR__ . '/../../public/uploads/juegos';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $nombreArchivo = 'j' . $idJuego . '.' . $extensiones[$info['mime']];
        if (!move_uploaded_file($archivo['tmp_name'], $dir . '/' . $nombreArchivo)) {
            return;
        }

        if (!empty($anterior['imagen']) && $anterior['imagen'] !== $nombreArchivo) {
            @unlink($dir . '/' . $anterior['imagen']);
        }

        $juegoModel->updateImagen($idJuego, $nombreArchivo);
    }

    public function actualizar(string $id): void
    {
        $this->requireRole('rrhh');

        if (!$this->isPost() || !$this->checkCsrf()) {
            $this->redirect('rrhh/juegos');
        }

        $juegoModel = new Juego();
        $juegoModel->update((int)$id, [
            'titulo'           => trim((string)$this->input('titulo')),
            'descripcion'      => trim((string)$this->input('descripcion')),
            'tipo_competencia' => trim((string)$this->input('tipo_competencia')),
            'dificultad'       => $this->input('dificultad', 'facil'),
            'activo'           => $this->input('activo') ? 1 : 0,
        ]);

        $this->redirect('rrhh/juegos');
    }

    public function eliminar(string $id): void
    {
        $this->requireRole('rrhh');
        if ($this->isPost() && $this->checkCsrf()) {
            (new Juego())->delete((int)$id);
        }
        $this->redirect('rrhh/juegos');
    }
}
