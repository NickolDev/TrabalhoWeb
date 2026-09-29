<?php

/**
 * Front Controller — TODA requisição passa por aqui.
 * No XAMPP quem manda para cá é o .htaccess; na Vercel é o api/index.php.
 * Este arquivo carrega as configurações, registra as rotas e chama o Router.
 */

use App\Controller\AuthController;
use App\Controller\FotoController;
use App\Controller\HomeController;
use App\Controller\InteresseController;
use App\Controller\ItemController;
use App\Controller\PainelController;
use App\Core\Config;
use App\Core\HttpException;
use App\Core\Router;
use App\Core\Session;
use App\Core\Url;
use App\Core\View;

define('RAIZ_PROJETO', dirname(__DIR__));

require RAIZ_PROJETO . '/src/autoload.php';

Config::carregar(RAIZ_PROJETO . '/config/config.php');

$debug = (bool) Config::get('app.debug', false);
ini_set('display_errors', $debug ? '1' : '0');
error_reporting(E_ALL);
date_default_timezone_set('America/Sao_Paulo');

// ---------------- Rotas ----------------
$router = new Router();

// Público
$router->get('/', [HomeController::class, 'index']);
$router->get('/itens/{id}', [ItemController::class, 'show']);

// Autenticação
$router->get('/cadastro', [AuthController::class, 'formCadastro']);
$router->post('/cadastro', [AuthController::class, 'cadastrar']);
$router->get('/login', [AuthController::class, 'formLogin']);
$router->post('/login', [AuthController::class, 'login']);
$router->post('/logout', [AuthController::class, 'logout']);

// CRUD de itens (exige login)
$router->get('/itens/novo', [ItemController::class, 'create']);
$router->post('/itens', [ItemController::class, 'store']);
$router->get('/itens/{id}/editar', [ItemController::class, 'edit']);
$router->post('/itens/{id}/editar', [ItemController::class, 'update']);
$router->post('/itens/{id}/remover', [ItemController::class, 'destroy']);
$router->post('/itens/{id}/status', [ItemController::class, 'alternarStatus']);

// Interesse
$router->post('/itens/{id}/interesse', [InteresseController::class, 'registrar']);
$router->post('/itens/{id}/interesse/remover', [InteresseController::class, 'remover']);

// Painel do usuário
$router->get('/painel', [PainelController::class, 'index']);

// Fotos dos itens (guardadas no banco)
$router->get('/fotos/{arquivo}', [FotoController::class, 'mostrar']);

// ---------------- Despacho ----------------
$caminho = Url::resolverCaminho($_SERVER['SCRIPT_NAME'] ?? '/index.php', $_SERVER['REQUEST_URI'] ?? '/');

try {
    // As fotos são públicas e não usam login: não precisam abrir a sessão
    if (!str_starts_with($caminho, '/fotos/')) {
        Session::iniciar();
    }

    $router->despachar($_SERVER['REQUEST_METHOD'] ?? 'GET', $caminho);
} catch (HttpException $e) {
    http_response_code($e->getCode());
    echo (new View())->render('erros/erro', [
        'titulo'   => 'Erro ' . $e->getCode(),
        'codigo'   => $e->getCode(),
        'mensagem' => $e->getMessage(),
    ]);
} catch (Throwable $e) {
    error_log((string) $e);
    http_response_code(500);
    echo (new View())->render('erros/erro', [
        'titulo'   => 'Erro 500',
        'codigo'   => 500,
        'mensagem' => $debug ? $e->getMessage() . ' em ' . $e->getFile() . ':' . $e->getLine()
                             : 'Ocorreu um erro inesperado. Tente novamente mais tarde.',
    ]);
}
