<?php

namespace App\Model\Entity;

/** Item oferecido gratuitamente. */
class ItemDoacao extends Item
{
    public const TIPO = 'doacao';

    public function getTipo(): string
    {
        return self::TIPO;
    }

    public function getRotuloTipo(): string
    {
        return 'Doação';
    }

    public function getChamada(): string
    {
        return 'Grátis — é só demonstrar interesse e combinar a retirada.';
    }

    public function getRotuloConcluido(): string
    {
        return 'Doado';
    }
}
