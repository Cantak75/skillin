<?php
/**
 * Modelo Resultado
 * RF4: registro de puntuación, tiempo y fecha de cada partida completada.
 */
class Resultado
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function registrar(int $idUsuario, int $idJuego, int $puntuacion, int $tiempoEmpleado, ?int $idAsignacion = null): int
    {
        $sql = 'INSERT INTO resultado (id_usuario, id_juego, puntuacion, tiempo_empleado, id_asignacion)
                VALUES (:usuario, :juego, :puntuacion, :tiempo, :asignacion)';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'usuario'    => $idUsuario,
            'juego'      => $idJuego,
            'puntuacion' => $puntuacion,
            'tiempo'     => $tiempoEmpleado,
            'asignacion' => $idAsignacion,
        ]);
        return (int)$this->db->lastInsertId();
    }

    /** Historial de un trabajador (todos sus intentos) */
    public function historialPorUsuario(int $idUsuario): array
    {
        $sql = 'SELECT r.*, j.titulo AS juego_titulo
                FROM resultado r
                JOIN juego j ON j.id_juego = r.id_juego
                WHERE r.id_usuario = :usuario
                ORDER BY r.fecha_realizacion DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['usuario' => $idUsuario]);
        return $stmt->fetchAll();
    }

    /** RF7: rendimiento de un grupo de trabajadores en un juego concreto */
    public function rendimientoPorJuego(int $idJuego, int $idEmpresa): array
    {
        $sql = 'SELECT r.*, u.nombre, u.apellidos, u.departamento
                FROM resultado r
                JOIN usuario u ON u.id_usuario = r.id_usuario
                WHERE r.id_juego = :juego AND u.id_empresa = :empresa
                ORDER BY r.puntuacion DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['juego' => $idJuego, 'empresa' => $idEmpresa]);
        return $stmt->fetchAll();
    }

    /** Estadísticas agregadas para el dashboard de RRHH */
    public function estadisticasEmpresa(int $idEmpresa): array
    {
        $sql = 'SELECT j.titulo, COUNT(r.id_resultado) AS total_partidas,
                       ROUND(AVG(r.puntuacion), 1) AS media_puntuacion
                FROM resultado r
                JOIN usuario u ON u.id_usuario = r.id_usuario
                JOIN juego j ON j.id_juego = r.id_juego
                WHERE u.id_empresa = :empresa
                GROUP BY j.id_juego, j.titulo
                ORDER BY j.titulo';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['empresa' => $idEmpresa]);
        return $stmt->fetchAll();
    }

    public function ultimoResultado(int $idUsuario): ?array
    {
        $sql = 'SELECT r.*, j.titulo AS juego_titulo
                FROM resultado r JOIN juego j ON j.id_juego = r.id_juego
                WHERE r.id_usuario = :usuario
                ORDER BY r.fecha_realizacion DESC LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['usuario' => $idUsuario]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function contarPartidasUsuario(int $idUsuario): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM resultado WHERE id_usuario = :usuario');
        $stmt->execute(['usuario' => $idUsuario]);
        return (int)$stmt->fetchColumn();
    }
}
