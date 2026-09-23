<?php

namespace App\Core;

/**
 * Roteador: associa (método HTTP + caminho) a um método de Controller.
 *
 *   $router->get('/itens/{id}', [ItemController::class, 'show']);
 *
 * Parâmetros entre chaves viram argumentos inteiros do método.
 */
final class Router
{
    /** @var array<int, array{metodo: string, padrao: string, acao: array{0: class-string, 1: string}}> */
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

            array_shift($parametros); // remove o "match completo"
            [$classe, $acao] = $rota['acao'];

            $controller = new $classe();
            $controller->$acao(...array_map('intval', $parametros));
            return;
        }

        if ($caminhoExiste) {
            throw new HttpException('Método não permitido.', 405);
        }

        throw HttpException::naoEncontrado();
    }
}
