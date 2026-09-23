<?php

namespace App\Core;

use PDO;

/**
 * Conexão única (Singleton) com o MySQL via PDO.
 *
 * - ERRMODE_EXCEPTION: qualquer erro de SQL vira exceção.
 * - EMULATE_PREPARES = false: o MySQL recebe o SQL e os valores separados
 *   (prepared statements reais), o que impede SQL Injection.
 */
final class Database
{
    private static ?PDO $conexao = null;

    private function __construct()
    {
    }

    public static function conexao(): PDO
    {
        if (self::$conexao === null) {
            $dsn = sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=%s',
                Config::get('db.host'),
                Config::get('db.porta', 3306),
                Config::get('db.banco'),
                Config::get('db.charset', 'utf8mb4')
            );

            self::$conexao = new PDO($dsn, Config::get('db.usuario'), Config::get('db.senha'), [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        }

        return self::$conexao;
    }
}
