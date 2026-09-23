<?php

namespace App\Core;

/**
 * Encapsula o uso de $_SESSION: login do usuário, mensagens "flash"
 * e dados antigos de formulário.
 */
final class Session
{
    public static function iniciar(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => '/',
            'httponly' => true,                             // JS não lê o cookie
            'samesite' => 'Lax',
            'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        ]);
        session_name('BAZARSESSID');
        session_start();
    }

    // ---------- Autenticação ----------

    public static function login(int $id, string $nome): void
    {
        session_regenerate_id(true); // evita fixação de sessão
        $_SESSION['usuario'] = ['id' => $id, 'nome' => $nome];
    }

    public static function logout(): void
    {
        $_SESSION = [];
        session_regenerate_id(true);
    }

    public static function usuarioId(): ?int
    {
        return $_SESSION['usuario']['id'] ?? null;
    }

    public static function usuarioNome(): ?string
    {
        return $_SESSION['usuario']['nome'] ?? null;
    }

    public static function logado(): bool
    {
        return self::usuarioId() !== null;
    }

    // ---------- Mensagens flash (aparecem uma única vez) ----------

    public static function flash(string $tipo, string $mensagem): void
    {
        $_SESSION['flash'][] = ['tipo' => $tipo, 'mensagem' => $mensagem];
    }

    public static function consumirFlash(): array
    {
        $mensagens = $_SESSION['flash'] ?? [];
        unset($_SESSION['flash']);
        return $mensagens;
    }

    // ---------- Dados antigos do formulário (para re-preencher após erro) ----------

    public static function guardarAntigos(array $dados): void
    {
        $_SESSION['antigos'] = $dados;
    }

    public static function consumirAntigos(): array
    {
        $dados = $_SESSION['antigos'] ?? [];
        unset($_SESSION['antigos']);
        return $dados;
    }

    // ---------- Genérico ----------

    public static function get(string $chave, mixed $padrao = null): mixed
    {
        return $_SESSION[$chave] ?? $padrao;
    }

    public static function set(string $chave, mixed $valor): void
    {
        $_SESSION[$chave] = $valor;
    }
}
