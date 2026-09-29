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

            self::$conexao = new PDO($dsn, Config::get('db.usuario'), Config::get('db.senha'), self::opcoes());

            // Datas no horário de Brasília, mesmo quando o servidor do banco está em UTC (caso do Aiven)
            self::$conexao->prepare("SET time_zone = '-03:00'")->execute();
        }

        return self::$conexao;
    }

    /** Opções do PDO (também usadas pelo database/instalar.php). */
    public static function opcoes(): array
    {
        $opcoes = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        // Conexão criptografada (SSL), exigida por bancos na nuvem como o Aiven.
        // O certificado da autoridade (CA) do banco fica em config/ca.pem.
        if (Config::get('db.ssl', false)) {
            $certificado = dirname(__DIR__, 2) . '/config/ca.pem';

            if (!is_file($certificado)) {
                throw new \RuntimeException('DB_SSL=true, mas o arquivo config/ca.pem não foi encontrado. Baixe o certificado CA no painel do Aiven.');
            }

            // No PHP 8.4+ a constante se chama Pdo\Mysql::ATTR_SSL_CA
            // (a antiga PDO::MYSQL_ATTR_SSL_CA ficou obsoleta no PHP 8.5, usado pela Vercel).
            $chaveCa = defined('Pdo\Mysql::ATTR_SSL_CA') ? \Pdo\Mysql::ATTR_SSL_CA : PDO::MYSQL_ATTR_SSL_CA;
            $opcoes[$chaveCa] = $certificado;
        }

        return $opcoes;
    }
}
