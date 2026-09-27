<?php

namespace App\Core;

/**
 * Classe base de todos os Controllers.
 * Concentra o que se repete: renderizar view, redirecionar,
 * exigir login, validar CSRF e ler dados do POST.
 */
abstract class Controller
{
    private ?View $view = null;

    protected function render(string $template, array $dados = []): void
    {
        $this->view ??= new View();
        echo $this->view->render($template, $dados);
    }

    protected function redirecionar(string $caminho): void
    {
        header('Location: ' . Url::para($caminho));
        exit;
    }

    /** Volta para o formulário mantendo o que o usuário digitou e mostrando o erro. */
    protected function voltarComErro(string $caminho, string $mensagem, array $antigos = []): void
    {
        Session::flash('erro', $mensagem);
        Session::guardarAntigos($antigos);
        $this->redirecionar($caminho);
    }

    /** Garante que há usuário logado e devolve o id dele. */
    protected function exigirLogin(): int
    {
        $id = Session::usuarioId();

        if ($id === null) {
            Session::flash('aviso', 'Faça login para continuar.');
            $this->redirecionar('/login');
        }

        return $id;
    }

    /** Todo POST precisa trazer o token CSRF válido. */
    protected function validarCsrf(): void
    {
        // Se o formulário passar do post_max_size do php.ini, o PHP descarta todo o $_POST
        // (inclusive o token). Nesse caso a mensagem certa é "arquivo grande demais".
        if (empty($_POST) && !empty($_SERVER['CONTENT_LENGTH'])) {
            throw HttpException::requisicaoInvalida('O arquivo enviado é grande demais. Envie uma foto de até 2 MB.');
        }

        if (!Csrf::valido($_POST['_csrf'] ?? null)) {
            throw HttpException::requisicaoInvalida('Sessão expirada ou formulário inválido. Recarregue a página e tente novamente.');
        }
    }

    protected function post(string $campo): string
    {
        $valor = $_POST[$campo] ?? '';
        return is_string($valor) ? trim($valor) : '';
    }

    protected function query(string $campo): string
    {
        $valor = $_GET[$campo] ?? '';
        return is_string($valor) ? trim($valor) : '';
    }
}
