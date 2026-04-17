<?php
// ===================================================
// index.php - Roteador principal do FrequenciaSmart
// ===================================================

ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: text/html; charset=utf-8');

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/aut.php';
require_once __DIR__ . '/helpers.php';

iniciar_sessao();

// Pega a rota da URL
$rota = trim($_GET['rota'] ?? '/', '/');
$metodo = $_SERVER['REQUEST_METHOD'];

if ($rota === '' || $rota === 'login') {
    if ($metodo === 'POST') {
        require __DIR__ . '/actions/login_post.php';
    } else {
        if (isset($_SESSION['usuario'])) {
            $role = $_SESSION['usuario']['role'];
            redirect($role === 'ORIENTADORA' ? '/orientadora' : '/dashboard');
        }
        require __DIR__ . '/pages/login.php';
    }
    exit;
}

if ($rota === 'logout' && $metodo === 'POST') {
    require __DIR__ . '/actions/logout.php';
    exit;
}

requer_login();
$partes = explode('/', $rota);

if ($rota === 'dashboard') { require __DIR__ . '/pages/dashboard.php'; exit; }
if ($rota === 'orientadora') { require __DIR__ . '/pages/orientadora.php'; exit; }
if ($rota === 'turmas') { require __DIR__ . '/pages/turmas.php'; exit; }
if (count($partes) === 3 && $partes[0] === 'turmas' && is_numeric($partes[1])) {
    $id = (int)$partes[1];
    $acao = $partes[2];
    if ($acao === 'qrcode') { require __DIR__ . '/pages/turmas_qrcode.php'; exit; }
    if ($acao === 'imprimir') { require __DIR__ . '/pages/turmas_imprimir.php'; exit; }
    if ($acao === 'excluir' && $metodo === 'POST') { require __DIR__ . '/actions/turmas_destroy.php'; exit; }
}
if ($rota === 'usuarios') { require __DIR__ . '/pages/usuarios.php'; exit; }
if ($rota === 'escolas') { require __DIR__ . '/pages/escolas_index.php'; exit; }
if ($rota === 'frequencias') { require __DIR__ . '/pages/frequencias.php'; exit; }
if ($rota === 'frequencia/lancar') { require __DIR__ . '/pages/frequencia_lancar.php'; exit; }
if ($rota === 'frequencia/registrar' && $metodo === 'POST') { require __DIR__ . '/actions/frequencia_registrar.php'; exit; }
if ($rota === 'alunos') {
    if ($metodo === 'POST') require __DIR__ . '/actions/alunos_store.php';
    else require __DIR__ . '/pages/alunos_index.php';
    exit;
}
if ($rota === 'alunos/criar') { require __DIR__ . '/pages/alunos_form.php'; exit; }
if (count($partes) === 2 && $partes[0] === 'alunos' && is_numeric($partes[1])) {
    $id = (int)$partes[1];
    if ($metodo === 'POST') require __DIR__ . '/actions/alunos_update.php';
    exit;
}
if (count($partes) === 3 && $partes[0] === 'alunos' && is_numeric($partes[1])) {
    $id = (int)$partes[1];
    $acao = $partes[2];
    if ($acao === 'editar') { require __DIR__ . '/pages/alunos_form.php'; exit; }
    if ($acao === 'excluir' && $metodo === 'POST') { require __DIR__ . '/actions/alunos_destroy.php'; exit; }
    if ($acao === 'qrcode') { require __DIR__ . '/pages/alunos_qrcode.php'; exit; }
}

http_response_code(404);
echo "404 - Pagina nao encontrada";
