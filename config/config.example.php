<?php

/**
 * Copie este arquivo para config/config.php e ajuste os valores.
 *
 *   cp config/config.example.php config/config.php
 *
 * O config.php está no .gitignore, então a senha do banco nunca vai para o GitHub.
 */
return [
    'app' => [
        'nome'  => 'Bazar Universitário',
        // true = mostra detalhes dos erros (use só no XAMPP). Na nuvem, deixe false.
        'debug' => true,
    ],

    'db' => [
        'host'    => '127.0.0.1',
        'porta'   => 3306,
        'banco'   => 'bazar_universitario',
        'usuario' => 'root',          // no XAMPP o padrão é root sem senha
        'senha'   => '',              // na VM: 'bazar_app' / 'uma-senha-forte'
        'charset' => 'utf8mb4',
    ],

    'upload' => [
        'tamanho_maximo' => 2 * 1024 * 1024, // 2 MB
        'tipos_permitidos' => [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
        ],
    ],
];
