<?php

namespace App\Model\DAO;

use App\Model\Entity\Usuario;

class UsuarioDAO extends DAO
{
    public function buscarPorId(int $id): ?Usuario
    {
        $stmt = $this->pdo->prepare('SELECT * FROM usuarios WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $linha = $stmt->fetch();

        return $linha ? Usuario::deLinha($linha) : null;
    }

    public function buscarPorEmail(string $email): ?Usuario
    {
        $stmt = $this->pdo->prepare('SELECT * FROM usuarios WHERE email = :email');
        $stmt->execute(['email' => strtolower(trim($email))]);
        $linha = $stmt->fetch();

        return $linha ? Usuario::deLinha($linha) : null;
    }

    public function emailExiste(string $email): bool
    {
        $stmt = $this->pdo->prepare('SELECT 1 FROM usuarios WHERE email = :email');
        $stmt->execute(['email' => strtolower(trim($email))]);

        return (bool) $stmt->fetchColumn();
    }

    public function inserir(Usuario $usuario): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO usuarios (nome, email, senha_hash) VALUES (:nome, :email, :senha_hash)'
        );
        $stmt->execute([
            'nome'       => $usuario->getNome(),
            'email'      => $usuario->getEmail(),
            'senha_hash' => $usuario->getSenhaHash(),
        ]);

        $usuario->setId((int) $this->pdo->lastInsertId());
    }

    public function atualizarSenha(Usuario $usuario): void
    {
        $stmt = $this->pdo->prepare('UPDATE usuarios SET senha_hash = :senha_hash WHERE id = :id');
        $stmt->execute([
            'senha_hash' => $usuario->getSenhaHash(),
            'id'         => $usuario->getId(),
        ]);
    }
}
