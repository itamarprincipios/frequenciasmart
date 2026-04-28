<?php
// pages/frequencias.php
requer_login();

$turmaId = !empty($_GET['turma_id']) ? $_GET['turma_id'] : null;
$data    = !empty($_GET['data']) ? $_GET['data'] : date('Y-m-d');
$turno   = !empty($_GET['turno']) ? $_GET['turno'] : null;

$modoAcumulado = !empty($turmaId);


if ($modoAcumulado) {
    // VISÃO POR TURMA
    $dados = db_all(
        "SELECT a.id, a.nome as aluno_nome, 
                (SELECT f.status FROM frequencias f WHERE f.aluno_id = a.id AND f.data = ?) as status_hoje,
                (SELECT COUNT(*) FROM frequencias f WHERE f.aluno_id = a.id AND f.status = 'FALTA' AND f.data <= ?) as total_faltas
         FROM alunos a
         JOIN turmas t ON t.id = a.turma_id
         WHERE a.turma_id = ? AND a.ativo = 1
         AND (t.turno = ? OR ? IS NULL)
         ORDER BY a.nome",
        [$data, $data, $turmaId, $turno, $turno]
    );
    
    $totalGeral = count($dados);
    $presentes  = count(array_filter($dados, fn($d) => ($d->status_hoje ?? '') === 'PRESENTE'));
    $faltasHoje = count(array_filter($dados, fn($d) => ($d->status_hoje ?? '') === 'FALTA'));
    $naoLancado = $totalGeral - $presentes - $faltasHoje;
} else {
    // VISÃO GERAL: Resumo consolidado por Turma para o dia selecionado
    $dados = db_all(
        "SELECT t.id AS turma_id, t.nome AS turma_nome, t.turno,
                COUNT(CASE WHEN f.status = 'FALTA' THEN 1 END) AS total_faltas,
                COUNT(CASE WHEN f.status = 'PRESENTE' THEN 1 END) AS total_presencas,
                COUNT(*) AS total_registros
         FROM frequencias f
         JOIN turmas t ON t.id = f.turma_id
         WHERE f.escola_id = ? AND f.data = ?
         AND (t.turno = ? OR ? IS NULL)
         GROUP BY t.id, t.nome, t.turno
         ORDER BY t.nome ASC",
        [escola_id(), $data, $turno, $turno]
    );

    $totalGeral = count($dados);
    $presentes  = array_sum(array_column($dados, 'total_presencas'));
    $faltasHoje = array_sum(array_column($dados, 'total_faltas'));
}

// Turmas para o select (filtradas pelo turno se selecionado, para facilitar a busca)
$sqlTurmas = "SELECT * FROM turmas WHERE escola_id = ? AND ativa = 1";
$paramsTurmas = [escola_id()];
if ($turno) {
    $sqlTurmas .= " AND turno = ?";
    $paramsTurmas[] = $turno;
}
$turmas = db_all($sqlTurmas . " ORDER BY nome", $paramsTurmas);

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
            <div class="form-group" style="margin:0;flex:1;min-width:140px">
                <label>Turno</label>
                <select name="turno" class="form-control" onchange="this.form.submit()">
                    <option value="">-- Todos --</option>
                    <option value="MANHA" <?= ($turno ?? '') === 'MANHA' ? 'selected' : '' ?>>Matutino</option>
                    <option value="TARDE" <?= ($turno ?? '') === 'TARDE' ? 'selected' : '' ?>>Vespertino</option>
                </select>
            </div>
            <div class="form-group" style="margin:0;flex:1;min-width:160px">
                <label>Turma</label>
                <select name="turma_id" class="form-control" onchange="this.form.submit()">
                    <option value="">-- Todas as Turmas (Log) --</option>
                    <?php foreach ($turmas as $t): ?>
                    <option value="<?= e($t->id) ?>" <?= $turmaId == $t->id ? 'selected' : '' ?>>
                        <?= e($t->nome) ?> (<?= e($t->turno) ?>)
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Filtrar</button>
            <a href="/frequencias" class="btn btn-outline">Limpar Filtros</a>
        </form>
    </div>
</div>

<!-- RESUMO -->
<?php if ($totalGeral > 0 || $modoAcumulado): ?>
<div class="cards" style="margin-bottom:1.5rem">
    <div class="card green">
        <div class="card-label">Presentes (<?= e(fmt_data($data)) ?>)</div>
        <div class="card-value"><?= $presentes ?></div>
        <div class="card-sub"><?= $totalGeral > 0 ? round(($presentes/$totalGeral)*100) : 0 ?>% de presença</div>
    </div>
    <div class="card red">
        <div class="card-label">Faltas Hoje</div>
        <div class="card-value"><?= $faltasHoje ?></div>
    </div>
    <div class="card yellow">
        <div class="card-label"><?= $modoAcumulado ? 'Não Lançados' : 'Total Registros' ?></div>
        <div class="card-value"><?= $modoAcumulado ? $naoLancado : $totalGeral ?></div>
    </div>
