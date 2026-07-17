

<?php
/**
 * seed_passwords.php
 * ---------------------------------------------------------------
 * Ejecutar UNA VEZ desde consola (php seed_passwords.php) después
 * de importar skillindb.sql para sustituir los hashes de ejemplo por
 * hashes bcrypt reales y poder iniciar sesión con las cuentas demo.
 *
 * Uso:
 *   php database/seed_passwords.php
 * ---------------------------------------------------------------
 */

require_once __DIR__ . '/../config/database.php';

try {
    $pdo = Database::getConnection();

    $usuarios = [
        'laura.martin@albaconstrucciones.com' => '1234',
        'carlos.ruiz@innotech.com'            => '1234',
        'ana.garcia@innotech.com'             => '1234',
        'juan.chacon@albaconstrucciones.com'  => '1234',
        'eva.flores@albaconstrucciones.com'   => '1234',
        'elsa.cantero@albaconstrucciones.com' => '1234',
        'tak@tagtak.com'                      => '1234',
    ];

    $stmt = $pdo->prepare('UPDATE usuario SET contrasena = :hash WHERE email = :email');

    foreach ($usuarios as $email => $plain) {
        $hash = password_hash($plain, PASSWORD_BCRYPT);
        $stmt->execute(['hash' => $hash, 'email' => $email]);
        echo "Actualizado: {$email} -> contraseña: {$plain}" . PHP_EOL;
    }

    echo PHP_EOL . "Listo. Ya puedes iniciar sesión con las cuentas demo." . PHP_EOL;
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . PHP_EOL;
}
