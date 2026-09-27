<?php

namespace App\Controller;

use App\Core\Controller;
use App\Model\DAO\CategoriaDAO;
use App\Model\DAO\ItemDAO;

/** Página inicial: listagem pública de itens disponíveis (não exige login). */
class HomeController extends Controller
{
    public function index(): void
    {
        $categorias = (new CategoriaDAO())->listar();

        $categoriaId = filter_var($this->query('categoria'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $categoriaId = $categoriaId === false ? null : $categoriaId;

        $busca = $this->query('busca');

        $itens = (new ItemDAO())->listarDisponiveis($categoriaId, $busca);

        $this->render('home/index', [
            'titulo'            => 'Itens disponíveis',
            'itens'             => $itens,
            'categorias'        => $categorias,
            'categoriaAtualId'  => $categoriaId,
            'busca'             => $busca,
        ]);
    }
}
