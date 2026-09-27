<?php

namespace App\Model\DAO;

use App\Model\Entity\Interesse;
use App\Model\Entity\Item;

class InteresseDAO extends DAO
{
    public function existe(int $itemId, int $usuarioId): bool
    {
        $stmt = $this->pdo->prepare(
            'SELECT 1 FROM interesses WHERE item_id = :item_id AND usuario_id = :usuario_id'
        );
        $stmt->execute(['item_id' => $itemId, 'usuario_id' => $usuarioId]);

        return (bool) $stmt->fetchColumn();
    }

    public function registrar(Interesse $interesse): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO interesses (item_id, usuario_id) VALUES (:item_id, :usuario_id)'
        );
        $stmt->execute([
            'item_id'    => $interesse->getItemId(),
            'usuario_id' => $interesse->getUsuarioId(),
        ]);
    }

    public function remover(int $itemId, int $usuarioId): void
    {
        $stmt = $this->pdo->prepare(
            'DELETE FROM interesses WHERE item_id = :item_id AND usuario_id = :usuario_id'
        );
        $stmt->execute(['item_id' => $itemId, 'usuario_id' => $usuarioId]);
    }

    /**
     * Quem demonstrou interesse em um item (visível só para o dono).
     *
     * @return Interesse[]
     */
    public function listarPorItem(int $itemId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT it.*, u.nome AS usuario_nome, u.email AS usuario_email
               FROM interesses it
               JOIN usuarios u ON u.id = it.usuario_id
              WHERE it.item_id = :item_id
              ORDER BY it.criado_em, it.id'
        );
        $stmt->execute(['item_id' => $itemId]);

        $interesses = [];
        foreach ($stmt->fetchAll() as $linha) {
            $interesses[] = Interesse::deLinha($linha);
        }

        return $interesses;
    }

    public function contarPorItem(int $itemId): int
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM interesses WHERE item_id = :item_id');
        $stmt->execute(['item_id' => $itemId]);

        return (int) $stmt->fetchColumn();
    }

    /** Total de interesses recebidos nos itens de um usuário. */
    public function contarRecebidos(int $donoId): int
    {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*)
               FROM interesses it
               JOIN itens i ON i.id = it.item_id
              WHERE i.usuario_id = :usuario_id'
        );
        $stmt->execute(['usuario_id' => $donoId]);

        return (int) $stmt->fetchColumn();
    }

    /**
     * Itens (de outras pessoas) em que o usuário demonstrou interesse.
     *
     * @return Item[]
     */
    public function itensDeInteresseDo(int $usuarioId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT i.*, c.nome AS categoria_nome, u.nome AS dono_nome
               FROM interesses it
               JOIN itens i      ON i.id = it.item_id
               JOIN categorias c ON c.id = i.categoria_id
               JOIN usuarios u   ON u.id = i.usuario_id
              WHERE it.usuario_id = :usuario_id
              ORDER BY it.criado_em DESC, it.id DESC'
        );
        $stmt->execute(['usuario_id' => $usuarioId]);

        $itens = [];
        foreach ($stmt->fetchAll() as $linha) {
            $itens[] = Item::deLinha($linha);
        }

        return $itens;
    }
}
