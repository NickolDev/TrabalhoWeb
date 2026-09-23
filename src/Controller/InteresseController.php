<?php

namespace App\Controller;

use App\Core\Controller;
use App\Core\HttpException;
use App\Core\Session;
use App\Model\DAO\InteresseDAO;
use App\Model\DAO\ItemDAO;
use App\Model\Entity\Interesse;

/** Botão "Tenho interesse": vincula usuário + item. */
class InteresseController extends Controller
{
    private InteresseDAO $interesses;

    public function __construct()
    {
        $this->interesses = new InteresseDAO();
    }

    public function registrar(int $id): void
    {
        $usuarioId = $this->exigirLogin();
        $this->validarCsrf();

        $item = (new ItemDAO())->buscarPorId($id);

        if ($item === null) {
            throw HttpException::naoEncontrado('Item não encontrado.');
        }

        if ($item->pertenceA($usuarioId)) {
            Session::flash('erro', 'Você não pode demonstrar interesse no seu próprio item.');
        } elseif (!$item->estaDisponivel()) {
            Session::flash('erro', 'Este item não está mais disponível.');
        } elseif ($this->interesses->existe($id, $usuarioId)) {
            Session::flash('aviso', 'Você já demonstrou interesse neste item.');
        } else {
            $this->interesses->registrar(new Interesse(null, $id, $usuarioId));
            Session::flash('sucesso', 'Interesse registrado! O dono do item verá seu nome e e-mail para entrar em contato.');
        }

        $this->redirecionar('/itens/' . $id);
    }

    public function remover(int $id): void
    {
        $usuarioId = $this->exigirLogin();
        $this->validarCsrf();

        $this->interesses->remover($id, $usuarioId);
        Session::flash('sucesso', 'Interesse cancelado.');

        $this->redirecionar($this->post('voltar') === 'painel' ? '/painel' : '/itens/' . $id);
    }
}
