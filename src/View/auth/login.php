<?php
/**
 * @var \App\Core\View $this
 * @var array $antigos
 */
?>
<section class="formulario-caixa formulario-caixa--estreita">
    <h1>Entrar</h1>
    <p class="texto-suave">Acesse para publicar itens e demonstrar interesse.</p>

    <form action="<?= $this->url('/login') ?>" method="post" class="formulario">
        <?= $this->csrf() ?>

        <label>
            E-mail
            <input type="email" name="email" value="<?= $this->e($antigos['email'] ?? '') ?>" required autofocus autocomplete="email">
        </label>

        <label>
            Senha
            <input type="password" name="senha" required autocomplete="current-password">
        </label>

        <button type="submit" class="botao botao--largo">Entrar</button>
    </form>

    <p class="formulario-caixa__rodape">Ainda não tem conta? <a href="<?= $this->url('/cadastro') ?>">Cadastre-se</a></p>
</section>
