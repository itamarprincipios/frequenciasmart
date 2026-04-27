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
            --primary:    #6366f1; /* Indigo 500 */
            --primary-dk: #4f46e5; /* Indigo 600 */
            --success:    #10b981;
            --danger:     #ef4444;
            --warning:    #f59e0b;
            --bg:         #f8fafc; /* Slate 50 */
            --text-main:  #0f172a; /* Slate 900 */
            --text-muted: #64748b; /* Slate 500 */
            --sidebar-bg: #0f172a; /* Slate 900 */
            --sidebar-w:  260px;
            --radius:     12px;
            --shadow-sm:  0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow:     0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
        }
        body { font-family: 'Inter', sans-serif; background: var(--bg); color: var(--text-main); line-height: 1.5; -webkit-font-smoothing: antialiased; }

        /* SIDEBAR */
        .sidebar {
            position: fixed; top: 0; left: 0; height: 100vh; width: var(--sidebar-w);
            background: var(--sidebar-bg); color: #fff; display: flex; flex-direction: column; z-index: 100;
        }
        .sidebar-brand { padding: 2rem 1.5rem; }
        .sidebar-brand h2 { font-size: 1.25rem; font-weight: 800; color: #fff; display: flex; align-items: center; gap: .5rem; }
        .sidebar-brand small { font-size: .7rem; color: #94a3b8; display: block; margin-top: .25rem; letter-spacing: .02em; }
        
        .sidebar nav { flex: 1; padding: 0 1rem; }
        .nav-item { 
            display: flex; align-items: center; gap: .75rem; padding: .8rem 1rem;
            color: #94a3b8; text-decoration: none; font-size: .875rem; font-weight: 500;
            border-radius: 8px; transition: all .2s ease; margin-bottom: .25rem;
        }
        .nav-item:hover { color: #fff; background: rgba(255,255,255,.05); }
        .nav-item.active { background: var(--primary); color: #fff; box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3); }
        .nav-item svg { width: 20px; height: 20px; flex-shrink: 0; opacity: .8; }

        .sidebar-footer { padding: 1.5rem 1rem 140px; border-top: 1px solid rgba(255,255,255,.1); }
        .user-card { background: rgba(255,255,255,.03); border-radius: 12px; padding: .75rem; border: 1px solid rgba(255,255,255,.05); }
        .user-card span { display: block; font-size: .7rem; color: #64748b; margin-bottom: .1rem; }
        .user-card strong { font-size: .825rem; color: #f1f5f9; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; display: block; }
        
        .logout-btn { 
            width: 100%; margin-top: .75rem; background: rgba(239, 68, 68, 0.1); 
            color: #f87171; border: 1px solid rgba(239, 68, 68, 0.2); 
            padding: .6rem; border-radius: 8px; font-size: .75rem; font-weight: 600;
            cursor: pointer; transition: all .2s;
        }
        .logout-btn:hover { background: #ef4444; color: #fff; border-color: #ef4444; }

        /* MAIN */
        .main { margin-left: var(--sidebar-w); min-height: 100vh; display: flex; flex-direction: column; }
        .topbar { 
            background: #fff; border-bottom: 1px solid #e2e8f0; padding: .75rem 2rem;
            display: flex; align-items: center; justify-content: space-between; 
            position: sticky; top:0; z-index:50; box-shadow: var(--shadow-sm); 
        }
        .topbar h1 { font-size: 1.1rem; font-weight: 700; color: #1e293b; }
        .content { padding: 2rem; flex: 1; }

        /* CARDS */
        .cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
        .card { 
            background: #fff; border-radius: var(--radius); padding: 1.5rem; 
            box-shadow: var(--shadow); border: 1px solid #f1f5f9; position: relative; overflow: hidden;
            transition: transform .2s ease;
        }
        .card:hover { transform: translateY(-2px); }
        .card::after { content: ""; position: absolute; left: 0; top: 0; height: 100%; width: 4px; background: var(--primary); }
        .card.green::after  { background: var(--success); }
        .card.red::after    { background: var(--danger); }
        .card.yellow::after { background: var(--warning); }
        
        .card-label { font-size: .7rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase; letter-spacing: .05em; margin-bottom: .25rem; }
        .card-value { font-size: 2rem; font-weight: 800; color: var(--text-main); letter-spacing: -0.02em; }
        .card-sub   { font-size: .75rem; color: #94a3b8; margin-top: .5rem; font-weight: 500; }

        /* TABLES */
        .table-wrap { 
            background: #fff; border-radius: var(--radius); box-shadow: var(--shadow); 
            overflow-x: auto; margin-bottom: 2rem; border: 1px solid #f1f5f9;
        }
        .table-head { 
            padding: 1.25rem 1.5rem; border-bottom: 1px solid #f1f5f9; 
            display: flex; align-items: center; justify-content: space-between;
        }
        .table-head h3 { font-size: .95rem; font-weight: 700; color: var(--text-main); }
        
        table { width: 100%; border-collapse: collapse; }
        th { 
            background: #f8fafc; padding: 1rem 1.5rem; text-align: left; 
            font-size: .7rem; font-weight: 700; color: var(--text-muted);
            text-transform: uppercase; letter-spacing: 0.05em; border-bottom: 1px solid #f1f5f9;
        }
        td { padding: 1rem 1.5rem; font-size: .875rem; color: #334155; border-bottom: 1px solid #f8fafc; }
        tr:hover td { background: #fdfdfd; }
        tr:last-child td { border-bottom: none; }

        /* BADGES */
        .badge { 
            display: inline-flex; align-items: center; padding: .25rem .75rem; 
            border-radius: 6px; font-size: .7rem; font-weight: 700; letter-spacing: 0.01em;
        }
        .badge-blue   { background: #e0e7ff; color: #4338ca; }
        .badge-green  { background: #d1fae5; color: #065f46; }
        .badge-red    { background: #fee2e2; color: #991b1b; }
        .badge-yellow { background: #fef3c7; color: #92400e; }
        .badge-gray   { background: #f1f5f9; color: #475569; }

        /* BUTTONS */
        .btn { 
            display: inline-flex; align-items: center; gap: .5rem; padding: .6rem 1.2rem; 
            border-radius: 8px; font-size: .825rem; font-weight: 600; cursor: pointer; 
            border: 1px solid transparent; transition: all .2s; text-decoration: none;
            box-shadow: var(--shadow-sm);
        }
        .btn-primary { background: var(--primary); color: #fff; }
        .btn-primary:hover { background: var(--primary-dk); transform: translateY(-1px); box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        
        .btn-outline { background: #fff; color: #475569; border-color: #e2e8f0; }
        .btn-outline:hover { background: #f8fafc; border-color: #cbd5e1; color: var(--text-main); }
        
        .btn-danger { background: #fee2e2; color: #ef4444; }
        .btn-danger:hover { background: #ef4444; color: #fff; }

        /* FORM */
        .form-group { margin-bottom: 1.25rem; }
        .form-group label { display: block; font-size: .825rem; font-weight: 600; margin-bottom: .5rem; color: #475569; }
        .form-control { 
            width: 100%; padding: .7rem 1rem; border: 1.5px solid #e2e8f0; border-radius: 8px;
            font-size: .9rem; background: #fff; transition: all .2s; outline: none;
        }
        .form-control:focus { border-color: var(--primary); box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1); }

        /* CHARTS */
        .chart-wrap { 
            background: #fff; border-radius: var(--radius); padding: 1.5rem; 
            box-shadow: var(--shadow); border: 1px solid #f1f5f9;
            display: flex; flex-direction: column; gap: 1rem;
            height: 100%;
        }
        .chart-wrap h3 { font-size: .95rem; font-weight: 700; color: var(--text-main); }
        .chart-wrap canvas { max-height: 280px !important; width: 100% !important; }

        /* GRID 2 cols */
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem; }
        @media (max-width: 1024px) { .grid-2 { grid-template-columns: 1fr; } }
        .menu-toggle { display: none; background: none; border: none; cursor: pointer; color: #64748b; padding: .5rem; border-radius: 8px; }
        .menu-toggle:hover { background: #f1f5f9; color: var(--text-main); }
        .overlay { position: fixed; top:0; left:0; width:100%; height:100%; background:rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); z-index: 90; display: none; opacity: 0; transition: opacity 0.3s; }

        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1); width: 280px; }
            .sidebar.open { transform: translateX(0); }
            .main { margin-left: 0; }
            .menu-toggle { display: flex; }
            .overlay.show { display: block; opacity: 1; }
            .content { padding: 1.25rem; }
            .topbar { padding: .75rem 1.25rem; }
        }
    </style>
    <script>
        function toggleMenu() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('overlay');
            sidebar.classList.toggle('open');
            overlay.classList.toggle('show');
        }
    </script>
</head>
<body>

<?php $usuario = $_SESSION['usuario'] ?? []; $role = $usuario['role'] ?? ''; ?>

<!-- OVERLAY (Mobile) -->
<div class="overlay" id="overlay" onclick="toggleMenu()"></div>

<!-- SIDEBAR -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <h2>📚 FrequenciaSmart</h2>
        <small>
            <?php if ($usuario['is_super_admin'] ?? false): ?>
                ADMINISTRATIVO MASTER
            <?php elseif ($usuario['escola_nome'] ?? false): ?>
                <?= e($usuario['escola_nome']) ?>
            <?php else: ?>
                SISTEMA DE FREQUÊNCIA
            <?php endif; ?>
        </small>
    </div>
    <nav onclick="if(window.innerWidth <= 768) toggleMenu()">
        <?php if (is_super_admin()): ?>
            <a href="/escolas" class="nav-item <?= rota_ativa('escolas') ?>">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                Gerir Escolas
            </a>
            <a href="/usuarios" class="nav-item <?= rota_ativa('usuarios') ?>">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                Usuários / Diretores
            </a>
        <?php else: ?>
            <?php if (tem_role('DIRETOR','VICE')): ?>
            <a href="/dashboard" class="nav-item <?= rota_ativa('dashboard') ?>">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
                Dashboard
            </a>
            <a href="/relatorios" class="nav-item <?= rota_ativa('relatorios') ?>">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 17v-2a4 4 0 00-4-4H5m11 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m4 6h-6m0 0l3-3m-3 3l3 3"/></svg>
                Relatórios
            </a>
            <?php endif; ?>

            <?php if (tem_role('DIRETOR','VICE','ORIENTADORA')): ?>
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

            <?php if (tem_role('DIRETOR','VICE','ORIENTADORA')): ?>
            <a href="/alunos" class="nav-item <?= rota_ativa('alunos*') ?>">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/></svg>
                Alunos
            </a>
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
        <?php endif; ?>
    </nav>

    <div class="sidebar-footer">
        <div class="user-card">
            <span>Logado como:</span>
            <strong title="<?= e($usuario['nome'] ?? 'Usuário') ?>">
                <?= e($usuario['nome'] ?? 'Usuário') ?>
            </strong>
            <div style="margin-top:.25rem">
                <?php if ($usuario['is_super_admin'] ?? false): ?>
                    <span class="badge" style="background:#fbbf24; color:#0f172a">MASTER</span>
                <?php else: ?>
                    <span class="badge badge-blue"><?= e($usuario['role'] ?? '') ?></span>
                <?php endif; ?>
            </div>
        </div>
        <form method="POST" action="/logout">
            <?php csrf_field(); ?>
            <button type="submit" class="logout-btn">⎋ Sair do Sistema</button>
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
        <div style="display:flex;align-items:center;gap:1.5rem">
            <span style="font-size:.875rem; font-weight:600; color:var(--text-muted)"><?= date('d/m/Y') ?></span>
        </div>
    </div>
    <div class="content">
        <?php if ($erro = get_flash('error')): ?>
            <div class="alert alert-error"><?= e($erro) ?></div>
        <?php endif; ?>
        <?php if ($sucesso = get_flash('success')): ?>
            <div class="alert alert-success"><?= e($sucesso) ?></div>
        <?php endif; ?>
        <!-- CONTEÚDO DA PÁGINA -->
