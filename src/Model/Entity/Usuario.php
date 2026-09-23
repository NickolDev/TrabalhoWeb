<?php

namespace App\Model\Entity;

use DateTimeImmutable;
use InvalidArgumentException;

/**
 * Usuário do bazar. A senha nunca é guardada em texto puro:
 * só o hash gerado por password_hash() fica no objeto e no banco.
 */
class Usuario
{
    private ?int $id;
    private string $nome;
    private string $email;
    private string $senhaHash;
    private DateTimeImmutable $criadoEm;

    public function __construct(
        ?int $id,
        string $nome,
        string $email,
        string $senhaHash,
        ?DateTimeImmutable $criadoEm = null
    ) {
        $this->id = $id;
        $this->setNome($nome);
        $this->setEmail($email);
        $this->senhaHash = $senhaHash;
        $this->criadoEm = $criadoEm ?? new DateTimeImmutable();
    }

    /** Cria um novo usuário a partir da senha digitada, já gerando o hash. */
    public static function registrar(string $nome, string $email, string $senhaPura): self
    {
        if (mb_strlen($senhaPura) < 6) {
            throw new InvalidArgumentException('A senha deve ter pelo menos 6 caracteres.');
        }

        return new self(null, $nome, $email, password_hash($senhaPura, PASSWORD_DEFAULT));
    }

    public static function deLinha(array $linha): self
    {
        return new self(
            (int) $linha['id'],
            $linha['nome'],
            $linha['email'],
            $linha['senha_hash'],
            new DateTimeImmutable($linha['criado_em'])
        );
    }

    public function verificarSenha(string $senhaPura): bool
    {
        return password_verify($senhaPura, $this->senhaHash);
    }

    public function precisaRehash(): bool
    {
        return password_needs_rehash($this->senhaHash, PASSWORD_DEFAULT);
    }

    // ---------- Getters ----------

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNome(): string
    {
        return $this->nome;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getSenhaHash(): string
    {
        return $this->senhaHash;
    }

    public function getCriadoEm(): DateTimeImmutable
    {
        return $this->criadoEm;
    }

    // ---------- Setters com validação (encapsulamento) ----------

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function setNome(string $nome): void
    {
        $nome = trim($nome);

        if ($nome === '' || mb_strlen($nome) > 100) {
            throw new InvalidArgumentException('Informe um nome com até 100 caracteres.');
        }

        $this->nome = $nome;
    }

    public function setEmail(string $email): void
    {
        $email = mb_strtolower(trim($email));

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 150) {
            throw new InvalidArgumentException('Informe um e-mail válido.');
        }

        $this->email = $email;
    }

    public function definirSenha(string $senhaPura): void
    {
        $this->senhaHash = password_hash($senhaPura, PASSWORD_DEFAULT);
    }
}
