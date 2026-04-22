<?php
// pages/escolas_index.php — Painel Master
requer_super_admin();

$escolas = db_all(
    "SELECT e.*,
            (SELECT COUNT(*) FROM users u WHERE u.escola_id = e.id) AS users_count,
            (SELECT COUNT(*) FROM alunos a WHERE a.escola_id = e.id) AS alunos_count,
            (SELECT COUNT(*) FROM users u WHERE u.escola_id = e.id AND u.role = 'DIRETOR') AS diretores_count
     FROM escolas e
     ORDER BY e.ativa DESC, e.nome"
);

// Totalizadores
$totalEscolas  = count($escolas);
$totalAtivas   = count(array_filter($escolas, fn($e) => $e->ativa));
$totalAlunos   = array_sum(array_column($escolas, 'alunos_count'));
$totalUsuarios = array_sum(array_column($escolas, 'users_count'));

$tituloPagina = 'Gerenciamento de Escolas';
include __DIR__ . '/../layout/header.php';
?>

<!-- CARDS DE RESUMO -->
<div class="cards" style="margin-bottom:1.5rem;">
    <div class="card">
        <div class="card-label">Total de Escolas</div>
        <div class="card-value"><?= $totalEscolas ?></div>
        <div class="card-sub"><?= $totalAtivas ?> ativa(s)</div>
    </div>
    <div class="card green">
        <div class="card-label">Total de Alunos</div>
        <div class="card-value"><?= number_format($totalAlunos) ?></div>
        <div class="card-sub">em todas as escolas</div>
    </div>
    <div class="card" style="border-color:#8b5cf6">
        <div class="card-label">Total de Usuários</div>
        <div class="card-value"><?= $totalUsuarios ?></div>
        <div class="card-sub">gestor(es) e equipes</div>
    </div>
</div>

<!-- TABELA -->
<div class="table-wrap">
    <div class="table-head">
        <h3>🏢 Escolas Cadastradas no Sistema</h3>
        <a href="/escolas/criar" class="btn btn-primary">+ Nova Escola</a>
    </div>
    <table>
        <thead>
            <tr>
                <th>Nome da Escola</th>
                <th>Slug</th>
                <th style="text-align:center">Usuários</th>
                <th style="text-align:center">Alunos</th>
                <th style="text-align:center">Status</th>
                <th>Criada em</th>
                <th style="text-align:center">Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($escolas)): ?>
            <tr><td colspan="7" style="text-align:center;color:#94a3b8;padding:2rem">
                Nenhuma escola cadastrada ainda. <a href="/escolas/criar" style="color:#4f46e5">Criar primeira escola →</a>
            </td></tr>
            <?php else: foreach ($escolas as $e): ?>
            <tr>
                <td><strong><?= e($e->nome) ?></strong></td>
                <td><code style="background:#f1f5f9;padding:.15rem .4rem;border-radius:4px;font-size:.75rem"><?= e($e->slug) ?></code></td>
                <td style="text-align:center"><?= $e->users_count ?></td>
                <td style="text-align:center"><?= number_format($e->alunos_count) ?></td>
                <td style="text-align:center">
                    <?php if ($e->ativa): ?>
                        <span class="badge badge-green">● ATIVA</span>
                    <?php else: ?>
                        <span class="badge badge-red">● BLOQUEADA</span>
                    <?php endif; ?>
                </td>
                <td style="color:#94a3b8;font-size:.8rem"><?= fmt_data($e->created_at) ?></td>
                <td>
                    <div style="display:flex;gap:.4rem;justify-content:center">
                        <a href="/escolas/<?= $e->id ?>/editar" class="btn btn-outline" style="padding:.3rem .7rem;font-size:.75rem">
                            ✏️ Editar
                        </a>
                        <form method="POST" action="/escolas/<?= $e->id ?>/toggle"
                              onsubmit="return confirm('<?= $e->ativa ? 'Bloquear' : 'Ativar' ?> a escola «<?= e($e->nome) ?>»?')">
                            <?php csrf_field(); ?>
                            <button type="submit" class="btn <?= $e->ativa ? 'btn-danger' : 'btn-primary' ?>"
                                    style="padding:.3rem .7rem;font-size:.75rem">
                                <?= $e->ativa ? '🔴 Bloquear' : '✅ Ativar' ?>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