</div>
<?php endif; ?>

<!-- TABELA -->
<div class="table-wrap">
    <div class="table-head">
        <h3>
            <?= $modoAcumulado 
                ? '📊 Panorama da Turma' 
                : '📋 Registros de ' . e(fmt_data($data)) 
            ?>
        </h3>
        <span style="font-size:.8rem;color:#64748b"><?= $totalGeral ?> aluno(s)/registro(s)</span>
    </div>
    <table>
        <thead>
            <?php if ($modoAcumulado): ?>
                <tr>
                    <th>Aluno</th>
                    <th style="text-align:center">Presença Hoje</th>
                    <th style="text-align:center">Faltas Acumuladas</th>
                    <th style="text-align:center">Situação</th>
                    <th style="text-align:center">Ação Rápida</th>
                </tr>
            <?php else: ?>
                <tr>
                    <th>Turma</th>
                    <th style="text-align:center">Turno</th>
                    <th style="text-align:center">Alunos Presentes</th>
                    <th style="text-align:center">Alunos Faltantes</th>
                </tr>
            <?php endif; ?>
        </thead>
        <tbody>
            <?php if (empty($dados)): ?>
            <tr><td colspan="5" style="text-align:center;color:#94a3b8;padding:2rem">Nenhum dado encontrado para os filtros selecionados</td></tr>
            <?php else: foreach ($dados as $d): ?>
            <tr>
                <?php if ($modoAcumulado): ?>
                    <td><strong><?= e($d->aluno_nome ?? '—') ?></strong></td>
                    <td style="text-align:center">
                        <?php if ($d->status_hoje === 'PRESENTE'): ?>
                            <span class="badge badge-green">Presente</span>
                        <?php elseif ($d->status_hoje === 'FALTA'): ?>
                            <span class="badge badge-red">Falta</span>
                        <?php else: ?>
                            <span class="badge badge-gray">Não Lançado</span>
                        <?php endif; ?>
                    </td>
                    <td style="text-align:center">
                        <strong style="font-size:1.1rem; <?= $d->total_faltas > 5 ? 'color:var(--danger)' : '' ?>">
                            <?= $d->total_faltas ?>
                        </strong>
                    </td>
                    <td style="text-align:center">
                        <?php if ($d->total_faltas >= 5): ?>
                            <span class="badge badge-red" style="background:#fef2f2; color:#b91c1c; border:1px solid #fee2e2">CRÍTICO</span>
                        <?php elseif ($d->total_faltas >= 3): ?>
                            <span class="badge badge-yellow" style="background:#fffbeb; color:#b45309; border:1px solid #fef3c7">ALERTA</span>
                        <?php else: ?>
                            <span class="badge badge-green" style="background:#f0fdf4; color:#15803d; border:1px solid #dcfce7">OK</span>
                        <?php endif; ?>
                    </td>
                    <td style="text-align:center">
                        <form method="POST" action="/frequencia/manual" style="display:inline-block;">
                            <?php csrf_field(); ?>
                            <input type="hidden" name="aluno_id" value="<?= $d->id ?>">
                            <input type="hidden" name="turma_id" value="<?= $turmaId ?>">
                            <input type="hidden" name="data" value="<?= $data ?>">
                            <?php if ($d->status_hoje === 'PRESENTE'): ?>
                                <input type="hidden" name="status" value="FALTA">
                                <button type="submit" class="btn btn-outline" style="padding:.2rem .5rem; font-size:.65rem; color:var(--danger)">✗ Dar Falta</button>
                            <?php else: ?>
                                <input type="hidden" name="status" value="PRESENTE">
                                <button type="submit" class="btn btn-outline" style="padding:.2rem .5rem; font-size:.65rem; color:var(--success)">✓ Dar Presença</button>
                            <?php endif; ?>
                        </form>
                    </td>
                <?php else: ?>
                    <td><strong><?= e($d->turma_nome ?? '—') ?></strong></td>
                    <td style="text-align:center"><span class="badge badge-blue"><?= e($d->turno ?? '—') ?></span></td>
                    <td style="text-align:center"><strong style="color:var(--success)"><?= e($d->total_presencas) ?></strong></td>
                    <td style="text-align:center"><strong style="color:var(--danger)"><?= e($d->total_faltas) ?></strong></td>
                <?php endif; ?>
            </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
