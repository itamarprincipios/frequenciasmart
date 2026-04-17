<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?> – <?= e($tituloPagina ?? 'Sistema de Frequência') ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --primary:    #4f46e5;
            --primary-dk: #4338ca;
            --success:    #10b981;
            --danger:     #ef4444;
            --warning:    #f59e0b;
            --bg:         #f1f5f9;
            --sidebar-w:  240px;
            --radius:     10px;
        }
        body { font-family: 'Inter', sans-serif; background: var(--bg); color: #1e293b; }

        /* SIDEBAR */
        .sidebar {
            position: fixed; top: 0; left: 0; height: 100vh; width: var(--sidebar-w);
            background: linear-gradient(180deg, #1e1b4b 0%, #312e81 100%);
            color: #fff; display: flex; flex-direction: column; padding: 1.5rem 0; z-index: 100;
        }
        .sidebar-brand { padding: 0 1.5rem 1.5rem; border-bottom: 1px solid rgba(255,255,255,.1); }
        .sidebar-brand h2 { font-size: 1.25rem; font-weight: 700; color: #c7d2fe; }
        .sidebar-brand small { font-size: .7rem; color: #818cf8; }
        .sidebar nav { flex: 1; padding-top: 1rem; }
        .nav-item { display: flex; align-items: center; gap: .75rem; padding: .7rem 1.5rem;
                    color: #c7d2fe; text-decoration: none; font-size: .875rem; transition: all .2s; }
        .nav-item:hover, .nav-item.active { background: rgba(255,255,255,.1); color: #fff; }
        .nav-item svg { width: 18px; height: 18px; flex-shrink: 0; }
        .sidebar-footer { padding: 1rem 1.5rem; border-top: 1px solid rgba(255,255,255,.1); }
        .sidebar-footer span { display: block; font-size: .75rem; color: #818cf8; }
        .sidebar-footer strong { font-size: .875rem; color: #c7d2fe; }

        /* MAIN */
        .main { margin-left: var(--sidebar-w); min-height: 100vh; }
        .topbar { background: #fff; border-bottom: 1px solid #e2e8f0; padding: .75rem 1.5rem;
                  display: flex; align-items: center; justify-content: space-between; position: sticky; top:0; z-index:50; }
        .topbar h1 { font-size: 1.1rem; font-weight: 600; }
        .content { padding: 1.5rem; }

        /* CARDS */
        .cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1.5rem; }
        .card { background: #fff; border-radius: var(--radius); padding: 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,.06);
                border-left: 4px solid var(--primary); }
        .card.green  { border-color: var(--success); }
        .card.red    { border-color: var(--danger); }
        .card.yellow { border-color: var(--warning); }
        .card-label { font-size: .75rem; color: #64748b; font-weight: 500; text-transform: uppercase; letter-spacing: .05em; }
        .card-value { font-size: 2rem; font-weight: 700; margin-top: .25rem; }
        .card-sub   { font-size: .75rem; color: #94a3b8; margin-top: .25rem; }

        /* TABLES */
        .table-wrap { background: #fff; border-radius: var(--radius); box-shadow: 0 1px 3px rgba(0,0,0,.06); overflow: hidden; margin-bottom: 1.5rem; }
        .table-head { padding: 1rem 1.25rem; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; }
        .table-head h3 { font-size: .9rem; font-weight: 600; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: .75rem 1.25rem; text-align: left; font-size: .825rem; }
        th { font-weight: 600; color: #475569; background: #f8fafc; border-bottom: 1px solid #e2e8f0; }
        tr:not(:last-child) td { border-bottom: 1px solid #f1f5f9; }
        tr:hover td { background: #fafafe; }

        /* BADGES */
        .badge { display: inline-block; padding: .2rem .6rem; border-radius: 999px; font-size: .7rem; font-weight: 600; }
        .badge-blue   { background: #dbeafe; color: #1d4ed8; }
        .badge-green  { background: #d1fae5; color: #065f46; }
        .badge-red    { background: #fee2e2; color: #991b1b; }
        .badge-yellow { background: #fef3c7; color: #92400e; }
        .badge-gray   { background: #f1f5f9; color: #475569; }

        /* BUTTONS */
        .btn { display: inline-flex; align-items: center; gap: .4rem; padding: .5rem 1rem; border-radius: 6px;
               font-size: .825rem; font-weight: 500; cursor: pointer; border: none; transition: all .15s; text-decoration: none; }
        .btn-primary { background: var(--primary); color: #fff; }
        .btn-primary:hover { background: var(--primary-dk); }
        .btn-outline { background: transparent; color: var(--primary); border: 1px solid var(--primary); }
        .btn-danger  { background: var(--danger); color: #fff; }

        /* GRID 2 cols */
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; }
        @media (max-width: 900px) { .grid-2 { grid-template-columns: 1fr; } }

        /* ALERT */
        .alert { padding: .75rem 1rem; border-radius: var(--radius); margin-bottom: 1rem; font-size: .875rem; }
        .alert-error   { background: #fee2e2; color: #991b1b; }
        .alert-success { background: #d1fae5; color: #065f46; }

        /* FORM */
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; font-size: .825rem; font-weight: 500; margin-bottom: .4rem; color: #374151; }
        .form-control { width: 100%; padding: .6rem .85rem; border: 1px solid #d1d5db; border-radius: 6px;
                        font-size: .875rem; outline: none; transition: border .15s; font-family: inherit; }
        .form-control:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(79,70,229,.1); }
        select.form-control { background: #fff; }

        /* CHART WRAP */
        .chart-wrap { background: #fff; border-radius: var(--radius); padding: 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,.06); }
        .chart-wrap h3 { font-size: .9rem; font-weight: 600; margin-bottom: 1rem; }

        /* LOGOUT BTN */
        .logout-btn { background: none; border: none; cursor: pointer; color: #ef4444; font-size: .8rem; font-family: inherit; }

        /* RESPONSIVO */
        .menu-toggle { display: none; background: none; border: none; cursor: pointer; color: #1e293b; margin-right: 1rem; }
        .overlay { position: fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index: 90; display: none; }

        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); transition: transform 0.3s ease; box-shadow: 4px 0 15px rgba(0,0,0,0.1); }
            .sidebar.open { transform: translateX(0); }
            .main { margin-left: 0; }
            .menu-toggle { display: flex; align-items: center; }
            .overlay.open { display: block; }
        }
    </style>
</head>
<body>

<?php $usuario = $_SESSION['usuario'] ?? []; $role = $usuario['role'] ?? ''; ?>

<!-- OVERLAY (Mobile) -->
<div class="overlay" id="overlay" onclick="toggleMenu()"></div>

<!-- SIDEBAR -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <h2>📚 FrequenciaSmart</h2>
        <small><?= e($usuario['escola_nome'] ?? 'Sistema de Frequência') ?></small>
    </div>
    <nav>
        <?php if (in_array($role, ['DIRETOR','VICE'])): ?>
        <a href="/dashboard" class="nav-item <?= rota_ativa('dashboard') ?>">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
            Dashboard
        </a>
        <?php endif; ?>

        <?php if (!tem_role('ASSISTENTE')): ?>
        <a href="/orientadora" class="nav-item <?= rota_ativa('orientadora') ?>">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
            Alertas
        </a>
        <?php endif; ?>

        <a href="/frequencia/lancar" class="nav-item <?= rota_ativa('frequencia/lancar') ?>">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
            Lançar Frequência
        </a>

        <a href="/frequencias" class="nav-item <?= rota_ativa('frequencias') ?>">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            Frequências
        </a>

        <?php if (!tem_role('ASSISTENTE')): ?>
        <a href="/alunos" class="nav-item <?= rota_ativa('alunos*') ?>">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/></svg>
            Alunos
        </a>
        <?php endif; ?>

        <?php if (tem_role('DIRETOR', 'VICE', 'ORIENTADORA')): ?>
        <a href="/turmas" class="nav-item <?= rota_ativa('turmas') ?>">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            Turmas
        </a>
        <?php endif; ?>

        <?php if (tem_role('DIRETOR')): ?>
        <a href="/usuarios" class="nav-item <?= rota_ativa('usuarios') ?>">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            Usuários
        </a>
        <?php endif; ?>

        <?php if ($usuario['is_super_admin'] ?? 0): ?>
        <a href="/escolas" class="nav-item <?= rota_ativa('escolas') ?>" style="margin-top:1rem;color:#fbbf24">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            🏢 GERIR ESCOLAS
        </a>
        <?php endif; ?>
    </nav>
    <div class="sidebar-footer">
        <span>Logado como</span>
        <strong><?= e($usuario['nome'] ?? 'Usuário') ?></strong>
        <span style="margin-top:.2rem">
            <span class="badge badge-blue"><?= e($usuario['role'] ?? '') ?></span>
        </span>
        <form method="POST" action="/logout" style="margin-top:.75rem">
            <?php csrf_field(); ?>
            <button type="submit" class="logout-btn">⎋ Sair</button>
        </form>
    </div>
</aside>

<!-- MAIN -->
<main class="main">
    <div class="topbar">
        <div style="display:flex;align-items:center;">
            <button class="menu-toggle" onclick="toggleMenu()">
                <svg style="width:24px;height:24px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <h1><?= e($tituloPagina ?? ($usuario['escola_nome'] ?? 'FrequenciaSmart')) ?></h1>
        </div>
        <span style="font-size:.8rem; color:#64748b"><?= date('d/m/Y') ?></span>
    </div>
    <div class="content">
        <?php if ($erro = get_flash('error')): ?>
            <div class="alert alert-error"><?= e($erro) ?></div>
        <?php endif; ?>
        <?php if ($sucesso = get_flash('success')): ?>
            <div class="alert alert-success"><?= e($sucesso) ?></div>
        <?php endif; ?>
        <!-- CONTEÚDO DA PÁGINA -->
