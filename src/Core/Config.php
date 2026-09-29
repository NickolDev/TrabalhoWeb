<?php

namespace App\Core;

/**
 * Acesso centralizado às configurações de config/config.php.
 * Uso: Config::get('db.host')
 */
final class Config
{
    private static array $valores = [];

    public static function carregar(string $arquivo): void
    {
        if (!is_file($arquivo)) {
            http_response_code(500);
            exit('Arquivo config/config.php não encontrado.');
        }

        self::$valores = require $arquivo;
    }

    public static function get(string $chave, mixed $padrao = null): mixed
    {
        $valor = self::$valores;

        foreach (explode('.', $chave) as $parte) {
            if (!is_array($valor) || !array_key_exists($parte, $valor)) {
                return $padrao;
            }
            $valor = $valor[$parte];
        }

        return $valor;
    }
}
