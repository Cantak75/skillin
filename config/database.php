<?php
/**
 * Clase Database
 * Gestiona la conexión PDO (patrón Singleton) a la base de datos MySQL/MariaDB.
 */
class Database
{
    private static ?PDO $connection = null;

    // -----------------------------------------------------------------
    // AJUSTA ESTOS DATOS A TU ENTORNO
    // -----------------------------------------------------------------
    private const HOST    = 'localhost';
    private const DBNAME  = 'skillindb';
    private const USER    = 'root';
    private const PASS    = '';
    private const CHARSET = 'utf8mb4';

    public static function getConnection(): PDO
    {
        if (self::$connection === null) {
            $dsn = 'mysql:host=' . self::HOST . ';dbname=' . self::DBNAME . ';charset=' . self::CHARSET;

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            try {
                self::$connection = new PDO($dsn, self::USER, self::PASS, $options);
            } catch (PDOException $e) {
                die('Error de conexión a la base de datos: ' . $e->getMessage());
            }
        }

        return self::$connection;
    }
}
