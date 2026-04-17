<?php
// pages/dashboard.php
requer_role('DIRETOR', 'VICE');

$mes = date('Y-m');

// Cards
$totalFaltasMes = db_one(
    "SELECT COUNT(*) AS total FROM frequencias WHERE escola_id = ? AND status = 'FALTA' AND DATE_FORMAT(data,'%Y-%m') = ?",
    [escola_id(), $mes]
)->total;

$totalAlertasAtivos = db_one(
    "SELECT COUNT(*) AS total FROM alertas WHERE escola_id = ? AND mes_referencia = ?",
    [escola_id(), $mes]
)->total;

$totalAlunos = db_one("SELECT COUNT(*) AS total FROM alunos WHERE escola_id = ? AND ativo = 1", [escola_id()])->total;
$totalTurmas = db_one("SELECT COUNT(*) AS total FROM turmas WHERE escola_id = ? AND ativa = 1", [escola_id()])->total;

// Ranking de faltas
$rankingFaltas = db_all(
    "SELECT f.aluno_id, COUNT(*) AS total, a.nome AS aluno_nome,
            t.nome AS turma_nome
     FROM frequencias f
     JOIN alunos a ON a.id = f.aluno_id
     LEFT JOIN turmas t ON t.id = a.turma_id
     WHERE f.escola_id = ? AND f.status = 'FALTA' AND DATE_FORMAT(f.data,'%Y-%m') = ?
     GROUP BY f.aluno_id
     ORDER BY total DESC
     LIMIT 10",
    [escola_id(), $mes]
);

// Faltas por turma (para gráfico)
$faltasPorTurma = db_all(
    "SELECT f.turma_id, COUNT(*) AS total, t.nome AS turma_nome
     FROM frequencias f
     JOIN turmas t ON t.id = f.turma_id
     WHERE f.escola_id = ? AND f.status = 'FALTA' AND DATE_FORMAT(f.data,'%Y-%m') = ?
     GROUP BY f.turma_id",
    [escola_id(), $mes]
);

// Alertas recentes
$alertasRecentes = db_all(
    "SELECT al.*, a.nome AS aluno_nome, a.matricula, t.nome AS turma_nome
     FROM alertas al
     JOIN alunos a ON a.id = al.aluno_id
     LEFT JOIN turmas t ON t.id = a.turma_id
     WHERE al.escola_id = ?
     ORDER BY al.created_at DESC
     LIMIT 10",
    [escola_id()]
);

$tituloPagina = 'Dashboard – Direção';
include __DIR__ . '/../layout/header.php';
?>

<!-- CARDS -->
<div class="cards">
    <div class="card red">
        <div class="card-label">Faltas no Mês</div>
        <div class="card-value"><?= e($totalFaltasMes) ?></div>
        <div class="card-sub"><?= date('m/Y') ?></div>
    </div>
    <div class="card yellow">
        <div class="card-label">Alertas Ativos</div>
        <div class="card-value"><?= e($totalAlertasAtivos) ?></div>
        <div class="card-sub">Este mês</div>
    </div>
    <div class="card green">
        <div class="card-label">Total de Alunos</div>
        <div class="card-value"><?= e($totalAlunos) ?></div>
        <div class="card-sub">Ativos</div>
    </div>
    <div class="card">
        <div class="card-label">Turmas</div>
        <div class="card-value"><?= e($totalTurmas) ?></div>
        <div class="card-sub">Ativas em <?= date('Y') ?></div>
    </div>
</div>

<!-- GRID -->
<div class="grid-2">

    <!-- RANKING FALTAS -->
    <div class="table-wrap">
        <div class="table-head"><h3>🏆 Ranking de Faltas no Mês</h3></div>
        <table>
            <thead>
                <tr><th>#</th><th>Aluno</th><th>Turma</th><th>Faltas</th></tr>
            </thead>
            <tbody>
                <?php if (empty($rankingFaltas)): ?>
                <tr><td colspan="4" style="text-align:center;color:#94a3b8;padding:1.5rem">Nenhuma falta registrada</td></tr>
                <?php else: foreach ($rankingFaltas as $i => $item): ?>
                <tr>
                    <td><?= $i + 1 ?>º</td>
                    <td><?= e($item->aluno_nome) ?></td>
                    <td><?= e($item->turma_nome ?? '—') ?></td>
                    <td><span class="badge badge-red"><?= e($item->total) ?></span></td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>

    <!-- GRÁFICO POR TURMA -->
    <div class="chart-wrap">
        <h3>📊 Faltas por Turma</h3>
        <canvas id="chartTurmas" height="200"></canvas>
    </div>

</div>

<!-- ALERTAS RECENTES -->
<div class="table-wrap">
    <div class="table-head">
        <h3>🔔 Alertas Recentes</h3>
        <a href="/orientadora" class="btn btn-outline" style="font-size:.75rem">Ver todos</a>
    </div>
    <table>
        <thead>
            <tr><th>Aluno</th><th>Turma</th><th>Tipo</th><th>Mês</th><th>Data</th></tr>
        </thead>
        <tbody>
            <?php if (empty($alertasRecentes)): ?>
            <tr><td colspan="5" style="text-align:center;color:#94a3b8;padding:1.5rem">Nenhum alerta gerado</td></tr>
            <?php else: foreach ($alertasRecentes as $alerta): ?>
            <tr>
                <td><?= e($alerta->aluno_nome ?? '—') ?></td>
                <td><?= e($alerta->turma_nome ?? '—') ?></td>
                <td>
                    <?php if ($alerta->tipo === 'CONSECUTIVA'): ?>
                        <span class="badge badge-red">3 consecutivas</span>
                    <?php else: ?>
                        <span class="badge badge-yellow">10 mensais</span>
                    <?php endif; ?>
                </td>
                <td><?= e($alerta->mes_referencia) ?></td>
                <td><?= fmt_datetime($alerta->created_at) ?></td>
            </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

<script>
const labels  = <?= json_encode(array_column($faltasPorTurma, 'turma_nome')) ?>;
const valores = <?= json_encode(array_column($faltasPorTurma, 'total')) ?>;
new Chart(document.getElementById('chartTurmas'), {
    type: 'bar',
    data: {
        labels,
        datasets: [{
            label: 'Faltas',
            data: valores,
            backgroundColor: '#6366f1',
            borderRadius: 6,
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
    }
});
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>
