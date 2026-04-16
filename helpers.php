<?php
// ===================================================
// helpers.php — Funções utilitárias globais
// ===================================================

/**
 * Redireciona para uma rota e termina execução
 */
function redirect(string $url): void {
    header('Location: ' . $url);
    exit;
}

/**
 * Escapa HTML para saída segura
 */
function e(mixed $val): string {
    return htmlspecialchars((string)($val ?? ''), ENT_QUOTES, 'UTF-8');
}

/**
 * Gera e armazena token CSRF na sessão
 */
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Imprime campo hidden com CSRF token
 */
function csrf_field(): void {
    echo '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
}

/**
 * Valida token CSRF (encerra com erro se inválido)
 */
function verificar_csrf(): void {
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals(csrf_token(), $token)) {
        http_response_code(419);
        die('<div style="font-family:monospace;padding:2rem;color:red">Token CSRF inválido. Volte e tente novamente.</div>');
    }
}

/**
 * Flash de mensagem (sucesso/erro)
 */
function flash(string $tipo, string $msg): void {
    $_SESSION['flash'][$tipo] = $msg;
}

/**
 * Lê e remove flash da sessão
 */
function get_flash(string $tipo): ?string {
    $msg = $_SESSION['flash'][$tipo] ?? null;
    unset($_SESSION['flash'][$tipo]);
    return $msg;
}

/**
 * Retorna old input (para repopular formulários após erro)
 */
function old(string $campo, mixed $default = ''): string {
    return e($_SESSION['old'][$campo] ?? $default);
}

/**
 * Salva todos os POST como old input
 */
function salvar_old(array $campos): void {
    $_SESSION['old'] = array_intersect_key($_POST, array_flip($campos));
}

/**
 * Limpa old inputs
 */
function limpar_old(): void {
    unset($_SESSION['old']);
}

/**
 * Gera URL de QR Code via API pública (sem dependência)
 */
function qr_url(string $data, int $size = 200): string {
    return 'https://api.qrserver.com/v1/create-qr-code/?size=' . $size . 'x' . $size . '&data=' . urlencode($data);
}

/**
 * Formata data do MySQL para PT-BR
 */
function fmt_data(string $data): string {
    return date('d/m/Y', strtotime($data));
}

/**
 * Formata datetime do MySQL para PT-BR
 */
function fmt_datetime(string $dt): string {
    return date('d/m/Y H:i', strtotime($dt));
}

/**
 * Retorna mês atual no formato Y-m
 */
function mes_atual(): string {
    return date('Y-m');
}

/**
 * Verifica se a rota atual corresponde ao padrão
 */
function rota_ativa(string $padrao): string {
    $rotaAtual = trim($_GET['rota'] ?? '/', '/');
    $padrao     = trim($padrao, '/');
    return fnmatch($padrao, $rotaAtual) ? 'active' : '';
}
