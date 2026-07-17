<?php
/**
 * Modelo Juego (catálogo de serious games)
 */
class Juego
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function all(bool $soloActivos = false): array
    {
        $sql = 'SELECT * FROM juego';
        if ($soloActivos) {
            $sql .= ' WHERE activo = 1';
        }
        $sql .= ' ORDER BY titulo ASC';
        return $this->db->query($sql)->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM juego WHERE id_juego = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findBySlug(string $slug): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM juego WHERE slug = :slug');
        $stmt->execute(['slug' => $slug]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function create(array $data): int
    {
        $sql = 'INSERT INTO juego (titulo, descripcion, tipo_competencia, dificultad, slug, activo)
                VALUES (:titulo, :descripcion, :tipo_competencia, :dificultad, :slug, :activo)';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'titulo'           => $data['titulo'],
            'descripcion'      => $data['descripcion'] ?? null,
            'tipo_competencia' => $data['tipo_competencia'] ?? null,
            'dificultad'       => $data['dificultad'] ?? 'facil',
            'slug'             => $data['slug'],
            'activo'           => $data['activo'] ?? 1,
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $sql = 'UPDATE juego SET titulo = :titulo, descripcion = :descripcion,
                tipo_competencia = :tipo_competencia, dificultad = :dificultad, activo = :activo
                WHERE id_juego = :id';
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'titulo'           => $data['titulo'],
            'descripcion'      => $data['descripcion'] ?? null,
            'tipo_competencia' => $data['tipo_competencia'] ?? null,
            'dificultad'       => $data['dificultad'] ?? 'facil',
            'activo'           => $data['activo'] ?? 1,
            'id'               => $id,
        ]);
    }

    public function updateImagen(int $id, string $nombreArchivo): bool
    {
        $stmt = $this->db->prepare('UPDATE juego SET imagen = :imagen WHERE id_juego = :id');
        return $stmt->execute(['imagen' => $nombreArchivo, 'id' => $id]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM juego WHERE id_juego = :id');
        return $stmt->execute(['id' => $id]);
    }
}
