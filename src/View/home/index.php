<?php
/**
 * @var \App\Core\View $this
 * @var \App\Model\Entity\Item[] $itens
 * @var \App\Model\Entity\Categoria[] $categorias
 * @var ?int $categoriaAtualId
 * @var string $busca
 */
?>
<section class="destaque">
    <div>
        <h1>Doe ou troque o que não usa mais</h1>
        <p>Livros, materiais e eletrônicos circulando entre estudantes. Encontre algo útil ou publique o que está parado.</p>
    </div>
    <?php if (!$this->usuarioLogado()): ?>
        <a href="<?= $this->url('/cadastro') ?>" class="botao">Criar conta e publicar</a>
    <?php else: ?>
        <a href="<?= $this->url('/itens/novo') ?>" class="botao">+ Publicar item</a>
    <?php endif; ?>
</section>

<form action="<?= $this->url('/') ?>" method="get" class="filtros">
    <label class="filtros__campo">
        <span class="sr-only">Buscar</span>
        <input type="search" name="busca" value="<?= $this->e($busca) ?>" placeholder="Buscar por nome ou descrição" maxlength="100">
    </label>
    <?php if ($categoriaAtualId !== null): ?>
        <input type="hidden" name="categoria" value="<?= $this->e($categoriaAtualId) ?>">
    <?php endif; ?>
    <button type="submit" class="botao botao--secundario">Buscar</button>
    <?php if ($busca !== '' || $categoriaAtualId !== null): ?>
        <a href="<?= $this->url('/') ?>" class="link-limpar">Limpar filtros</a>
    <?php endif; ?>
</form>

<nav class="categorias" aria-label="Categorias">
    <a href="<?= $this->url('/') ?>" class="chip <?= $categoriaAtualId === null ? 'chip--ativo' : '' ?>">Todas</a>
    <?php foreach ($categorias as $categoria): ?>
        <a href="<?= $this->url('/?categoria=' . $categoria->getId()) ?>"
           class="chip <?= $categoria->getId() === $categoriaAtualId ? 'chip--ativo' : '' ?>">
            <?= $this->e($categoria->getNome()) ?>
        </a>
    <?php endforeach; ?>
</nav>

<p class="contagem"><?= count($itens) ?> <?= count($itens) === 1 ? 'item disponível' : 'itens disponíveis' ?></p>

<?php if ($itens === []): ?>
    <div class="vazio">
        <p>Nenhum item encontrado<?= $categoriaAtualId !== null || $busca !== '' ? ' com esses filtros' : '' ?>.</p>
        <?php if ($this->usuarioLogado()): ?>
            <a href="<?= $this->url('/itens/novo') ?>" class="botao">Publique o primeiro</a>
        <?php endif; ?>
    </div>
<?php else: ?>
    <div class="grade">
        <?php foreach ($itens as $item): ?>
            <?= $this->parcial('itens/_card', ['item' => $item]) ?>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
