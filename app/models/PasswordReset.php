<?php
/**
 * Modelo PasswordReset
 * Tokens de un solo uso para la recuperación de contraseña por email.
 */
class PasswordReset
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function crear(int $idUsuario, string $tokenHash, string $expiraEn): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO password_reset (id_usuario, token_hash, expira_en) VALUES (:id, :hash, :expira)'
        );
        $stmt->execute(['id' => $idUsuario, 'hash' => $tokenHash, 'expira' => $expiraEn]);
    }

    /** Devuelve el registro si el token existe, no ha caducado y no se ha usado */
    public function buscarValidoPorHash(string $tokenHash): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM password_reset WHERE token_hash = :hash AND usado = 0 AND expira_en > NOW() LIMIT 1'
        );
        $stmt->execute(['hash' => $tokenHash]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function marcarUsado(int $idReset): void
    {
        $stmt = $this->db->prepare('UPDATE password_reset SET usado = 1 WHERE id_reset = :id');
        $stmt->execute(['id' => $idReset]);
    }

    /** Invalida tokens anteriores sin usar del usuario al pedir uno nuevo */
    public function invalidarPendientes(int $idUsuario): void
    {
        $stmt = $this->db->prepare('UPDATE password_reset SET usado = 1 WHERE id_usuario = :id AND usado = 0');
        $stmt->execute(['id' => $idUsuario]);
    }
}