<?php

namespace App\Model\DAO;

/**
 * Guarda o conteúdo das fotos na tabela `fotos`.
 * (Na Vercel o disco é somente leitura, então a imagem não pode ir para uma pasta.)
 */
class FotoDAO extends DAO
{
    public function salvar(string $nome, string $tipo, string $dados): void
    {
        $stmt = $this->pdo->prepare('INSERT INTO fotos (nome, tipo, dados) VALUES (:nome, :tipo, :dados)');
        $stmt->execute(['nome' => $nome, 'tipo' => $tipo, 'dados' => $dados]);
    }

    /** @return array{tipo: string, dados: string}|null */
    public function buscar(string $nome): ?array
    {
        $stmt = $this->pdo->prepare('SELECT tipo, dados FROM fotos WHERE nome = :nome');
        $stmt->execute(['nome' => $nome]);
        $linha = $stmt->fetch();

        return $linha ?: null;
    }

    public function remover(string $nome): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM fotos WHERE nome = :nome');
        $stmt->execute(['nome' => $nome]);
    }
}
