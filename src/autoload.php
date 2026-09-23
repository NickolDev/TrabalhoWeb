<?php

/**
 * Autoloader PSR-4 simples (sem Composer).
 * Namespace "App\" aponta para a pasta src/.
 *   App\Core\Router            -> src/Core/Router.php
 *   App\Model\Entity\Usuario   -> src/Model/Entity/Usuario.php
 */
spl_autoload_register(static function (string $classe): void {
    $prefixo = 'App\\';

    if (strncmp($classe, $prefixo, strlen($prefixo)) !== 0) {
        return;
    }

    $relativo = substr($classe, strlen($prefixo));
    $arquivo  = __DIR__ . '/' . str_replace('\\', '/', $relativo) . '.php';

    if (is_file($arquivo)) {
        require $arquivo;
    }
});
