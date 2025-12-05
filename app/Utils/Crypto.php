<?php

namespace App\Utils;

use Exception;

class Crypto
{
    private static $method = 'AES-256-CBC';

    // Obtiene la clave de encriptación desde la variable de entorno

    private static function getKey()
    {
        $secret = getenv('APP_SECRET_KEY');
        
        if (!$secret) {
            throw new Exception('La variable APP_SECRET_KEY no está definida en el .env');
        }
        // Retornamos el hash binario (raw) de 32 bytes
        return hash('sha256', $secret, true);
    }

    public static function encriptar($texto)
    {
        if (empty($texto)) return null;

        // 1. Generar un IV (Vector de Inicialización) aleatorio y seguro
        $ivSize = openssl_cipher_iv_length(self::$method);
        $iv = openssl_random_pseudo_bytes($ivSize);

        // 2. Encriptar el texto usando la llave y el IV
        $encrypted = openssl_encrypt($texto, self::$method, self::getKey(), 0, $iv);

        // 3. Concatenamos el IV + El Texto Encriptado y lo convertimos a Base64
        // Guardamos el IV para poder desencriptar después.
        return base64_encode($iv . $encrypted);
    }
        
    public static function desencriptar($textoCifrado)
    {
        if (empty($textoCifrado)) return null;

        // 1. Decodificar de Base64 a binario
        $data = base64_decode($textoCifrado);

        // 2. Calcular el tamaño del IV para saber dónde cortar
        $ivSize = openssl_cipher_iv_length(self::$method);

        // 3. Extraer el IV (la primera parte del string)
        $iv = substr($data, 0, $ivSize);

        // 4. Extraer el mensaje cifrado real (el resto del string)
        $encryptedText = substr($data, $ivSize);

        // 5. Desencriptar
        return openssl_decrypt($encryptedText, self::$method, self::getKey(), 0, $iv);
    }
}