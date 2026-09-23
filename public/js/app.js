/*
 * JavaScript apenas para INTERFACE (client-side).
 * Nenhuma regra de negócio fica aqui: validação, posse do item,
 * senhas e acesso ao banco são tratados no servidor (PHP).
 */
(function () {
    'use strict';

    // Menu no celular
    var botaoMenu = document.querySelector('[data-menu-botao]');
    var menu = document.querySelector('[data-menu]');
    if (botaoMenu && menu) {
        botaoMenu.addEventListener('click', function () {
            var aberto = menu.classList.toggle('menu--aberto');
            botaoMenu.setAttribute('aria-expanded', aberto ? 'true' : 'false');
        });
    }

    // Confirmação antes de remover
    document.querySelectorAll('form[data-confirmar]').forEach(function (form) {
        form.addEventListener('submit', function (evento) {
            if (!window.confirm(form.getAttribute('data-confirmar'))) {
                evento.preventDefault();
            }
        });
    });

    // Pré-visualização da foto escolhida
    var campoFoto = document.querySelector('[data-preview-foto]');
    var preview = document.querySelector('[data-preview]');
    if (campoFoto && preview) {
        campoFoto.addEventListener('change', function () {
            var arquivo = campoFoto.files && campoFoto.files[0];
            if (!arquivo) {
                return;
            }
            var img = document.createElement('img');
            img.alt = 'Pré-visualização';
            img.src = URL.createObjectURL(arquivo);
            preview.replaceChildren(img);
        });
    }
})();
