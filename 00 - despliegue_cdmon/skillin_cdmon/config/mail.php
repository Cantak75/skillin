<?php
/**
 * Configuración SMTP para el envío de correos (recuperación de contraseña).
 *
 * Rellena 'host', 'username' y 'password' con los datos de tu proveedor
 * antes de usar "Recuperar contraseña". Mientras 'host' tenga el valor de
 * ejemplo, Mailer registrará el error en el log de Apache sin enviar nada.
 *
 * Ejemplos habituales:
 *   - Gmail:      host=smtp.gmail.com          puerto=587  cifrado=tls
 *                 (usuario = tu email, password = "contraseña de aplicación", no la normal)
 *   - Outlook365: host=smtp.office365.com       puerto=587  cifrado=tls
 *   - Mailtrap (bandeja de pruebas, no envía email real): host=sandbox.smtp.mailtrap.io puerto=2525 cifrado=tls
 */
return [
    'host'       => 'smtp.tagtak.com',
    'port'       => 587,
    'encryption' => 'tls', // 'tls' (STARTTLS) o 'ssl'
    'username'   => '',
    'password'   => '',
    'from_email' => 'info@tagtak.com',
    'from_name'  => 'Skillin',
];