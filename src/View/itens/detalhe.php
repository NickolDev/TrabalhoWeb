<?php
/**
 * @var \App\Core\View $this
 * @var \App\Model\Entity\Item $item
 * @var bool $ehDono
 * @var \App\Model\Entity\Interesse[] $interesses
 * @var int $totalInteresses
 * @var bool $jaInteressado
 */
?>
<a href="<?= $this->url('/') ?>" class="voltar">← Voltar para a listagem</a>

<article class="detalhe">
    <div class="detalhe__foto">
        <?php if ($item->getFoto()): ?>
            <img src="<?= $this->url('/fotos/' . $item->getFoto()) ?>" alt="Foto de <?= $this->e($item->getNome()) ?>">
        <?php else: ?>
            <span class="card__sem-foto card__sem-foto--grande">Sem foto</span>
        <?php endif; ?>
    </div>

    <div class="detalhe__info">
        <div class="detalhe__selos">
            <span class="selo selo--<?= $this->e($item->getTipo()) ?>"><?= $this->e($item->getRotuloTipo()) ?></span>
            <span class="selo selo--status-<?= $this->e($item->getStatus()) ?>"><?= $this->e($item->getRotuloStatus()) ?></span>
        </div>

        <h1><?= $this->e($item->getNome()) ?></h1>
        <p class="detalhe__meta">
            <?= $this->e($item->getNomeCategoria()) ?> ·
            publicado por <strong><?= $this->e($item->getNomeDono()) ?></strong>
            em <?= $this->e($this->data($item->getCriadoEm())) ?>
        </p>

        <p class="detalhe__chamada"><?= $this->e($item->getChamada()) ?></p>

        <?php if ($item->getDescricao()): ?>
            <div class="detalhe__descricao"><?= nl2br($this->e($item->getDescricao())) ?></div>
        <?php else: ?>
            <p class="texto-suave">Sem descrição.</p>
        <?php endif; ?>

        <p class="texto-suave">
            <?= $totalInteresses ?> <?= $totalInteresses === 1 ? 'pessoa demonstrou' : 'pessoas demonstraram' ?> interesse.
        </p>

        <div class="detalhe__acoes">
            <?php if ($ehDono): ?>
                <a href="<?= $this->url('/itens/' . $item->getId() . '/editar') ?>" class="botao botao--secundario">Editar</a>

                <form action="<?= $this->url('/itens/' . $item->getId() . '/status') ?>" method="post">
                    <?= $this->csrf() ?>
                    <button type="submit" class="botao botao--secundario">
                        <?= $item->estaDisponivel() ? 'Marcar como ' . $this->e(strtolower($item->getRotuloConcluido())) : 'Disponibilizar novamente' ?>
                    </button>
                </form>

                <form action="<?= $this->url('/itens/' . $item->getId() . '/remover') ?>" method="post"
                      data-confirmar="Remover este item definitivamente?">
                    <?= $this->csrf() ?>
                    <button type="submit" class="botao botao--perigo">Remover</button>
                </form>

            <?php elseif (!$item->estaDisponivel()): ?>
                <p class="alerta alerta--aviso">Este item já foi <?= $this->e(strtolower($item->getRotuloConcluido())) ?>.</p>

            <?php elseif (!$this->usuarioLogado()): ?>
                <a href="<?= $this->url('/login') ?>" class="botao">Entre para demonstrar interesse</a>

            <?php elseif ($jaInteressado): ?>
                <p class="alerta alerta--sucesso">Você já demonstrou interesse neste item.</p>
                <form action="<?= $this->url('/itens/' . $item->getId() . '/interesse/remover') ?>" method="post">
                    <?= $this->csrf() ?>
                    <button type="submit" class="link-botao">Cancelar interesse</button>
                </form>

            <?php else: ?>
                <form action="<?= $this->url('/itens/' . $item->getId() . '/interesse') ?>" method="post">
                    <?= $this->csrf() ?>
                    <button type="submit" class="botao botao--grande">Tenho interesse</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</article>

<?php if ($ehDono): ?>
    <section class="painel-bloco">
        <h2>Quem tem interesse</h2>
        <?php if ($interesses === []): ?>
            <p class="texto-suave">Ninguém demonstrou interesse ainda.</p>
        <?php else: ?>
            <table class="tabela">
                <thead>
                <tr><th>Nome</th><th>E-mail para contato</th><th>Data</th></tr>
                </thead>
                <tbody>
                <?php foreach ($interesses as $interesse): ?>
                    <tr>
                        <td><?= $this->e($interesse->getNomeUsuario()) ?></td>
                        <td><a href="mailto:<?= $this->e($interesse->getEmailUsuario()) ?>"><?= $this->e($interesse->getEmailUsuario()) ?></a></td>
                        <td><?= $this->e($interesse->getCriadoEm()->format('d/m/Y H:i')) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>
<?php endif; ?>
