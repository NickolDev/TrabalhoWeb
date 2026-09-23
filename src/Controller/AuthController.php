<?php

namespace App\Controller;

use App\Core\Controller;
use App\Core\Session;
use App\Model\DAO\UsuarioDAO;
use App\Model\Entity\Usuario;
use InvalidArgumentException;

/** Cadastro, login e logout de usuários. */
class AuthController extends Controller
{
    private UsuarioDAO $usuarios;

    public function __construct()
    {
        $this->usuarios = new UsuarioDAO();
    }

    public function formCadastro(): void
    {
        if (Session::logado()) {
            $this->redirecionar('/painel');
        }

        $this->render('auth/cadastro', [
            'titulo'  => 'Criar conta',
            'antigos' => Session::consumirAntigos(),
        ]);
    }

    public function cadastrar(): void
    {
        $this->validarCsrf();

        $nome        = $this->post('nome');
        $email       = $this->post('email');
        $senha       = $_POST['senha'] ?? '';
        $confirmacao = $_POST['confirmacao'] ?? '';
        $antigos     = ['nome' => $nome, 'email' => $email];

        if (!is_string($senha) || !is_string($confirmacao) || $senha !== $confirmacao) {
            $this->voltarComErro('/cadastro', 'As senhas não conferem.', $antigos);
        }

        try {
            $usuario = Usuario::registrar($nome, $email, $senha);
        } catch (InvalidArgumentException $e) {
            $this->voltarComErro('/cadastro', $e->getMessage(), $antigos);
        }

        if ($this->usuarios->emailExiste($usuario->getEmail())) {
            $this->voltarComErro('/cadastro', 'Já existe uma conta com este e-mail.', $antigos);
        }

        $this->usuarios->inserir($usuario);
        Session::login($usuario->getId(), $usuario->getNome());
        Session::flash('sucesso', 'Conta criada! Bem-vindo(a) ao Bazar, ' . $usuario->getNome() . '.');

        $this->redirecionar('/painel');
    }

    public function formLogin(): void
    {
        if (Session::logado()) {
            $this->redirecionar('/painel');
        }

        $this->render('auth/login', [
            'titulo'  => 'Entrar',
            'antigos' => Session::consumirAntigos(),
        ]);
    }

    public function login(): void
    {
        $this->validarCsrf();

        $email = $this->post('email');
        $senha = $_POST['senha'] ?? '';

        $usuario = $this->usuarios->buscarPorEmail($email);

        // Mensagem genérica: não revela se o e-mail existe ou não
        if ($usuario === null || !is_string($senha) || !$usuario->verificarSenha($senha)) {
            $this->voltarComErro('/login', 'E-mail ou senha incorretos.', ['email' => $email]);
        }

        // Se o PHP passou a usar um algoritmo mais forte, atualiza o hash salvo
        if ($usuario->precisaRehash()) {
            $usuario->definirSenha($senha);
            $this->usuarios->atualizarSenha($usuario);
        }

        Session::login($usuario->getId(), $usuario->getNome());
        Session::flash('sucesso', 'Olá, ' . $usuario->getNome() . '!');

        $this->redirecionar('/painel');
    }

    public function logout(): void
    {
        $this->validarCsrf();

        Session::logout();
        Session::flash('sucesso', 'Você saiu da sua conta.');

        $this->redirecionar('/');
    }
}
