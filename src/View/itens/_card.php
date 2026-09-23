<?php
/**
 * @var \App\Core\View $this
 * @var \App\Model\Entity\Item $item
 */
?>
<article class="card">
    <a href="<?= $this->url('/itens/' . $item->getId()) ?>" class="card__link">
        <div class="card__foto">
            <?php if ($item->getFoto()): ?>
                <img src="<?= $this->url('/uploads/' . $item->getFoto()) ?>" alt="Foto de <?= $this->e($item->getNome()) ?>" loading="lazy">
            <?php else: ?>
                <span class="card__sem-foto" aria-hidden="true"><?= $this->e(mb_strtoupper(mb_substr($item->getNome(), 0, 1))) ?></span>
            <?php endif; ?>
            <span class="selo selo--<?= $this->e($item->getTipo()) ?>"><?= $this->e($item->getRotuloTipo()) ?></span>
        </div>
        <div class="card__corpo">
            <p class="card__categoria"><?= $this->e($item->getNomeCategoria()) ?></p>
            <h3 class="card__titulo"><?= $this->e($item->getNome()) ?></h3>
            <p class="card__meta">por <?= $this->e($item->getNomeDono()) ?> · <?= $this->e($this->data($item->getCriadoEm())) ?></p>
        </div>
    </a>
</article>
