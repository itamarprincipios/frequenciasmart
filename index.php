<?php
// ===================================================
// index.php — Roteador principal do EduTrack
// ===================================================

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/helpers.php';

iniciar_sessao();

// Pega a rota da URL (ex: "dashboard", "alunos/criar", "alunos/5/editar")
$rota = trim($_GET['rota'] ?? '/', '/');
$metodo = $_SERVER['REQUEST_METHOD'];

// -----------------------------------------------
// ROTAS PÚBLICAS
// -----------------------------------------------
if ($rota === '' || $rota === 'login') {
    if ($metodo === 'POST') {
        require __DIR__ . '/actions/login_post.php';
    } else {
        // Se já logado, redireciona
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

// -----------------------------------------------
// ROTAS PROTEGIDAS (requer login)
// -----------------------------------------------
requer_login();

// Extrai segmentos da rota
$partes = explode('/', $rota);

// --- Dashboard ---
if ($rota === 'dashboard') {
    require __DIR__ . '/pages/dashboard.php';
    exit;
}

// --- Orientadora / Alertas ---
if ($rota === 'orientadora') {
    require __DIR__ . '/pages/orientadora.php';
    exit;
}

// --- Turmas ---
if ($rota === 'turmas') {
    require __DIR__ . '/pages/turmas.php';
    exit;
}
if (count($partes) === 3 && $partes[0] === 'turmas' && $partes[2] === 'qrcode' && is_numeric($partes[1])) {
    $id = (int)$partes[1];
    require __DIR__ . '/pages/turmas_qrcode.php';
    exit;
}

// --- Usuários ---
if ($rota === 'usuarios') {
    require __DIR__ . '/pages/usuarios.php';
    exit;
}

// --- Frequências ---
if ($rota === 'frequencias') {
    require __DIR__ . '/pages/frequencias.php';
    exit;
}
if ($rota === 'frequencia/lancar') {
    require __DIR__ . '/pages/frequencia_lancar.php';
    exit;
}
if ($rota === 'frequencia/registrar' && $metodo === 'POST') {
    require __DIR__ . '/actions/frequencia_registrar.php';
    exit;
}

// --- Alunos ---
if ($rota === 'alunos') {
    if ($metodo === 'POST') {
        require __DIR__ . '/actions/alunos_store.php';
    } else {
        require __DIR__ . '/pages/alunos_index.php';
    }
    exit;
}
if ($rota === 'alunos/criar') {
    require __DIR__ . '/pages/alunos_form.php';
    exit;
}
if (count($partes) === 2 && $partes[0] === 'alunos' && is_numeric($partes[1])) {
    // POST /alunos/{id} → update
    $id = (int)$partes[1];
    if ($metodo === 'POST') {
        require __DIR__ . '/actions/alunos_update.php';
    }
    exit;
}
if (count($partes) === 3 && $partes[0] === 'alunos' && is_numeric($partes[1])) {
    $id = (int)$partes[1];
    $acao = $partes[2];

    if ($acao === 'editar') {
        require __DIR__ . '/pages/alunos_form.php';
        exit;
    }
    if ($acao === 'excluir' && $metodo === 'POST') {
        require __DIR__ . '/actions/alunos_destroy.php';
        exit;
    }
    if ($acao === 'qrcode') {
        require __DIR__ . '/pages/alunos_qrcode.php';
        exit;
    }
}

// -----------------------------------------------
// 404 — Rota não encontrada
// -----------------------------------------------
http_response_code(404);
echo '<!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8"><title>404 – Página não encontrada</title>
<style>body{font-family:Inter,sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh;background:#f1f5f9;margin:0}
.box{text-align:center;padding:3rem;background:#fff;border-radius:16px;box-shadow:0 4px 24px rgba(0,0,0,.08)}
h1{font-size:4rem;color:#4f46e5;margin:0}p{color:#64748b;margin:.5rem 0 1.5rem}a{color:#4f46e5;text-decoration:none;font-weight:600}</style>
</head><body><div class="box"><h1>404</h1><p>Página não encontrada.</p><a href="/dashboard">← Voltar ao início</a></div></body></html>';
