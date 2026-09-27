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

        // (int) transforma qualquer coisa estranha ("abc", "1 OR 1=1") em número; 0 = sem filtro
        $categoriaId = (int) $this->parametroGet('categoria');
        if ($categoriaId <= 0) {
            $categoriaId = null;
        }

        $busca = $this->parametroGet('busca');

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
