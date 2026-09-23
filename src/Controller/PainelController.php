<?php

namespace App\Controller;

use App\Core\Controller;
use App\Core\Session;
use App\Model\DAO\InteresseDAO;
use App\Model\DAO\ItemDAO;

/** Bônus: painel do usuário logado com seus números e seus itens. */
class PainelController extends Controller
{
    public function index(): void
    {
        $usuarioId = $this->exigirLogin();

        $itemDAO = new ItemDAO();
        $interesseDAO = new InteresseDAO();

        $this->render('painel/index', [
            'titulo'              => 'Meu painel',
            'nome'                => Session::usuarioNome(),
            'estatisticas'        => $itemDAO->estatisticasDoUsuario($usuarioId),
            'interessesRecebidos' => $interesseDAO->contarRecebidos($usuarioId),
            'meusItens'           => $itemDAO->listarPorUsuario($usuarioId),
            'meusInteresses'      => $interesseDAO->itensDeInteresseDo($usuarioId),
        ]);
    }
}
