<?php
/**
 * Mailer
 * Cliente SMTP mínimo (sin dependencias externas) para el envío de correos
 * transaccionales, como el enlace de recuperación de contraseña.
 */
class Mailer
{
    private array $config;

    public function __construct()
    {
        $this->config = require __DIR__ . '/../../config/mail.php';
    }

    public function enviar(string $paraEmail, string $paraNombre, string $asunto, string $cuerpoHtml): bool
    {
        $cfg = $this->config;

        if (empty($cfg['host']) || $cfg['host'] === 'smtp.example.com') {
            error_log('Mailer: config/mail.php no está configurado (host de ejemplo). No se envía el correo.');
            return false;
        }

        $transporte = $cfg['encryption'] === 'ssl' ? 'ssl://' : 'tcp://';
        $socket = @stream_socket_client(
            $transporte . $cfg['host'] . ':' . $cfg['port'],
            $errno,
            $errstr,
            15
        );
        if (!$socket) {
            error_log("Mailer: no se pudo conectar a {$cfg['host']}:{$cfg['port']} - $errstr");
            return false;
        }

        try {
            $this->leer($socket);
            $this->comando($socket, 'EHLO skillin.local', '250');

            if ($cfg['encryption'] === 'tls') {
                $this->comando($socket, 'STARTTLS', '220');
                if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                    error_log('Mailer: no se pudo iniciar TLS con el servidor SMTP.');
                    return false;
                }
                $this->comando($socket, 'EHLO skillin.local', '250');
            }

            if (!empty($cfg['username'])) {
                $this->comando($socket, 'AUTH LOGIN', '334');
                $this->comando($socket, base64_encode($cfg['username']), '334');
                $this->comando($socket, base64_encode($cfg['password']), '235');
            }

            $this->comando($socket, "MAIL FROM:<{$cfg['from_email']}>", '250');
            $this->comando($socket, "RCPT TO:<{$paraEmail}>", '250');
            $this->comando($socket, 'DATA', '354');

            $cabeceras = [
                'From: ' . $this->codificarCabecera($cfg['from_name']) . " <{$cfg['from_email']}>",
                'To: ' . $this->codificarCabecera($paraNombre) . " <{$paraEmail}>",
                'Subject: ' . $this->codificarCabecera($asunto),
                'MIME-Version: 1.0',
                'Content-Type: text/html; charset=UTF-8',
                'Content-Transfer-Encoding: 8bit',
            ];
            // Escapa las líneas que empiecen por "." para no truncar el mensaje (RFC 5321)
            $cuerpoEscapado = preg_replace('/^\./m', '..', $cuerpoHtml);
            $mensaje = implode("\r\n", $cabeceras) . "\r\n\r\n" . $cuerpoEscapado . "\r\n.";
            $this->comando($socket, $mensaje, '250');

            $this->comando($socket, 'QUIT', '221');
            return true;
        } catch (RuntimeException $e) {
            error_log('Mailer: ' . $e->getMessage());
            return false;
        } finally {
            fclose($socket);
        }
    }

    private function codificarCabecera(string $texto): string
    {
        return '=?UTF-8?B?' . base64_encode($texto) . '?=';
    }

    /** Envía un comando y valida que la respuesta empiece por el código esperado */
    private function comando($socket, string $linea, string $codigoEsperado): string
    {
        fwrite($socket, $linea . "\r\n");
        $respuesta = $this->leer($socket);
        if (!str_starts_with($respuesta, $codigoEsperado)) {
            throw new RuntimeException("Respuesta SMTP inesperada (se esperaba {$codigoEsperado}): {$respuesta}");
        }
        return $respuesta;
    }

    private function leer($socket): string
    {
        $respuesta = '';
        while (($linea = fgets($socket, 515)) !== false) {
            $respuesta .= $linea;
            // La última línea de una respuesta multilínea tiene un espacio tras el código, no un guion
            if (isset($linea[3]) && $linea[3] === ' ') {
                break;
            }
        }
        return $respuesta;
    }
}