<?php
/**
 * @var \App\Core\View $this
 * @var int $codigo
 * @var string $mensagem
 */
?>
<section class="erro">
    <p class="erro__codigo"><?= $this->e($codigo) ?></p>
    <h1><?= $this->e($mensagem) ?></h1>
    <a href="<?= $this->url('/') ?>" class="botao">Voltar ao início</a>
</section>
