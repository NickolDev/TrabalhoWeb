<?php

/**
 * Configuração do projeto.
 *
 * Cada valor é lido de uma VARIÁVEL DE AMBIENTE quando ela existe.
 * - Na Vercel, as variáveis ficam no painel (Settings → Environment Variables),
 *   então a senha do banco nunca aparece no código nem no GitHub.
 * - No XAMPP nenhuma variável existe, e valem os padrões depois do "?:".
 */
return [
    'app' => [
        'nome'  => 'Bazar Universitário',
        // Na Vercel, APP_ENV=producao esconde os detalhes dos erros.
        // No XAMPP os detalhes aparecem, o que ajuda a achar problemas.
        'debug' => getenv('APP_ENV') !== 'producao',
    ],

    'db' => [
        'host'    => getenv('DB_HOST') ?: '127.0.0.1',
        'porta'   => (int) (getenv('DB_PORT') ?: 3306),
        'banco'   => getenv('DB_NAME') ?: 'bazar_universitario',
        'usuario' => getenv('DB_USER') ?: 'root',   // XAMPP: root sem senha
        'senha'   => getenv('DB_PASS') ?: '',
        'charset' => 'utf8mb4',
        // Conexão criptografada (SSL). O Aiven exige; no XAMPP fica desligada.
        // Com DB_SSL=true, o certificado config/ca.pem é usado.
        'ssl'     => getenv('DB_SSL') === 'true',
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
