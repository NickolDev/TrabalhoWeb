<?php

namespace App\Core;

/**
 * Renderiza os templates de src/View dentro do layout.
 *
 * Dentro de qualquer template, $this é esta classe, então dá para usar:
 *   <?= $this->e($item->getNome()) ?>   -> escapa com htmlspecialchars (anti-XSS)
 *   <?= $this->url('/itens/novo') ?>     -> gera a URL correta
 *   <?= $this->csrf() ?>                 -> campo oculto com token CSRF
 */
final class View
{
    private string $diretorio;

    public function __construct()
    {
        $this->diretorio = dirname(__DIR__) . '/View';
    }

    public function render(string $template, array $dados = [], string $layout = 'layout/principal'): string
    {
        $conteudo = $this->incluir($template, $dados);

        if ($layout === '') {
            return $conteudo;
        }

        return $this->incluir($layout, $dados + ['conteudo' => $conteudo]);
    }

    /** Renderiza um trecho reaproveitável (ex.: card de item). */
    public function parcial(string $template, array $dados = []): string
    {
        return $this->incluir($template, $dados);
    }

    private function incluir(string $template, array $dados): string
    {
        $arquivo = $this->diretorio . '/' . $template . '.php';

        if (!is_file($arquivo)) {
            throw new \RuntimeException("View não encontrada: {$template}");
        }

        extract($dados, EXTR_SKIP);
        ob_start();
        require $arquivo;
        return (string) ob_get_clean();
    }

    // ---------- Helpers usados nos templates ----------

    /** Escapa qualquer dado vindo do usuário antes de exibir (prevenção de XSS). */
    public function e(mixed $valor): string
    {
        return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
    }

    public function url(string $caminho = '/'): string
    {
        return $this->e(Url::para($caminho));
    }

    public function csrf(): string
    {
        return '<input type="hidden" name="_csrf" value="' . $this->e(Csrf::token()) . '">';
    }

    public function usuarioLogado(): bool
    {
        return Session::logado();
    }

    public function usuarioId(): ?int
    {
        return Session::usuarioId();
    }

    public function usuarioNome(): string
    {
        return (string) Session::usuarioNome();
    }

    public function mensagens(): array
    {
        return Session::consumirFlash();
    }

    public function data(\DateTimeInterface $data): string
    {
        return $data->format('d/m/Y');
    }

    public function nomeApp(): string
    {
        return (string) Config::get('app.nome', 'Bazar Universitário');
    }
}
