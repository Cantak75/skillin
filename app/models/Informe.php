<?php
/**
 * Modelo Informe
 * RF7: los administradores pueden generar informes de rendimiento.
 * El informe guarda metadatos; los datos se calculan dinámicamente
 * a partir de `resultado` para evitar redundancia (ver justificación
 * del modelo E/R: "separación de resultados e informes").
 */
class Informe
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function crear(int $idUsuarioRrhh, int $idEmpresa, string $tipo, ?string $observaciones): int
    {
        $sql = 'INSERT INTO informe (id_usuario_rrhh, id_empresa, tipo, observaciones)
                VALUES (:rrhh, :empresa, :tipo, :obs)';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'rrhh'    => $idUsuarioRrhh,
            'empresa' => $idEmpresa,
            'tipo'    => $tipo,
            'obs'     => $observaciones,
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function listarPorEmpresa(int $idEmpresa): array
    {
        $sql = 'SELECT i.*, u.nombre, u.apellidos
                FROM informe i
                JOIN usuario u ON u.id_usuario = i.id_usuario_rrhh
                WHERE i.id_empresa = :empresa
                ORDER BY i.fecha_generacion DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['empresa' => $idEmpresa]);
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM informe WHERE id_informe = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
}
