<?php

namespace App\Core;

use PDO;
use SessionHandlerInterface;

/**
 * Guarda as sessões do PHP no MySQL (tabela `sessoes`) em vez de arquivos.
 *
 * Por quê? Na Vercel cada acesso pode ser atendido por um servidor diferente
 * e o disco não é compartilhado: com sessão em arquivo, o usuário seria
 * deslogado do nada. Com a sessão no banco, todos os servidores enxergam a mesma.
 *
 * O PHP chama estes métodos sozinho depois do session_set_save_handler():
 * read() no session_start() e write() no fim da requisição.
 */
class SessaoNoBanco implements SessionHandlerInterface
{
    public function __construct(private PDO $pdo)
    {
    }

    public function open(string $path, string $name): bool
    {
        return true; // a conexão já está aberta
    }

    public function close(): bool
    {
        return true;
    }

    /** Devolve os dados da sessão (texto serializado pelo PHP) ou '' se não existir. */
    public function read(string $id): string|false
    {
        $stmt = $this->pdo->prepare('SELECT dados FROM sessoes WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $dados = $stmt->fetchColumn();

        return $dados === false ? '' : $dados;
    }

    /** Grava (ou substitui) os dados da sessão. */
    public function write(string $id, string $data): bool
    {
        $stmt = $this->pdo->prepare(
            'REPLACE INTO sessoes (id, dados, atualizado_em) VALUES (:id, :dados, :agora)'
        );

        return $stmt->execute(['id' => $id, 'dados' => $data, 'agora' => time()]);
    }

    /** Chamado no logout e no session_regenerate_id(true). */
    public function destroy(string $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM sessoes WHERE id = :id');
        $stmt->execute(['id' => $id]);

        return true;
    }

    /** "Coleta de lixo": apaga sessões paradas há mais tempo que o limite. */
    public function gc(int $max_lifetime): int|false
    {
        $stmt = $this->pdo->prepare('DELETE FROM sessoes WHERE atualizado_em < :limite');
        $stmt->execute(['limite' => time() - $max_lifetime]);

        return $stmt->rowCount();
    }
}
