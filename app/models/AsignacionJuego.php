<?php
/**
 * Modelo AsignacionJuego
 * Entidad intermedia N:M entre usuario y juego (RF5).
 */
class AsignacionJuego
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /** Asigna un juego a un único trabajador */
    public function asignar(int $idUsuario, int $idJuego, ?string $fechaLimite, int $asignadoPor): int
    {
        $sql = 'INSERT INTO asignacion_juego (id_usuario, id_juego, fecha_limite, asignado_por)
                VALUES (:usuario, :juego, :fecha_limite, :asignado_por)';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'usuario'      => $idUsuario,
            'juego'        => $idJuego,
            'fecha_limite' => $fechaLimite ?: null,
            'asignado_por' => $asignadoPor,
        ]);
        return (int)$this->db->lastInsertId();
    }

    /**
     * true si el usuario ya tiene ese juego asignado en un estado no completado
     * (pendiente, en_progreso o caducado). Si todas sus asignaciones previas de
     * ese juego están completadas (o no tiene ninguna), devuelve false.
     */
    public function tienePendiente(int $idUsuario, int $idJuego): bool
    {
        $sql = "SELECT COUNT(*) FROM asignacion_juego
                WHERE id_usuario = :usuario AND id_juego = :juego AND estado != 'completado'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['usuario' => $idUsuario, 'juego' => $idJuego]);
        return (bool)$stmt->fetchColumn();
    }

    /** Asigna un juego a varios trabajadores (grupo) de una sola vez - RF5 */
    public function asignarMultiple(array $idsUsuarios, int $idJuego, ?string $fechaLimite, int $asignadoPor): int
    {
        $count = 0;
        foreach ($idsUsuarios as $idUsuario) {
            $this->asignar((int)$idUsuario, $idJuego, $fechaLimite, $asignadoPor);
            $count++;
        }
        return $count;
    }

    /** Juegos asignados a un trabajador, con datos del juego (RF3) */
    public function listarPorUsuario(int $idUsuario): array
    {
        $sql = 'SELECT a.*, j.titulo, j.descripcion, j.slug, j.dificultad, j.tipo_competencia, j.imagen
                FROM asignacion_juego a
                JOIN juego j ON j.id_juego = a.id_juego
                WHERE a.id_usuario = :usuario
                ORDER BY a.fecha_asignacion DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['usuario' => $idUsuario]);
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM asignacion_juego WHERE id_asignacion = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function marcarEstado(int $id, string $estado): bool
    {
        $stmt = $this->db->prepare('UPDATE asignacion_juego SET estado = :estado WHERE id_asignacion = :id');
        return $stmt->execute(['estado' => $estado, 'id' => $id]);
    }

    /** Listado de asignaciones de la empresa, para el panel RRHH */
    public function listarPorEmpresa(int $idEmpresa): array
    {
        $sql = 'SELECT a.*, u.nombre, u.apellidos, j.titulo AS juego_titulo
                FROM asignacion_juego a
                JOIN usuario u ON u.id_usuario = a.id_usuario
                JOIN juego j ON j.id_juego = a.id_juego
                WHERE u.id_empresa = :empresa
                ORDER BY a.fecha_asignacion DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['empresa' => $idEmpresa]);
        return $stmt->fetchAll();
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM asignacion_juego WHERE id_asignacion = :id');
        return $stmt->execute(['id' => $id]);
    }
}
