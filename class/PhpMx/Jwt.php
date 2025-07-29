<?php

namespace PhpMx;

/** Classe utilitária para criação e validação de tokens JWT. */
abstract class Jwt
{
    /** Retorna o token JWT  */
    static function on(mixed $payload, ?string $key = null): string
    {
        $header = json_encode([
            "alg" => "HS256",
            "typ" => "JWT"
        ]);

        $key = $key ?? env('JWT_KEY');

        $payload = json_encode($payload);

        $header_payload = self::base64url_encode($header) . '.' . self::base64url_encode($payload);

        $signature = hash_hmac('sha256', $header_payload, $key, true);

        return self::base64url_encode($header) . '.' . self::base64url_encode($payload) . '.' . self::base64url_encode($signature);
    }

    /** Retorna o token conteúdo de um token JWT */
    static function off(mixed $token, ?string $key = null): mixed
    {
        if (!is_stringable($token))
            return false;

        $key = $key ?? env('JWT_KEY');

        $token = explode('.', $token . '...');
        $payload = self::base64_decode_url($token[1]);

        $signature = self::base64_decode_url($token[2]);

        $header_payload = $token[0] . '.' . $token[1];

        if (hash_hmac('sha256', $header_payload, $key, true) !== $signature)
            return false;

        return json_decode($payload, true);
    }

    /** Verifica se uma variavel é um token JWT válido */
    static function check(mixed $var, ?string $key = null)
    {
        if (is_string($var)) return boolval(self::off($var, $key));
        return false;
    }

    protected static function base64url_encode($data)
    {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
    }

    protected static function base64_decode_url($string)
    {
        return base64_decode(str_replace(['-', '_'], ['+', '/'], $string));
    }
}
