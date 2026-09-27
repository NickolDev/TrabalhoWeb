<?php

namespace App\Core;

/**
 * Roteador: associa (método HTTP + caminho) a um método de Controller.
 *
 *   $router->get('/itens/{id}', [ItemController::class, 'show']);
 *
 * O {id} da URL vira um número inteiro passado para o método do Controller.
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
        // /itens/{id}/editar  ->  #^/itens/(\d+)/editar$#
        $padrao = preg_replace('#\{[a-zA-Z_]+\}#', '(\d+)', $caminho);

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

            // $parametros[0] é a URL inteira; $parametros[1] é o que casou com o {id}
            if (isset($parametros[1])) {
                $controller->$acao((int) $parametros[1]);
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
