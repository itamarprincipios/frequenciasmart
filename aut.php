<?php
// ===================================================
// auth.php — Funções de autenticação
// ===================================================

/**
 * Inicia sessão com configurações seguras
 */
function iniciar_sessao(): void {
    if (session_status() === PHP_SESSION_NONE) {
        ini_set('session.cookie_httponly', 1);
        ini_set('session.use_strict_mode', 1);
        session_set_cookie_params(SESSION_LIFETIME);
        session_start();
    }
}

/**
 * Retorna o usuário logado ou null
 */
function usuario_logado(): ?array {
    return $_SESSION['usuario'] ?? null;
}

/**
 * Retorna o ID da escola do usuário logado
 */
function escola_id(): int {
    return (int)($_SESSION['usuario']['escola_id'] ?? 0);
}

/**
 * Protege a rota — redireciona para login se não autenticado
 */
function requer_login(): void {
    if (!isset($_SESSION['usuario'])) {
        redirect('/login');
    }
}

/**
 * Verifica se o usuário tem o role necessário
 */
function requer_role(string ...$roles): void {
    requer_login();
    $usuario = $_SESSION['usuario'];
    if (!in_array($usuario['role'], $roles)) {
        include __DIR__ . '/pages/403.php';
        exit;
    }
}

/**
 * Verifica se o usuário tem um dos roles (retorna bool)
 */
function tem_role(string ...$roles): bool {
    $usuario = $_SESSION['usuario'] ?? null;
    if (!$usuario) return false;
    return in_array($usuario['role'], $roles);
}
