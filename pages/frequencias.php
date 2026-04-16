<?php
// pages/frequencias.php
requer_login();

$turmaId = $_GET['turma_id'] ?? null;
$data    = $_GET['data'] ?? date('Y-m-d');

$params = [$data];
$where  = "WHERE f.data = ?";
if ($turmaId) {
    $where  .= " AND f.turma_id = ?";
    $params[] = $turmaId;
}

$frequencias = db_all(
    "SELECT f.*, a.nome AS aluno_nome, t.nome AS turma_nome, u.nome AS registrador_nome
     FROM frequencias f
     JOIN alunos a ON a.id = f.aluno_id
     JOIN turmas t ON t.id = f.turma_id
     LEFT JOIN users u ON u.id = f.registrado_por
     $where
     ORDER BY f.created_at DESC",
    $params
);

$turmas = db_all("SELECT * FROM turmas WHERE ativa = 1");

$presentes = count(array_filter($frequencias, fn($f) => $f->status === 'PRESENTE'));
$faltas    = count(array_filter($frequencias, fn($f) => $f->status === 'FALTA'));
$total     = count($frequencias);

$tituloPagina = 'Frequências';
include __DIR__ . '/../layout/header.php';
?>

<!-- FILTROS -->
<div class="table-wrap" style="margin-bottom:1.5rem">
    <div class="table-head"><h3>🔍 Filtrar Frequências</h3></div>
    <div style="padding:1rem 1.25rem">
        <form method="GET" action="/frequencias" style="display:flex;gap:1rem;flex-wrap:wrap;align-items:flex-end">
            <div class="form-group" style="margin:0;flex:1;min-width:160px">
                <label>Data</label>
                <input type="date" name="data" value="<?= e($data) ?>" class="form-control">
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
            <a href="/frequencias" class="btn btn-outline">Hoje</a>
        </form>
    </div>
</div>

<!-- RESUMO -->
<?php if ($total > 0): ?>
<div class="cards" style="margin-bottom:1.5rem">
    <div class="card green">
        <div class="card-label">Presentes</div>
        <div class="card-value"><?= e($presentes) ?></div>
        <div class="card-sub"><?= $total > 0 ? round(($presentes/$total)*100) : 0 ?>% da turma</div>
    </div>
    <div class="card red">
        <div class="card-label">Faltas</div>
        <div class="card-value"><?= e($faltas) ?></div>
    </div>
    <div class="card">
        <div class="card-label">Total registrado</div>
        <div class="card-value"><?= e($total) ?></div>
    </div>
</div>
<?php endif; ?>

<!-- TABELA -->
<div class="table-wrap">
    <div class="table-head">
        <h3>📋 Registros de <?= e(fmt_data($data)) ?></h3>
        <span style="font-size:.8rem;color:#64748b"><?= e($total) ?> registro(s)</span>
    </div>
    <table>
        <thead>
            <tr><th>Aluno</th><th>Turma</th><th>Status</th><th>Registrado por</th><th>Hora</th></tr>
        </thead>
        <tbody>
            <?php if (empty($frequencias)): ?>
            <tr><td colspan="5" style="text-align:center;color:#94a3b8;padding:2rem">Nenhuma frequência registrada para este período</td></tr>
            <?php else: foreach ($frequencias as $f): ?>
            <tr>
                <td><strong><?= e($f->aluno_nome ?? '—') ?></strong></td>
                <td><?= e($f->turma_nome ?? '—') ?></td>
                <td>
                    <?php if ($f->status === 'PRESENTE'): ?>
                        <span class="badge badge-green">✓ Presente</span>
                    <?php else: ?>
                        <span class="badge badge-red">✗ Falta</span>
                    <?php endif; ?>
                </td>
                <td style="color:#64748b;font-size:.8rem"><?= e($f->registrador_nome ?? '—') ?></td>
                <td style="color:#94a3b8;font-size:.8rem"><?= date('H:i', strtotime($f->created_at)) ?></td>
            </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
