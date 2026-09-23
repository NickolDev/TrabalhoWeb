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
            throw new \RuntimeException(
                'Arquivo config/config.php não encontrado. Copie config/config.example.php para config/config.php.'
            );
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
