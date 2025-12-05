<?php

namespace App\Utils;

use \Firebase\JWT\JWT;

class Auth
{
    private static $secret_key;
    private static $encrypt = ['HS256'];

    public static function generarToken($usuario)
    {
        // 1. Obtener la clave del .env
        self::$secret_key = getenv('JWT_SECRET');

        $ahora = time();
        $vence = $ahora + (60 * 60 * 24);

        // 2. Crear el "Payload" (La información dentro del token)
        $payload = [
            'creacion' => $ahora,
            'expiracion' => $vence,
            'data' => [
                'id' => $usuario->usuario_id,
                'nombre' => $usuario->usuario_nombre, // Ya viene desencriptado del login
                'correo' => $usuario->usuario_correo,
                'rol' => $usuario->rol_id
            ]
        ];

        return JWT::encode($payload, self::$secret_key);
    }


    // Si está vencido o es falso, lanza una excepción.
    public static function verificarToken($token)
    {
        if (empty(self::$secret_key)) {
            self::$secret_key = getenv('JWT_SECRET');
        }

        // Decodifica el token. Si falla (expirado/firma mala), la librería lanza Exception
        // array('HS256') es el algoritmo permitido
        return JWT::decode($token, self::$secret_key, self::$encrypt);
    }
}