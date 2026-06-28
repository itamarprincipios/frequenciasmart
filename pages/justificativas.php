<?php
// pages/justificativas.php — Listar justificativas de faltas
requer_login();
requer_role('DIRETOR', 'VICE', 'ORIENTADORA');

$turmaId = $_GET['turma_id'] ?? null;
$busca   = $_GET['busca'] ?? null;

$where  = "WHERE j.escola_id = ?";
$params = [escola_id()];

if ($turmaId) {
    $where  .= " AND a.turma_id = ?";
    $params[] = $turmaId;
}
if ($busca) {
    $where  .= " AND (a.nome LIKE ? OR j.responsavel_nome LIKE ?)";
    $params[] = "%$busca%";
    $params[] = "%$busca%";
}

$justificativas = db_all(
    "SELECT j.*, a.nome AS aluno_nome, t.nome AS turma_nome, f.data AS data_falta, u.nome AS usuario_nome
     FROM justificativas_faltas j
     JOIN alunos a ON a.id = j.aluno_id
     LEFT JOIN turmas t ON t.id = a.turma_id
     JOIN frequencias f ON f.id = j.frequencia_id
     LEFT JOIN users u ON u.id = j.registrado_por
     $where
     ORDER BY j.created_at DESC",
    $params
);

$turmas = db_all("SELECT * FROM turmas WHERE escola_id = ? AND ativa = 1 ORDER BY nome", [escola_id()]);

$tituloPagina = 'Justificativas de Faltas';
include __DIR__ . '/../layout/header.php';
?>

<!-- FILTROS -->
<div class="table-wrap" style="margin-bottom:1.5rem">
    <div class="table-head"><h3>🔍 Filtros</h3></div>
    <div style="padding:1rem 1.25rem">
        <form method="GET" action="/justificativas" style="display:flex;gap:1rem;flex-wrap:wrap;align-items:flex-end">
            <div class="form-group" style="margin:0;flex:2;min-width:200px">
                <label>Buscar aluno ou responsável</label>
                <input type="text" name="busca" value="<?= e($busca) ?>" class="form-control" placeholder="Nome do aluno ou pai/mãe...">
            </div>
            <div class="form-group" style="margin:0;flex:1;min-width:180px">
                <label>Turma</label>
                <select name="turma_id" class="form-control">
                    <option value="">Todas as turmas</option>
                    <?php foreach ($turmas as $t): ?>
                    <option value="<?= e($t->id) ?>" <?= $turmaId == $t->id ? 'selected' : '' ?>><?= e($t->nome) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Filtrar</button>
            <a href="/justificativas" class="btn btn-outline">Limpar</a>
        </form>
    </div>
</div>

<!-- TABELA -->
<div class="table-wrap">
    <div class="table-head">
        <h3>📝 Registro de Justificativas (<?= count($justificativas) ?>)</h3>
        <a href="/justificativas/criar" class="btn btn-primary">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4"/></svg>
            Nova Justificativa
        </a>
    </div>
    <table>
        <thead>
            <tr>
                <th style="width: 25%">Aluno</th>
                <th style="width: 15%">Data da Falta</th>
                <th style="width: 25%">Responsável</th>
                <th style="width: 20%">Motivo</th>
                <th style="text-align:center; width: 15%">Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($justificativas)): ?>
            <tr>
                <td colspan="5" style="text-align:center;color:#94a3b8;padding:2rem">
                    Nenhuma justificativa encontrada.
                </td>
            </tr>
            <?php else: foreach ($justificativas as $j): ?>
            <tr>
                <td>
                    <strong><?= e($j->aluno_nome) ?></strong>
                    <div style="font-size:.75rem;color:#94a3b8"><?= e($j->turma_nome) ?></div>
                </td>
                <td>
                    <span class="badge badge-green"><?= fmt_data($j->data_falta) ?></span>
                </td>
                <td>
                    <strong><?= e($j->responsavel_nome) ?></strong>
                    <div style="font-size:.75rem;color:#94a3b8"><?= e($j->parentesco) ?> (visita em <?= fmt_data($j->data_visita) ?>)</div>
                </td>
                <td>
                    <span title="<?= e($j->motivo) ?>"><?= e(mb_strimwidth($j->motivo, 0, 40, "...")) ?></span>
                </td>
                <td>
                    <div style="display:flex;gap:.4rem;justify-content:center;align-items:center;">
                        <a href="/justificativas/<?= e($j->id) ?>/imprimir" target="_blank"
                           class="btn btn-outline" style="font-size:.7rem;padding:.3rem .5rem" title="Imprimir Termo">
                            🖨️ Imprimir
                        </a>
                        <?php if (tem_role('DIRETOR', 'VICE')): ?>
                        <form method="POST" action="/justificativas/<?= e($j->id) ?>/excluir"
                               onsubmit="return confirm('Deseja excluir a justificativa de <?= e($j->aluno_nome) ?> e restaurar a falta?')">
                            <?php csrf_field(); ?>
                            <button type="submit" class="btn btn-danger"
                                    style="font-size:.7rem;padding:.3rem .5rem" title="Excluir">🗑️</button>
                        </form>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
