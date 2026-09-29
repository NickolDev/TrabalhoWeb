<?php

/**
 * Cria o banco e as tabelas rodando o schema.sql (e, se pedir, os dados de exemplo).
 * Usa as mesmas configurações da aplicação (config/config.php).
 *
 * Uso (no terminal, na pasta do projeto):
 *   php database/instalar.php              -> só as tabelas
 *   php database/instalar.php --exemplo    -> tabelas + dados de exemplo
 *
 * Para instalar no Aiven, defina antes as variáveis DB_HOST, DB_PORT, DB_USER,
 * DB_PASS e DB_SSL=true (veja docs/DEPLOY.md).
 *
 * Os comandos daqui são fixos, escritos nos arquivos .sql, sem nenhum dado
 * vindo de usuário, por isso usam exec() direto.
 */

use App\Core\Config;
use App\Core\Database;

if (PHP_SAPI !== 'cli') {
    exit('Este script só roda pelo terminal.');
}

$raiz = dirname(__DIR__);
require $raiz . '/src/autoload.php';
Config::carregar($raiz . '/config/config.php');

$arquivos = [$raiz . '/database/schema.sql'];
if (in_array('--exemplo', $argv, true)) {
    $arquivos[] = $raiz . '/database/dados-exemplo.sql';
}

// Conecta no servidor SEM escolher o banco, porque o schema.sql é quem cria o banco
$dsn = sprintf('mysql:host=%s;port=%d;charset=utf8mb4', Config::get('db.host'), Config::get('db.porta'));

try {
    $pdo = new PDO($dsn, Config::get('db.usuario'), Config::get('db.senha'), Database::opcoes());
} catch (PDOException $e) {
    exit('Não foi possível conectar em ' . Config::get('db.host') . ': ' . $e->getMessage() . PHP_EOL);
}

foreach ($arquivos as $arquivo) {
    echo 'Executando ' . basename($arquivo) . '...' . PHP_EOL;

    // Tira as linhas de comentário (--) e separa os comandos pelo ";" no fim da linha
    $linhas = array_filter(
        file($arquivo, FILE_IGNORE_NEW_LINES),
        fn (string $linha): bool => !str_starts_with(trim($linha), '--')
    );
    $comandos = preg_split('/;\s*$/m', implode("\n", $linhas));

    foreach ($comandos as $comando) {
        if (trim($comando) === '') {
            continue;
        }

        try {
            $pdo->exec($comando);
        } catch (PDOException $e) {
            exit('Erro no comando:' . PHP_EOL . trim($comando) . PHP_EOL . PHP_EOL . $e->getMessage() . PHP_EOL);
        }
    }
}

echo 'Pronto! Banco "' . Config::get('db.banco') . '" instalado em ' . Config::get('db.host') . '.' . PHP_EOL;
