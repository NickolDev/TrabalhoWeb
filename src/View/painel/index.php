<?php
/**
 * @var \App\Core\View $this
 * @var string $nome
 * @var array $estatisticas
 * @var int $interessesRecebidos
 * @var \App\Model\Entity\Item[] $meusItens
 * @var \App\Model\Entity\Item[] $meusInteresses
 */
?>
<div class="cabecalho-pagina">
    <div>
        <h1>Olá, <?= $this->e($nome) ?></h1>
        <p class="texto-suave">Acompanhe seus itens e os interesses que você recebeu.</p>
    </div>
    <a href="<?= $this->url('/itens/novo') ?>" class="botao">+ Publicar item</a>
</div>

<section class="numeros" aria-label="Resumo">
    <div class="numero">
        <span class="numero__valor"><?= $this->e($estatisticas['total']) ?></span>
        <span class="numero__rotulo">itens cadastrados</span>
    </div>
    <div class="numero">
        <span class="numero__valor"><?= $this->e($estatisticas['disponiveis']) ?></span>
        <span class="numero__rotulo">disponíveis</span>
    </div>
    <div class="numero">
        <span class="numero__valor"><?= $this->e($estatisticas['concluidos']) ?></span>
        <span class="numero__rotulo">doados / trocados</span>
    </div>
    <div class="numero">
        <span class="numero__valor"><?= $this->e($interessesRecebidos) ?></span>
        <span class="numero__rotulo">interesses recebidos</span>
    </div>
</section>

<section class="painel-bloco">
    <h2>Meus itens</h2>

    <?php if ($meusItens === []): ?>
        <div class="vazio">
            <p>Você ainda não publicou nenhum item.</p>
            <a href="<?= $this->url('/itens/novo') ?>" class="botao">Publicar o primeiro</a>
        </div>
    <?php else: ?>
        <div class="tabela-rolagem">
            <table class="tabela">
                <thead>
                <tr>
                    <th>Item</th>
                    <th>Tipo</th>
                    <th>Status</th>
                    <th>Interesses</th>
                    <th><span class="sr-only">Ações</span></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($meusItens as $item): ?>
                    <tr class="<?= $item->estaDisponivel() ? '' : 'linha--concluida' ?>">
                        <td>
                            <a href="<?= $this->url('/itens/' . $item->getId()) ?>"><?= $this->e($item->getNome()) ?></a>
                            <small class="texto-suave"><?= $this->e($item->getNomeCategoria()) ?></small>
                        </td>
                        <td><span class="selo selo--<?= $this->e($item->getTipo()) ?>"><?= $this->e($item->getRotuloTipo()) ?></span></td>
                        <td><span class="selo selo--status-<?= $this->e($item->getStatus()) ?>"><?= $this->e($item->getRotuloStatus()) ?></span></td>
                        <td><?= $this->e($item->getTotalInteresses() ?? 0) ?></td>
                        <td>
                            <div class="tabela__acoes">
                                <a href="<?= $this->url('/itens/' . $item->getId() . '/editar') ?>" class="link-botao">Editar</a>
                                <form action="<?= $this->url('/itens/' . $item->getId() . '/status') ?>" method="post">
                                    <?= $this->csrf() ?>
                                    <input type="hidden" name="voltar" value="painel">
                                    <button type="submit" class="link-botao">
                                        <?= $item->estaDisponivel() ? 'Marcar ' . $this->e(mb_strtolower($item->getRotuloConcluido())) : 'Reabrir' ?>
                                    </button>
                                </form>
                                <form action="<?= $this->url('/itens/' . $item->getId() . '/remover') ?>" method="post"
                                      data-confirmar="Remover este item definitivamente?">
                                    <?= $this->csrf() ?>
                                    <button type="submit" class="link-botao link-botao--perigo">Remover</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<section class="painel-bloco">
    <h2>Itens em que tenho interesse</h2>

    <?php if ($meusInteresses === []): ?>
        <p class="texto-suave">Você ainda não demonstrou interesse em nenhum item. <a href="<?= $this->url('/') ?>">Ver itens disponíveis</a></p>
    <?php else: ?>
        <div class="grade">
            <?php foreach ($meusInteresses as $item): ?>
                <?= $this->parcial('itens/_card', ['item' => $item]) ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
