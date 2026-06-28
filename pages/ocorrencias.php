<?php
// pages/ocorrencias.php — Histórico e listagem de ocorrências disciplinares
requer_login();
requer_role('DIRETOR', 'VICE', 'ORIENTADORA');

$turmaId = $_GET['turma_id'] ?? null;
$tipo    = $_GET['tipo'] ?? null;
$busca   = $_GET['busca'] ?? null;

$where  = "WHERE o.escola_id = ?";
$params = [escola_id()];

if ($turmaId) {
    $where  .= " AND a.turma_id = ?";
    $params[] = $turmaId;
}
if ($tipo) {
    $where  .= " AND o.tipo = ?";
    $params[] = $tipo;
}
if ($busca) {
    $where  .= " AND a.nome LIKE ?";
    $params[] = "%$busca%";
}

$ocorrencias = db_all(
    "SELECT o.*, a.nome AS aluno_nome, t.nome AS turma_nome, u.nome AS usuario_nome
     FROM ocorrencias_disciplinares o
     JOIN alunos a ON a.id = o.aluno_id
     LEFT JOIN turmas t ON t.id = o.turma_id
     LEFT JOIN users u ON u.id = o.registrado_por
     $where
     ORDER BY o.data_ocorrencia DESC, o.created_at DESC",
    $params
);

// Métricas rápidas no topo
$stats = db_one(
    "SELECT 
        COUNT(*) as total,
        COUNT(CASE WHEN tipo = 'INDISCIPLINA_PROFESSOR' THEN 1 END) as indisciplina,
        COUNT(CASE WHEN tipo = 'RECUSA_ATIVIDADE' THEN 1 END) as recusa,
        COUNT(CASE WHEN tipo = 'BRIGA' THEN 1 END) as briga,
        COUNT(CASE WHEN tipo = 'FURTO' THEN 1 END) as furto,
        COUNT(CASE WHEN tipo = 'OUTRO' THEN 1 END) as outro
     FROM ocorrencias_disciplinares 
     WHERE escola_id = ?",
    [escola_id()]
);

$turmas = db_all("SELECT * FROM turmas WHERE escola_id = ? AND ativa = 1 ORDER BY nome", [escola_id()]);

function fmt_tipo_ocorrencia($tipo) {
    switch ($tipo) {
        case 'INDISCIPLINA_PROFESSOR': return ['label' => 'Indisciplina com Prof.', 'class' => 'badge-red'];
        case 'RECUSA_ATIVIDADE':       return ['label' => 'Recusa em Sala', 'class' => 'badge-yellow'];
        case 'BRIGA':                  return ['label' => 'Briga / Conflito', 'class' => 'badge-red'];
        case 'FURTO':                  return ['label' => 'Furto / Subtração', 'class' => 'badge-red'];
        default:                       return ['label' => 'Outra Ocorrência', 'class' => 'badge-gray'];
    }
}

$tituloPagina = 'Ocorrências Disciplinares';
include __DIR__ . '/../layout/header.php';
?>

<!-- METRICAS -->
<div class="cards" style="margin-bottom: 1.5rem">
    <div class="card">
        <div class="card-label">Total Registrado</div>
        <div class="card-value"><?= (int)($stats->total ?? 0) ?></div>
        <div class="card-sub">Ocorrências disciplinares</div>
    </div>
    <div class="card red">
        <div class="card-label">Brigas & Furtos</div>
        <div class="card-value"><?= (int)($stats->briga ?? 0) + (int)($stats->furto ?? 0) ?></div>
        <div class="card-sub">Casos de maior gravidade</div>
    </div>
    <div class="card yellow">
        <div class="card-label">Indisciplina / Recusa</div>
        <div class="card-value"><?= (int)($stats->indisciplina ?? 0) + (int)($stats->recusa ?? 0) ?></div>
        <div class="card-sub">Casos em sala de aula</div>
    </div>
</div>

