<?php
// pages/frequencias.php
requer_login();

$turmaId = $_GET['turma_id'] ?? null;
$data    = $_GET['data'] ?? date('Y-m-d');

$modoAcumulado = !empty($turmaId);

if ($modoAcumulado) {
    // VISÃO POR TURMA: Todos os alunos + Faltas acumuladas + Status do dia selecionado
    $dados = db_all(
        "SELECT a.id, a.nome as aluno_nome, 
                (SELECT f.status FROM frequencias f WHERE f.aluno_id = a.id AND f.data = ?) as status_hoje,
                (SELECT COUNT(*) FROM frequencias f WHERE f.aluno_id = a.id AND f.status = 'FALTA' AND f.data <= ?) as total_faltas
         FROM alunos a
         WHERE a.turma_id = ? AND a.ativo = 1
         ORDER BY a.nome",
        [$data, $data, $turmaId]
    );
    
    $totalGeral = count($dados);
    $presentes  = count(array_filter($dados, fn($d) => ($d->status_hoje ?? '') === 'PRESENTE'));
    $faltasHoje = count(array_filter($dados, fn($d) => ($d->status_hoje ?? '') === 'FALTA'));
    $naoLancado = $totalGeral - $presentes - $faltasHoje;
} else {
    // VISÃO GERAL: Log de registros do dia selecionado em toda a escola
    $dados = db_all(
        "SELECT f.*, a.nome AS aluno_nome, t.nome AS turma_nome, u.nome AS registrador_nome
         FROM frequencias f
         JOIN alunos a ON a.id = f.aluno_id
         JOIN turmas t ON t.id = f.turma_id
         LEFT JOIN users u ON u.id = f.registrado_por
         WHERE f.escola_id = ? AND f.data = ?
         ORDER BY f.created_at DESC",
        [escola_id(), $data]
    );

    $totalGeral = count($dados);
    $presentes  = count(array_filter($dados, fn($d) => ($d->status ?? '') === 'PRESENTE'));
    $faltasHoje = count(array_filter($dados, fn($d) => ($d->status ?? '') === 'FALTA'));
}

$turmas = db_all("SELECT * FROM turmas WHERE escola_id = ? AND ativa = 1", [escola_id()]);

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
                <select name="turma_id" class="form-control" onchange="this.form.submit()">
                    <option value="">-- Todas as Turmas (Visão de Log) --</option>
                    <?php foreach ($turmas as $t): ?>
                    <option value="<?= e($t->id) ?>" <?= $turmaId == $t->id ? 'selected' : '' ?>><?= e($t->nome) ?></option>
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
                </tr>
            <?php else: ?>
                <tr>
                    <th>Aluno</th>
                    <th>Turma</th>
                    <th>Status</th>
                    <th>Registrado por</th>
                    <th>Hora</th>
                </tr>
            <?php endif; ?>
        </thead>
        <tbody>
            <?php if (empty($dados)): ?>
            <tr><td colspan="5" style="text-align:center;color:#94a3b8;padding:2rem">Nenhum dado encontrado para os filtros selecionados</td></tr>
            <?php else: foreach ($dados as $d): ?>
            <tr>
                <td><strong><?= e($d->aluno_nome ?? '—') ?></strong></td>
                
                <?php if ($modoAcumulado): ?>
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
                <?php else: ?>
                    <td><?= e($d->turma_nome ?? '—') ?></td>
                    <td>
                        <?php if ($d->status === 'PRESENTE'): ?>
                            <span class="badge badge-green">✓ Presente</span>
                        <?php else: ?>
                            <span class="badge badge-red">✗ Falta</span>
                        <?php endif; ?>
                    </td>
                    <td style="color:#64748b;font-size:.8rem"><?= e($d->registrador_nome ?? '—') ?></td>
                    <td style="color:#94a3b8;font-size:.8rem"><?= date('H:i', strtotime($d->created_at)) ?></td>
                <?php endif; ?>
            </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
