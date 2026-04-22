<?php
// pages/orientadora.php — Painel de alertas
requer_login();

$mes     = $_GET['mes']      ?? date('Y-m');
$turmaId = $_GET['turma_id'] ?? null;

$params = [escola_id(), $mes];
$where  = "WHERE al.escola_id = ? AND al.mes_referencia = ?";
if ($turmaId) {
    $where  .= " AND a.turma_id = ?";
    $params[] = $turmaId;
}

$alertas = db_all(
    "SELECT al.*, a.nome AS aluno_nome, a.matricula, t.nome AS turma_nome
     FROM alertas al
     JOIN alunos a ON a.id = al.aluno_id AND a.ativo = 1
     JOIN turmas t ON t.id = a.turma_id AND t.ativa = 1
     $where
     ORDER BY al.created_at DESC",
    $params
);

$turmas = db_all("SELECT * FROM turmas WHERE escola_id = ? AND ativa = 1 ORDER BY nome", [escola_id()]);

// Contadores
$totalAlertas     = count($alertas);
$totalConsec      = count(array_filter($alertas, fn($a) => $a->tipo === 'CONSECUTIVA'));
$totalIntercalada = count(array_filter($alertas, fn($a) => $a->tipo === 'INTERCALADA'));

$tituloPagina = 'Alertas de Frequência';
include __DIR__ . '/../layout/header.php';
?>

<!-- FILTROS -->
<div class="table-wrap" style="margin-bottom:1.5rem">
    <div class="table-head"><h3>🔍 Filtros</h3></div>
    <div style="padding:1rem 1.25rem">
        <form method="GET" action="/orientadora" style="display:flex;gap:1rem;flex-wrap:wrap;align-items:flex-end">
            <div class="form-group" style="margin:0;flex:1;min-width:160px">
                <label>Mês</label>
                <input type="month" name="mes" value="<?= e($mes) ?>" class="form-control">
            </div>
            <div class="form-group" style="margin:0;flex:1;min-width:160px">
                <label>Turma</label>
                <select name="turma_id" class="form-control">
                    <option value="">Todas</option>
                    <?php foreach ($turmas as $t): ?>
                    <option value="<?= e($t->id) ?>" <?= $turmaId == $t->id ? 'selected' : '' ?>><?= e($t->nome) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Filtrar</button>
            <a href="/orientadora" class="btn btn-outline">Limpar</a>
        </form>
    </div>
</div>

<!-- CARDS RESUMO -->
<div class="cards" style="margin-bottom:1.5rem">
    <div class="card red">
        <div class="card-label">Alertas no período</div>
        <div class="card-value"><?= e($totalAlertas) ?></div>
    </div>
    <div class="card yellow">
        <div class="card-label">Faltas consecutivas</div>
        <div class="card-value"><?= e($totalConsec) ?></div>
    </div>
    <div class="card">
        <div class="card-label">Faltas mensais (8+)</div>
        <div class="card-value"><?= e($totalIntercalada) ?></div>
    </div>
</div>

<!-- TABELA ALERTAS -->
<div class="table-wrap">
    <div class="table-head">
        <h3>🔔 Lista de Alertas</h3>
        <span style="font-size:.8rem;color:#64748b"><?= e($totalAlertas) ?> resultado(s)</span>
    </div>
    <table>
        <thead>
            <tr><th>Aluno</th><th>Matrícula</th><th>Turma</th><th>Tipo de Alerta</th><th>Mês</th><th>Gerado em</th><th style="text-align:right">Ações</th></tr>
        </thead>
        <tbody>
            <?php if (empty($alertas)): ?>
            <tr><td colspan="6" style="text-align:center;color:#94a3b8;padding:2rem">✅ Nenhum alerta para este período</td></tr>
            <?php else: foreach ($alertas as $alerta): ?>
            <tr>
                <td><strong><?= e($alerta->aluno_nome ?? '—') ?></strong></td>
                <td style="color:#64748b;font-size:.8rem"><?= e($alerta->matricula ?? '—') ?></td>
                <td><?= e($alerta->turma_nome ?? '—') ?></td>
                <td>
                    <?php if ($alerta->tipo === 'CONSECUTIVA'): ?>
                        <span class="badge badge-red">⚠️ 3 Consecutivas</span>
                    <?php else: ?>
                        <span class="badge badge-yellow">📊 8 Mensais</span>
                    <?php endif; ?>
                </td>
                <td><?= e($alerta->mes_referencia) ?></td>
                <td style="color:#64748b;font-size:.8rem"><?= fmt_datetime($alerta->created_at) ?></td>
                <td style="text-align:right">
                    <a href="/alertas/<?= e($alerta->id) ?>/imprimir" target="_blank" class="btn btn-outline" style="padding:.3rem .6rem; font-size:.75rem">
                        📄 Gerar Notificação
                    </a>
                </td>
            </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
