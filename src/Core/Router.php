<?php

namespace App\Core;

/**
 * Roteador: associa (método HTTP + caminho) a um método de Controller.
 *
 *   $router->get('/itens/{id}', [ItemController::class, 'show']);
 *
 * O {id} só aceita números; o {arquivo} aceita nomes como "a1b2c3.jpg".
 * O valor capturado é passado como argumento para o método do Controller.
 */
final class Router
{
    /** Lista de rotas: cada uma tem 'metodo', 'padrao' (regex) e 'acao' ([Controller, método]). */
    private array $rotas = [];

    public function get(string $caminho, array $acao): void
    {
        $this->adicionar('GET', $caminho, $acao);
    }

    public function post(string $caminho, array $acao): void
    {
        $this->adicionar('POST', $caminho, $acao);
    }

    private function adicionar(string $metodo, string $caminho, array $acao): void
    {
        // /itens/{id}/editar  ->  #^/itens/(\d{1,9})/editar$#   (até 9 dígitos, cabe num INT)
        $padrao = str_replace('{id}', '(\d{1,9})', $caminho);
        // /fotos/{arquivo}     ->  #^/fotos/([a-z0-9]+\.[a-z]+)$#
        $padrao = str_replace('{arquivo}', '([a-z0-9]+\.[a-z]+)', $padrao);

        $this->rotas[] = [
            'metodo' => $metodo,
            'padrao' => '#^' . $padrao . '$#',
            'acao'   => $acao,
        ];
    }

    public function despachar(string $metodo, string $caminho): void
    {
        $caminhoExiste = false;

        foreach ($this->rotas as $rota) {
            if (!preg_match($rota['padrao'], $caminho, $parametros)) {
                continue;
            }

            $caminhoExiste = true;

            if ($rota['metodo'] !== $metodo) {
                continue;
            }

            $classe = $rota['acao'][0];   // ex.: ItemController::class
            $acao   = $rota['acao'][1];   // ex.: 'show'
            $controller = new $classe();

            // $parametros[0] é a URL inteira; $parametros[1] é o que casou com o {id} ou {arquivo}.
            // Ele chega como texto ("5"); como o método pede int $id, o PHP converte para 5.
            if (isset($parametros[1])) {
                $controller->$acao($parametros[1]);
            } else {
                $controller->$acao();
            }
            return;
        }

        if ($caminhoExiste) {
            throw new HttpException('Método não permitido.', 405);
        }

        throw HttpException::naoEncontrado();
    }
}
