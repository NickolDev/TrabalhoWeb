<?php

namespace App\Core;

/**
 * Proteção contra CSRF: todo formulário POST envia um token secreto
 * guardado na sessão. Um site de terceiros não consegue adivinhá-lo.
 */
final class Csrf
{
    private const CHAVE = '_csrf';

    public static function token(): string
    {
        $token = Session::get(self::CHAVE);

        if (!is_string($token)) {
            $token = bin2hex(random_bytes(32));
            Session::set(self::CHAVE, $token);
        }

        return $token;
    }

    public static function valido(?string $tokenRecebido): bool
    {
        $token = Session::get(self::CHAVE);

        return is_string($token) && is_string($tokenRecebido) && hash_equals($token, $tokenRecebido);
    }
}
