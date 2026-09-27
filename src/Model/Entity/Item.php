<?php

namespace App\Model\Entity;

use DateTimeImmutable;
use InvalidArgumentException;

/**
 * Item publicado no bazar.
 *
 * É ABSTRATA: não existe "item genérico", só ItemDoacao ou ItemTroca.
 * Cada subclasse define o próprio tipo e seus textos (polimorfismo),
 * e todo o resto (validação, posse, status) é herdado daqui.
 */
abstract class Item
{
    public const STATUS_DISPONIVEL = 'disponivel';
    public const STATUS_CONCLUIDO  = 'concluido';

    private ?int $id;
    private int $usuarioId;
    private int $categoriaId;
    private string $nome;
    private ?string $descricao;
    private string $status;
    private ?string $foto;
    private DateTimeImmutable $criadoEm;

    // Dados "de leitura" vindos de JOIN (para exibição)
    private ?string $nomeCategoria = null;
    private ?string $nomeDono = null;
    private ?int $totalInteresses = null;

    public function __construct(
        ?int $id,
        int $usuarioId,
        int $categoriaId,
        string $nome,
        ?string $descricao,
        string $status = self::STATUS_DISPONIVEL,
        ?string $foto = null,
        ?DateTimeImmutable $criadoEm = null
    ) {
        $this->id = $id;
        $this->usuarioId = $usuarioId;
        $this->setCategoriaId($categoriaId);
        $this->setNome($nome);
        $this->setDescricao($descricao);
        $this->setStatus($status);
        $this->foto = $foto;
        $this->criadoEm = $criadoEm ?? new DateTimeImmutable();
    }

    // ---------- Comportamento que cada tipo define ----------

    /** Valor gravado na coluna ENUM `tipo` ('doacao' ou 'troca'). */
    abstract public function getTipo(): string;

    /** Rótulo exibido no selo do card. */
    abstract public function getRotuloTipo(): string;

    /** Frase curta que explica a proposta do item. */
    abstract public function getChamada(): string;

    /** Texto do selo quando o item já foi concluído ("Doado" / "Trocado"). */
    abstract public function getRotuloConcluido(): string;

    // ---------- Fábrica ----------

    /** Cria a subclasse correta a partir do tipo. */
    public static function fabricar(
        string $tipo,
        ?int $id,
        int $usuarioId,
        int $categoriaId,
        string $nome,
        ?string $descricao,
        string $status = self::STATUS_DISPONIVEL,
        ?string $foto = null,
        ?DateTimeImmutable $criadoEm = null
    ): self {
        return match ($tipo) {
            ItemDoacao::TIPO => new ItemDoacao($id, $usuarioId, $categoriaId, $nome, $descricao, $status, $foto, $criadoEm),
            ItemTroca::TIPO  => new ItemTroca($id, $usuarioId, $categoriaId, $nome, $descricao, $status, $foto, $criadoEm),
            default          => throw new InvalidArgumentException('Escolha se o item é para doação ou troca.'),
        };
    }

    /** Monta o objeto a partir de uma linha do banco (com JOINs opcionais). */
    public static function deLinha(array $linha): self
    {
        $item = self::fabricar(
            $linha['tipo'],
            (int) $linha['id'],
            (int) $linha['usuario_id'],
            (int) $linha['categoria_id'],
            $linha['nome'],
            $linha['descricao'],
            $linha['status'],
            $linha['foto'] ?? null,
            new DateTimeImmutable($linha['criado_em'])
        );

        $item->nomeCategoria = $linha['categoria_nome'] ?? null;
        $item->nomeDono = $linha['dono_nome'] ?? null;
        $item->totalInteresses = isset($linha['total_interesses']) ? (int) $linha['total_interesses'] : null;

        return $item;
    }

    public static function tiposDisponiveis(): array
    {
        return [
            ItemDoacao::TIPO => 'Doação',
            ItemTroca::TIPO  => 'Troca',
        ];
    }

    // ---------- Regras de negócio ----------

    public function pertenceA(?int $usuarioId): bool
    {
        return $usuarioId !== null && $this->usuarioId === $usuarioId;
    }

    public function estaDisponivel(): bool
    {
        return $this->status === self::STATUS_DISPONIVEL;
    }

    public function concluir(): void
    {
        $this->status = self::STATUS_CONCLUIDO;
    }

    public function reabrir(): void
    {
        $this->status = self::STATUS_DISPONIVEL;
    }

    public function getRotuloStatus(): string
    {
        return $this->estaDisponivel() ? 'Disponível' : $this->getRotuloConcluido();
    }

    // ---------- Getters ----------

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsuarioId(): int
    {
        return $this->usuarioId;
    }

    public function getCategoriaId(): int
    {
        return $this->categoriaId;
    }

    public function getNome(): string
    {
        return $this->nome;
    }

    public function getDescricao(): ?string
    {
        return $this->descricao;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getFoto(): ?string
    {
        return $this->foto;
    }

    public function getCriadoEm(): DateTimeImmutable
    {
        return $this->criadoEm;
    }

    public function getNomeCategoria(): ?string
    {
        return $this->nomeCategoria;
    }

    public function getNomeDono(): ?string
    {
        return $this->nomeDono;
    }

    public function getTotalInteresses(): ?int
    {
        return $this->totalInteresses;
    }

    // ---------- Setters com validação ----------

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function setNome(string $nome): void
    {
        $nome = trim($nome);

        if ($nome === '') {
            throw new InvalidArgumentException('Informe o nome do item.');
        }
        // strlen() conta bytes. Letras acentuadas ocupam 2 bytes em UTF-8, então este limite
        // é um pouco mais rígido que o VARCHAR(120) do banco — nunca deixa passar texto maior.
        if (strlen($nome) > 120) {
            throw new InvalidArgumentException('O nome do item deve ter no máximo 120 caracteres.');
        }

        $this->nome = $nome;
    }

    public function setDescricao(?string $descricao): void
    {
        $descricao = $descricao !== null ? trim($descricao) : null;

        if ($descricao !== null && strlen($descricao) > 2000) {
            throw new InvalidArgumentException('A descrição deve ter no máximo 2000 caracteres.');
        }

        $this->descricao = ($descricao === '' ? null : $descricao);
    }

    public function setCategoriaId(int $categoriaId): void
    {
        if ($categoriaId <= 0) {
            throw new InvalidArgumentException('Escolha uma categoria.');
        }

        $this->categoriaId = $categoriaId;
    }

    public function setStatus(string $status): void
    {
        if (!in_array($status, [self::STATUS_DISPONIVEL, self::STATUS_CONCLUIDO], true)) {
            throw new InvalidArgumentException('Status inválido.');
        }

        $this->status = $status;
    }

    public function setFoto(?string $foto): void
    {
        $this->foto = $foto;
    }
}