<!-- FILTROS -->
<div class="table-wrap" style="margin-bottom:1.5rem">
    <div class="table-head"><h3>🔍 Filtros de Busca</h3></div>
    <div style="padding:1rem 1.25rem">
        <form method="GET" action="/ocorrencias" style="display:flex;gap:1rem;flex-wrap:wrap;align-items:flex-end">
            <div class="form-group" style="margin:0;flex:2;min-width:200px">
                <label>Aluno</label>
                <input type="text" name="busca" value="<?= e($busca) ?>" class="form-control" placeholder="Buscar aluno por nome...">
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
            <div class="form-group" style="margin:0;flex:1;min-width:180px">
                <label>Tipo</label>
                <select name="tipo" class="form-control">
                    <option value="">Todos os tipos</option>
                    <option value="INDISCIPLINA_PROFESSOR" <?= $tipo === 'INDISCIPLINA_PROFESSOR' ? 'selected' : '' ?>>Indisciplina com Professor</option>
                    <option value="RECUSA_ATIVIDADE" <?= $tipo === 'RECUSA_ATIVIDADE' ? 'selected' : '' ?>>Recusa de Atividades</option>
                    <option value="BRIGA" <?= $tipo === 'BRIGA' ? 'selected' : '' ?>>Briga / Agressão</option>
                    <option value="FURTO" <?= $tipo === 'FURTO' ? 'selected' : '' ?>>Subtração / Furto</option>
                    <option value="OUTRO" <?= $tipo === 'OUTRO' ? 'selected' : '' ?>>Outros</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Filtrar</button>
            <a href="/ocorrencias" class="btn btn-outline">Limpar</a>
        </form>
    </div>
</div>

<!-- TABELA -->
<div class="table-wrap">
    <div class="table-head">
        <h3>📋 Livro Registro de Ocorrências (<?= count($ocorrencias) ?>)</h3>
        <a href="/ocorrencias/criar" class="btn btn-primary">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4"/></svg>
            Registrar Ocorrência
        </a>
    </div>
    <table>
        <thead>
            <tr>
                <th style="width: 25%">Aluno / Turma</th>
                <th style="width: 15%">Data</th>
                <th style="width: 20%">Classificação</th>
                <th style="width: 25%">Medida Adotada</th>
                <th style="text-align:center; width: 15%">Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($ocorrencias)): ?>
            <tr>
                <td colspan="5" style="text-align:center;color:#94a3b8;padding:2rem">
                    Nenhuma ocorrência disciplinar registrada até o momento.
                </td>
            </tr>
            <?php else: foreach ($ocorrencias as $o): 
                $tipoFmt = fmt_tipo_ocorrencia($o->tipo);
            ?>
            <tr>
                <td>
                    <strong><?= e($o->aluno_nome) ?></strong>
                    <div style="font-size:.75rem;color:#94a3b8"><?= e($o->turma_nome ?? 'Sem Turma') ?></div>
                </td>
                <td>
                    <strong><?= fmt_data($o->data_ocorrencia) ?></strong>
                </td>
                <td>
                    <span class="badge <?= $tipoFmt['class'] ?>"><?= e($tipoFmt['label']) ?></span>
                </td>
                <td>
                    <span style="font-size: .85rem; font-weight: 500; color: #475569;">
                        <?= e($o->medida_tomada ?: 'Nenhuma registrada') ?>
                    </span>
                </td>
                <td>
                    <div style="display:flex;gap:.4rem;justify-content:center;align-items:center;">
                        <a href="/ocorrencias/<?= e($o->id) ?>/imprimir" target="_blank"
                           class="btn btn-outline" style="font-size:.7rem;padding:.3rem .5rem" title="Imprimir Relatório">
                            🖨️ Imprimir
                        </a>
                        <?php if (tem_role('DIRETOR', 'VICE')): ?>
                        <form method="POST" action="/ocorrencias/<?= e($o->id) ?>/excluir"
                               onsubmit="return confirm('Deseja realmente excluir este registro de ocorrência? Esta ação é permanente.')">
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
