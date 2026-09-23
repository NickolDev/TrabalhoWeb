<?php

namespace App\Core;

/**
 * Exceção para erros HTTP (404 Não encontrado, 403 Proibido etc.).
 * É capturada no front controller, que mostra a página de erro adequada.
 */
class HttpException extends \RuntimeException
{
    public static function naoEncontrado(string $mensagem = 'Página não encontrada.'): self
    {
        return new self($mensagem, 404);
    }

    public static function proibido(string $mensagem = 'Você não tem permissão para isso.'): self
    {
        return new self($mensagem, 403);
    }

    public static function requisicaoInvalida(string $mensagem = 'Requisição inválida.'): self
    {
        return new self($mensagem, 400);
    }
}
