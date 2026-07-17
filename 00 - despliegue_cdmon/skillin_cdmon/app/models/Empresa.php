<?php
/**
 * Modelo Empresa
 */
class Empresa
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM empresa WHERE id_empresa = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function all(): array
    {
        $stmt = $this->db->query('SELECT * FROM empresa ORDER BY nombre ASC');
        return $stmt->fetchAll();
    }

    public function nombreExists(string $nombre): bool
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM empresa WHERE nombre = :nombre');
        $stmt->execute(['nombre' => $nombre]);
        return (bool)$stmt->fetchColumn();
    }

    public function create(string $nombre, ?string $sector): int
    {
        $stmt = $this->db->prepare('INSERT INTO empresa (nombre, sector) VALUES (:nombre, :sector)');
        $stmt->execute(['nombre' => $nombre, 'sector' => $sector]);
        return (int)$this->db->lastInsertId();
    }
}
