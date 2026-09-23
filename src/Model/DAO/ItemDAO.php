<?php

namespace App\Model\DAO;

use App\Model\Entity\Item;

class ItemDAO extends DAO
{
    /** SELECT base com os nomes da categoria e do dono (JOIN). */
    private const SELECT_COMPLETO = '
        SELECT i.*, c.nome AS categoria_nome, u.nome AS dono_nome
          FROM itens i
          JOIN categorias c ON c.id = i.categoria_id
          JOIN usuarios u   ON u.id = i.usuario_id';

    /**
     * Listagem pública: só itens disponíveis, com filtro opcional
     * por categoria e busca por texto. Os filtros entram como
     * parâmetros nomeados, nunca concatenados.
     *
     * @return Item[]
     */
    public function listarDisponiveis(?int $categoriaId = null, string $busca = ''): array
    {
        $sql = self::SELECT_COMPLETO . ' WHERE i.status = :status';
        $parametros = ['status' => Item::STATUS_DISPONIVEL];

        if ($categoriaId !== null) {
            $sql .= ' AND i.categoria_id = :categoria_id';
            $parametros['categoria_id'] = $categoriaId;
        }

        if ($busca !== '') {
            $sql .= ' AND (i.nome LIKE :busca_nome OR i.descricao LIKE :busca_descricao)';
            $termo = '%' . addcslashes($busca, '%_\\') . '%';
            $parametros['busca_nome'] = $termo;
            $parametros['busca_descricao'] = $termo;
        }

        $sql .= ' ORDER BY i.criado_em DESC, i.id DESC';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($parametros);

        return array_map([Item::class, 'deLinha'], $stmt->fetchAll());
    }

    public function buscarPorId(int $id): ?Item
    {
        $stmt = $this->pdo->prepare(self::SELECT_COMPLETO . ' WHERE i.id = :id');
        $stmt->execute(['id' => $id]);
        $linha = $stmt->fetch();

        return $linha ? Item::deLinha($linha) : null;
    }

    /** @return Item[] */
    public function listarPorUsuario(int $usuarioId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT i.*, c.nome AS categoria_nome, u.nome AS dono_nome,
                    (SELECT COUNT(*) FROM interesses x WHERE x.item_id = i.id) AS total_interesses
               FROM itens i
               JOIN categorias c ON c.id = i.categoria_id
               JOIN usuarios u   ON u.id = i.usuario_id
              WHERE i.usuario_id = :usuario_id
              ORDER BY i.status, i.criado_em DESC, i.id DESC'
        );
        $stmt->execute(['usuario_id' => $usuarioId]);

        return array_map([Item::class, 'deLinha'], $stmt->fetchAll());
    }

    public function inserir(Item $item): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO itens (usuario_id, categoria_id, nome, descricao, tipo, status, foto)
             VALUES (:usuario_id, :categoria_id, :nome, :descricao, :tipo, :status, :foto)'
        );
        $stmt->execute([
            'usuario_id'   => $item->getUsuarioId(),
            'categoria_id' => $item->getCategoriaId(),
            'nome'         => $item->getNome(),
            'descricao'    => $item->getDescricao(),
            'tipo'         => $item->getTipo(),
            'status'       => $item->getStatus(),
            'foto'         => $item->getFoto(),
        ]);

        $item->setId((int) $this->pdo->lastInsertId());
    }

    /**
     * Atualiza o item. O "AND usuario_id" garante, também no banco,
     * que ninguém altera item de outra pessoa.
     */
    public function atualizar(Item $item): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE itens
                SET categoria_id = :categoria_id, nome = :nome, descricao = :descricao,
                    tipo = :tipo, status = :status, foto = :foto
              WHERE id = :id AND usuario_id = :usuario_id'
        );
        $stmt->execute([
            'categoria_id' => $item->getCategoriaId(),
            'nome'         => $item->getNome(),
            'descricao'    => $item->getDescricao(),
            'tipo'         => $item->getTipo(),
            'status'       => $item->getStatus(),
            'foto'         => $item->getFoto(),
            'id'           => $item->getId(),
            'usuario_id'   => $item->getUsuarioId(),
        ]);
    }

    public function remover(int $id, int $usuarioId): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM itens WHERE id = :id AND usuario_id = :usuario_id');
        $stmt->execute(['id' => $id, 'usuario_id' => $usuarioId]);

        return $stmt->rowCount() > 0;
    }

    /** Números para o painel do usuário (bônus). */
    public function estatisticasDoUsuario(int $usuarioId): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) AS total,
                    COALESCE(SUM(status = 'disponivel'), 0) AS disponiveis,
                    COALESCE(SUM(status = 'concluido'), 0)  AS concluidos,
                    COALESCE(SUM(tipo = 'doacao'), 0)       AS doacoes,
                    COALESCE(SUM(tipo = 'troca'), 0)        AS trocas
               FROM itens
              WHERE usuario_id = :usuario_id"
        );
        $stmt->execute(['usuario_id' => $usuarioId]);

        return array_map('intval', $stmt->fetch());
    }
}
