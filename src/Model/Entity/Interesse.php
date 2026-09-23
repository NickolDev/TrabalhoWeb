<?php

namespace App\Model\Entity;

use DateTimeImmutable;

/** Registro de que um usuário tem interesse em um item. */
class Interesse
{
    private ?int $id;
    private int $itemId;
    private int $usuarioId;
    private DateTimeImmutable $criadoEm;

    // Dados de exibição (JOIN com usuarios)
    private ?string $nomeUsuario = null;
    private ?string $emailUsuario = null;

    public function __construct(?int $id, int $itemId, int $usuarioId, ?DateTimeImmutable $criadoEm = null)
    {
        $this->id = $id;
        $this->itemId = $itemId;
        $this->usuarioId = $usuarioId;
        $this->criadoEm = $criadoEm ?? new DateTimeImmutable();
    }

    public static function deLinha(array $linha): self
    {
        $interesse = new self(
            (int) $linha['id'],
            (int) $linha['item_id'],
            (int) $linha['usuario_id'],
            new DateTimeImmutable($linha['criado_em'])
        );

        $interesse->nomeUsuario = $linha['usuario_nome'] ?? null;
        $interesse->emailUsuario = $linha['usuario_email'] ?? null;

        return $interesse;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getItemId(): int
    {
        return $this->itemId;
    }

    public function getUsuarioId(): int
    {
        return $this->usuarioId;
    }

    public function getCriadoEm(): DateTimeImmutable
    {
        return $this->criadoEm;
    }

    public function getNomeUsuario(): ?string
    {
        return $this->nomeUsuario;
    }

    public function getEmailUsuario(): ?string
    {
        return $this->emailUsuario;
    }
}
