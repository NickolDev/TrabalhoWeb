<?php

/**
 * Ponto de entrada na VERCEL.
 *
 * A Vercel executa arquivos PHP que ficam na pasta api/. O vercel.json manda
 * todas as URLs (menos /css e /js) para este arquivo, que só repassa para o
 * front controller de sempre: public/index.php.
 *
 * No XAMPP este arquivo não é usado (lá quem faz esse papel é o .htaccess).
 */

// Na Vercel o site fica na raiz do domínio (https://seu-projeto.vercel.app/)
$_SERVER['SCRIPT_NAME'] = '/index.php';

require __DIR__ . '/../public/index.php';
