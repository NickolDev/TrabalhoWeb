<?php

namespace App\Model\Entity;

/** Item oferecido em troca de outro. */
class ItemTroca extends Item
{
    public const TIPO = 'troca';

    public function getTipo(): string
    {
        return self::TIPO;
    }

    public function getRotuloTipo(): string
    {
        return 'Troca';
    }

    public function getChamada(): string
    {
        return 'O dono aceita trocar por outro item — demonstre interesse e faça sua proposta.';
    }

    public function getRotuloConcluido(): string
    {
        return 'Trocado';
    }
}
