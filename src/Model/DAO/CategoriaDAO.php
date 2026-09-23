<?php

namespace App\Model\DAO;

use App\Model\Entity\Categoria;

class CategoriaDAO extends DAO
{
    /** @return Categoria[] */
    public function listar(): array
    {
        $stmt = $this->pdo->prepare('SELECT id, nome FROM categorias ORDER BY nome');
        $stmt->execute();

        return array_map([Categoria::class, 'deLinha'], $stmt->fetchAll());
    }

    public function buscarPorId(int $id): ?Categoria
    {
        $stmt = $this->pdo->prepare('SELECT id, nome FROM categorias WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $linha = $stmt->fetch();

        return $linha ? Categoria::deLinha($linha) : null;
    }

    public function existe(int $id): bool
    {
        return $this->buscarPorId($id) !== null;
    }
}
