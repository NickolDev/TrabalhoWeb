<?php

namespace App\Core;

/**
 * Gera URLs respeitando a pasta onde o projeto está instalado.
 *
 * Funciona tanto no XAMPP (http://localhost/bazar-universitario/)
 * quanto na VM com DocumentRoot apontando para public/ (http://IP/).
 */
final class Url
{
    private static string $base = '';

    /**
     * Descobre o prefixo da aplicação a partir do SCRIPT_NAME e da URI.
     * Retorna o caminho "limpo" da requisição, ex.: /itens/5
     */
    public static function resolverCaminho(string $scriptName, string $requestUri): string
    {
        $caminho = parse_url($requestUri, PHP_URL_PATH) ?: '/';
        $base    = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');

        // Acesso direto a /projeto/public/... ou DocumentRoot = public
        if ($base !== '' && str_starts_with($caminho, $base)) {
            self::$base = $base;
        } elseif (str_ends_with($base, '/public') && str_starts_with($caminho, substr($base, 0, -7))) {
            // XAMPP: /projeto/... é reescrito para /projeto/public/... pelo .htaccess da raiz
            self::$base = substr($base, 0, -7);
        } else {
            self::$base = '';
        }

        $caminho = substr($caminho, strlen(self::$base));
        $caminho = '/' . trim((string) $caminho, '/');

        return $caminho === '/index.php' ? '/' : $caminho;
    }

    public static function para(string $caminho = '/'): string
    {
        return self::$base . '/' . ltrim($caminho, '/');
    }
}
