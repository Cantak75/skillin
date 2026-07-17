<?php
/**
 * Modelo Usuario
 * Representa la tabla `usuario` (Trabajador o RRHH/Administrador).
 */
class Usuario
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM usuario WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM usuario WHERE id_usuario = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /** Igual que find(), pero incluye el nombre de la empresa a la que pertenece el usuario */
    public function findConEmpresa(int $id): ?array
    {
        $sql = 'SELECT u.*, e.nombre AS nombre_empresa
                FROM usuario u
                JOIN empresa e ON e.id_empresa = u.id_empresa
                WHERE u.id_usuario = :id LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function emailExists(string $email, ?int $exceptId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM usuario WHERE email = :email';
        $params = ['email' => $email];
        if ($exceptId) {
            $sql .= ' AND id_usuario != :id';
            $params['id'] = $exceptId;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (bool)$stmt->fetchColumn();
    }

    /** Listado de trabajadores de una empresa (RF6 - gestión de plantilla) */
    public function listarTrabajadoresPorEmpresa(int $idEmpresa, string $busqueda = ''): array
    {
        $sql = 'SELECT * FROM usuario WHERE id_empresa = :empresa AND rol = "trabajador"';
        $params = ['empresa' => $idEmpresa];

        if ($busqueda !== '') {
            $sql .= ' AND (nombre LIKE :busqueda1 OR apellidos LIKE :busqueda2 OR email LIKE :busqueda3)';
            $like = '%' . $busqueda . '%';
            $params['busqueda1'] = $like;
            $params['busqueda2'] = $like;
            $params['busqueda3'] = $like;
        }

        $sql .= ' ORDER BY apellidos ASC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /** Como listarTrabajadoresPorEmpresa(), pero incluye todos los roles (uso: administrador) */
    public function listarPorEmpresa(int $idEmpresa, string $busqueda = ''): array
    {
        $sql = 'SELECT * FROM usuario WHERE id_empresa = :empresa';
        $params = ['empresa' => $idEmpresa];

        if ($busqueda !== '') {
            $sql .= ' AND (nombre LIKE :busqueda1 OR apellidos LIKE :busqueda2 OR email LIKE :busqueda3)';
            $like = '%' . $busqueda . '%';
            $params['busqueda1'] = $like;
            $params['busqueda2'] = $like;
            $params['busqueda3'] = $like;
        }

        $sql .= ' ORDER BY apellidos ASC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function create(array $data): int
    {
        $sql = 'INSERT INTO usuario (nombre, apellidos, email, contrasena, rol, departamento, id_empresa)
                VALUES (:nombre, :apellidos, :email, :contrasena, :rol, :departamento, :id_empresa)';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'nombre'       => $data['nombre'],
            'apellidos'    => $data['apellidos'],
            'email'        => $data['email'],
            'contrasena'   => password_hash($data['contrasena'], PASSWORD_BCRYPT),
            'rol'          => $data['rol'] ?? 'trabajador',
            'departamento' => $data['departamento'] ?? null,
            'id_empresa'   => $data['id_empresa'],
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $sql = 'UPDATE usuario SET nombre = :nombre, apellidos = :apellidos, email = :email,
                departamento = :departamento WHERE id_usuario = :id';
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'nombre'       => $data['nombre'],
            'apellidos'    => $data['apellidos'],
            'email'        => $data['email'],
            'departamento' => $data['departamento'] ?? null,
            'id'           => $id,
        ]);
    }

    public function updateFoto(int $id, string $nombreArchivo): bool
    {
        $stmt = $this->db->prepare('UPDATE usuario SET foto = :foto WHERE id_usuario = :id');
        return $stmt->execute(['foto' => $nombreArchivo, 'id' => $id]);
    }

    public function updatePassword(int $id, string $plainPassword): bool
    {
        $stmt = $this->db->prepare('UPDATE usuario SET contrasena = :pass WHERE id_usuario = :id');
        return $stmt->execute([
            'pass' => password_hash($plainPassword, PASSWORD_BCRYPT),
            'id'   => $id,
        ]);
    }

    /** RF6: activar / desactivar cuenta (no se borra físicamente) */
    public function setActivo(int $id, bool $activo): bool
    {
        $stmt = $this->db->prepare('UPDATE usuario SET activo = :activo WHERE id_usuario = :id');
        return $stmt->execute(['activo' => $activo ? 1 : 0, 'id' => $id]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM usuario WHERE id_usuario = :id');
        return $stmt->execute(['id' => $id]);
    }
}
