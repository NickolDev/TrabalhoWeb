<?php /** @var \App\Core\View $this */ ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $this->e($titulo ?? '') ?> · <?= $this->e($this->nomeApp()) ?></title>
    <link rel="stylesheet" href="<?= $this->url('/css/style.css') ?>">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>📦</text></svg>">
</head>
<body>
<header class="topo">
    <div class="container topo__conteudo">
        <a href="<?= $this->url('/') ?>" class="marca">
            <span class="marca__icone" aria-hidden="true">B</span>
            <span><?= $this->e($this->nomeApp()) ?></span>
        </a>

        <button class="menu-botao" type="button" aria-label="Abrir menu" aria-expanded="false" data-menu-botao>
            <span></span><span></span><span></span>
        </button>

        <nav class="menu" data-menu>
            <a href="<?= $this->url('/') ?>">Início</a>
            <?php if ($this->usuarioLogado()): ?>
                <a href="<?= $this->url('/painel') ?>">Meu painel</a>
                <a href="<?= $this->url('/itens/novo') ?>" class="botao botao--pequeno">+ Publicar item</a>
                <form action="<?= $this->url('/logout') ?>" method="post" class="menu__sair">
                    <?= $this->csrf() ?>
                    <button type="submit" class="link-botao">Sair (<?= $this->e($this->usuarioNome()) ?>)</button>
                </form>
            <?php else: ?>
                <a href="<?= $this->url('/login') ?>">Entrar</a>
                <a href="<?= $this->url('/cadastro') ?>" class="botao botao--pequeno">Criar conta</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<main class="container principal">
    <?php foreach ($this->mensagens() as $flash): ?>
        <div class="alerta alerta--<?= $this->e($flash['tipo']) ?>" role="status">
            <?= $this->e($flash['mensagem']) ?>
        </div>
    <?php endforeach; ?>

    <?= $conteudo /* já renderizado e escapado pela view interna */ ?>
</main>

<footer class="rodape">
    <div class="container">
        <?= $this->e($this->nomeApp()) ?> · Trabalho final AB722 — Programação para Web II<br>
        Desenvolvido por Nickolas Goulart Galasso (RA 842278) e Pedro dos Anjos Sanches (RA 842621)
    </div>
</footer>

<script src="<?= $this->url('/js/app.js') ?>"></script>
</body>
</html>
