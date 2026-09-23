<?php

namespace App\Controller;

use App\Core\Controller;
use App\Core\HttpException;
use App\Core\Session;
use App\Model\DAO\CategoriaDAO;
use App\Model\DAO\InteresseDAO;
use App\Model\DAO\ItemDAO;
use App\Model\Entity\Item;
use App\Model\Service\FotoUpload;
use InvalidArgumentException;

/** Detalhe e CRUD de itens. Criar/editar/remover exigem login e posse do item. */
class ItemController extends Controller
{
    private ItemDAO $itens;
    private CategoriaDAO $categorias;
    private FotoUpload $fotos;

    public function __construct()
    {
        $this->itens = new ItemDAO();
        $this->categorias = new CategoriaDAO();
        $this->fotos = new FotoUpload();
    }

    // ---------------- READ (detalhe) ----------------

    public function show(int $id): void
    {
        $item = $this->itens->buscarPorId($id);

        if ($item === null) {
            throw HttpException::naoEncontrado('Item não encontrado.');
        }

        $usuarioId     = Session::usuarioId();
        $interesseDAO  = new InteresseDAO();
        $ehDono        = $item->pertenceA($usuarioId);

        $this->render('itens/detalhe', [
            'titulo'          => $item->getNome(),
            'item'            => $item,
            'ehDono'          => $ehDono,
            'interesses'      => $ehDono ? $interesseDAO->listarPorItem($id) : [],
            'totalInteresses' => $interesseDAO->contarPorItem($id),
            'jaInteressado'   => $usuarioId !== null && !$ehDono && $interesseDAO->existe($id, $usuarioId),
        ]);
    }

    // ---------------- CREATE ----------------

    public function create(): void
    {
        $this->exigirLogin();

        $this->render('itens/formulario', [
            'titulo'     => 'Cadastrar item',
            'item'       => null,
            'categorias' => $this->categorias->listar(),
            'tipos'      => Item::tiposDisponiveis(),
            'antigos'    => Session::consumirAntigos(),
            'acao'       => '/itens',
        ]);
    }

    public function store(): void
    {
        $usuarioId = $this->exigirLogin();
        $this->validarCsrf();

        $antigos = $this->dadosDoFormulario();
        $foto = null;

        try {
            $item = $this->montarItem(null, $usuarioId, $antigos, Item::STATUS_DISPONIVEL, null);

            $foto = $this->fotos->salvar($_FILES['foto'] ?? null);
            $item->setFoto($foto);

            $this->itens->inserir($item);
        } catch (InvalidArgumentException $e) {
            $this->fotos->remover($foto);
            $this->voltarComErro('/itens/novo', $e->getMessage(), $antigos);
        }

        Session::flash('sucesso', 'Item publicado com sucesso!');
        $this->redirecionar('/itens/' . $item->getId());
    }

    // ---------------- UPDATE ----------------

    public function edit(int $id): void
    {
        $usuarioId = $this->exigirLogin();
        $item = $this->buscarItemDoDono($id, $usuarioId);

        $this->render('itens/formulario', [
            'titulo'     => 'Editar item',
            'item'       => $item,
            'categorias' => $this->categorias->listar(),
            'tipos'      => Item::tiposDisponiveis(),
            'antigos'    => Session::consumirAntigos(),
            'acao'       => '/itens/' . $id . '/editar',
        ]);
    }

    public function update(int $id): void
    {
        $usuarioId = $this->exigirLogin();
        $this->validarCsrf();

        $atual   = $this->buscarItemDoDono($id, $usuarioId);
        $antigos = $this->dadosDoFormulario();
        $novaFoto = null;

        try {
            // Recria o objeto: se o tipo mudou, vira a outra subclasse (Doação <-> Troca)
            $item = $this->montarItem($id, $usuarioId, $antigos, $atual->getStatus(), $atual->getFoto());

            $novaFoto = $this->fotos->salvar($_FILES['foto'] ?? null);

            if ($novaFoto !== null) {
                $item->setFoto($novaFoto);
            } elseif ($this->post('remover_foto') === '1') {
                $item->setFoto(null);
            }

            $this->itens->atualizar($item);
        } catch (InvalidArgumentException $e) {
            $this->fotos->remover($novaFoto);
            $this->voltarComErro('/itens/' . $id . '/editar', $e->getMessage(), $antigos);
        }

        // Apaga do disco a foto antiga, se ela foi trocada ou removida
        if ($atual->getFoto() !== $item->getFoto()) {
            $this->fotos->remover($atual->getFoto());
        }

        Session::flash('sucesso', 'Item atualizado.');
        $this->redirecionar('/itens/' . $id);
    }

    /** Bônus: marcar como "já doado/trocado" (sai da listagem pública) ou reabrir. */
    public function alternarStatus(int $id): void
    {
        $usuarioId = $this->exigirLogin();
        $this->validarCsrf();

        $item = $this->buscarItemDoDono($id, $usuarioId);

        if ($item->estaDisponivel()) {
            $item->concluir();
            $mensagem = 'Item marcado como ' . mb_strtolower($item->getRotuloConcluido()) . '. Ele não aparece mais na listagem pública.';
        } else {
            $item->reabrir();
            $mensagem = 'Item disponível novamente na listagem.';
        }

        $this->itens->atualizar($item);
        Session::flash('sucesso', $mensagem);

        $this->redirecionar($this->post('voltar') === 'painel' ? '/painel' : '/itens/' . $id);
    }

    // ---------------- DELETE ----------------

    public function destroy(int $id): void
    {
        $usuarioId = $this->exigirLogin();
        $this->validarCsrf();

        $item = $this->buscarItemDoDono($id, $usuarioId);

        if ($this->itens->remover($id, $usuarioId)) {
            $this->fotos->remover($item->getFoto());
        }

        Session::flash('sucesso', 'Item "' . $item->getNome() . '" removido.');
        $this->redirecionar('/painel');
    }

    // ---------------- Auxiliares ----------------

    /**
     * Busca o item e garante que pertence ao usuário logado.
     * Requisito 6: o usuário só pode editar ou remover os PRÓPRIOS itens.
     */
    private function buscarItemDoDono(int $id, int $usuarioId): Item
    {
        $item = $this->itens->buscarPorId($id);

        if ($item === null) {
            throw HttpException::naoEncontrado('Item não encontrado.');
        }

        if (!$item->pertenceA($usuarioId)) {
            throw HttpException::proibido('Você só pode alterar os itens que você cadastrou.');
        }

        return $item;
    }

    private function dadosDoFormulario(): array
    {
        return [
            'nome'         => $this->post('nome'),
            'descricao'    => $this->post('descricao'),
            'categoria_id' => $this->post('categoria_id'),
            'tipo'         => $this->post('tipo'),
        ];
    }

    private function montarItem(?int $id, int $usuarioId, array $dados, string $status, ?string $foto): Item
    {
        $categoriaId = (int) $dados['categoria_id'];

        if (!$this->categorias->existe($categoriaId)) {
            throw new InvalidArgumentException('Escolha uma categoria válida.');
        }

        return Item::fabricar(
            $dados['tipo'],
            $id,
            $usuarioId,
            $categoriaId,
            $dados['nome'],
            $dados['descricao'],
            $status,
            $foto
        );
    }
}
