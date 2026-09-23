<?php
/**
 * @var \App\Core\View $this
 * @var array $antigos
 */
?>
<section class="formulario-caixa formulario-caixa--estreita">
    <h1>Criar conta</h1>
    <p class="texto-suave">Leva menos de um minuto.</p>

    <form action="<?= $this->url('/cadastro') ?>" method="post" class="formulario">
        <?= $this->csrf() ?>

        <label>
            Nome
            <input type="text" name="nome" value="<?= $this->e($antigos['nome'] ?? '') ?>" maxlength="100" required autofocus autocomplete="name">
        </label>

        <label>
            E-mail
            <input type="email" name="email" value="<?= $this->e($antigos['email'] ?? '') ?>" maxlength="150" required autocomplete="email">
        </label>

        <label>
            Senha (mínimo 6 caracteres)
            <input type="password" name="senha" minlength="6" required autocomplete="new-password">
        </label>

        <label>
            Confirme a senha
            <input type="password" name="confirmacao" minlength="6" required autocomplete="new-password">
        </label>

        <button type="submit" class="botao botao--largo">Criar conta</button>
    </form>

    <p class="formulario-caixa__rodape">Já tem conta? <a href="<?= $this->url('/login') ?>">Entrar</a></p>
</section>
