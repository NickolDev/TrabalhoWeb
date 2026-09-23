<?php

namespace App\Model\Entity;

use InvalidArgumentException;

class Categoria
{
    private ?int $id;
    private string $nome;

    public function __construct(?int $id, string $nome)
    {
        $this->id = $id;
        $this->setNome($nome);
    }

    public static function deLinha(array $linha): self
    {
        return new self((int) $linha['id'], $linha['nome']);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNome(): string
    {
        return $this->nome;
    }

    public function setNome(string $nome): void
    {
        $nome = trim($nome);

        if ($nome === '' || mb_strlen($nome) > 60) {
            throw new InvalidArgumentException('Nome de categoria inválido.');
        }

        $this->nome = $nome;
    }
}
