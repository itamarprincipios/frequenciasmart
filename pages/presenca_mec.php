<?php
// pages/presenca_mec.php — Relatório Bimestral do Sistema Presença (Bolsa Família / MEC)
// Decreto nº 12.064/2024, art. 39 — Portaria MDS nº 1.058/2025
requer_login();
requer_role('DIRETOR', 'VICE');

// Constante de dias letivos anuais (padrão LDB)
define('DIAS_LETIVOS_ANUAIS', 200);
define('FREQUENCIA_MINIMA_PBF', 0.75); // 75% para 6 a 18 anos

// Determinar bimestre selecionado
$bimestreParam = $_GET['bimestre'] ?? null;
$ano = $_GET['ano'] ?? date('Y');

// Mapeamento de bimestres para meses
$bimestres = [
    '1' => ['label' => '1º Bimestre', 'meses' => ['02', '03', '04'], 'inicio' => "$ano-02-01", 'fim' => "$ano-04-30"],
    '2' => ['label' => '2º Bimestre', 'meses' => ['05', '06', '07'], 'inicio' => "$ano-05-01", 'fim' => "$ano-07-31"],
    '3' => ['label' => '3º Bimestre', 'meses' => ['08', '09'],       'inicio' => "$ano-08-01", 'fim' => "$ano-09-30"],
    '4' => ['label' => '4º Bimestre', 'meses' => ['10', '11', '12'], 'inicio' => "$ano-10-01", 'fim' => "$ano-12-31"],
];

// Detectar bimestre atual se não informado
if (!$bimestreParam) {
    $mesAtual = (int)date('m');
    if ($mesAtual <= 4) $bimestreParam = '1';
    elseif ($mesAtual <= 7) $bimestreParam = '2';
    elseif ($mesAtual <= 9) $bimestreParam = '3';
    else $bimestreParam = '4';
}

$bimSel = $bimestres[$bimestreParam] ?? $bimestres['1'];
$dataInicio = $bimSel['inicio'];
$dataFim    = $bimSel['fim'];

// Dias letivos do bimestre (proporcional: 200 dias / 4 bimestres ≈ 50 dias)
// Para maior precisão, usamos os dias registrados no sistema no período
$diasLetivos = db_one(
    "SELECT COUNT(DISTINCT data) AS total FROM frequencias 
     WHERE escola_id = ? AND data BETWEEN ? AND ?",
    [escola_id(), $dataInicio, $dataFim]
)->total;

if (!$diasLetivos) $diasLetivos = 50; // fallback: 50 dias por bimestre

$escola = db_one("SELECT * FROM escolas WHERE id = ?", [escola_id()]);

// Buscar todos os alunos ativos com suas frequências no bimestre
$alunos = db_all(
    "SELECT 
        a.id, a.nome, a.matricula, a.data_nascimento,
        a.responsavel_nome, a.responsavel_cpf, a.responsavel_telefone,
        t.nome AS turma_nome, t.turno,
        COUNT(CASE WHEN f.status = 'PRESENTE' THEN 1 END) AS presencas,
        COUNT(CASE WHEN f.status = 'FALTA' THEN 1 END) AS faltas,
        COUNT(f.id) AS total_registros
     FROM alunos a
     JOIN turmas t ON t.id = a.turma_id AND t.ativa = 1
     LEFT JOIN frequencias f ON f.aluno_id = a.id AND f.data BETWEEN ? AND ?
     WHERE a.escola_id = ? AND a.ativo = 1
     GROUP BY a.id
     ORDER BY t.nome, a.nome",
    [$dataInicio, $dataFim, escola_id()]
);

// Calcular percentual de frequência para cada aluno
foreach ($alunos as &$aluno) {
    $aluno->pct_frequencia = $diasLetivos > 0 
        ? round(($aluno->presencas / $diasLetivos) * 100, 1)
        : 0;
    $aluno->atende_pbf = $aluno->pct_frequencia >= (FREQUENCIA_MINIMA_PBF * 100);
    $aluno->nao_lancado = $diasLetivos - $aluno->total_registros;
}
unset($aluno);

$totalAlunos       = count($alunos);
$totalAtendePBF    = count(array_filter($alunos, fn($a) => $a->atende_pbf));
$totalNaoAtendePBF = $totalAlunos - $totalAtendePBF;

$tituloPagina = 'Sistema Presença — Bolsa Família';
include __DIR__ . '/../layout/header.php';
?>

<!-- SELETOR DE BIMESTRE -->
<div class="table-wrap" style="margin-bottom:1.5rem">
    <div class="table-head"><h3>📋 Relatório Bimestral — Sistema Presença (MEC/Bolsa Família)</h3></div>
    <div style="padding:1rem 1.25rem">
        <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;padding:.85rem;font-size:.82rem;color:#1e40af;margin-bottom:1rem">
            <strong>📌 Base Legal:</strong> Decreto nº 12.064/2024, art. 39, II — Frequência mínima de <strong>75%</strong> para beneficiários de 6 a 18 anos.
            Todos os alunos desta escola são considerados beneficiários do Programa Bolsa Família. Registro bimestral obrigatório no Sistema Presença do MEC.
        </div>
        <form method="GET" action="/presenca-mec" style="display:flex;gap:1rem;flex-wrap:wrap;align-items:flex-end">
            <div class="form-group" style="margin:0;flex:1;min-width:160px">
                <label>Bimestre</label>
                <select name="bimestre" class="form-control" onchange="this.form.submit()">
                    <?php foreach ($bimestres as $k => $b): ?>
                    <option value="<?= $k ?>" <?= $k == $bimestreParam ? 'selected' : '' ?>><?= $b['label'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group" style="margin:0;flex:1;min-width:120px">
                <label>Ano</label>
                <select name="ano" class="form-control" onchange="this.form.submit()">
                    <?php for ($y = date('Y'); $y >= date('Y') - 3; $y--): ?>
                    <option value="<?= $y ?>" <?= $y == $ano ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <a href="/presenca-mec/imprimir?bimestre=<?= $bimestreParam ?>&ano=<?= $ano ?>" target="_blank"
               class="btn btn-primary" style="align-self:flex-end">
                🖨️ Gerar Relatório para Impressão
            </a>
        </form>
    </div>
</div>

<!-- CARDS RESUMO -->
<div class="cards" style="margin-bottom:1.5rem">
    <div class="card">
        <div class="card-label">Total de Alunos (PBF)</div>
        <div class="card-value"><?= $totalAlunos ?></div>
        <div class="card-sub"><?= $bimSel['label'] ?> / <?= $ano ?></div>
    </div>
    <div class="card green">
        <div class="card-label">Atendem 75% (PBF)</div>
        <div class="card-value"><?= $totalAtendePBF ?></div>
        <div class="card-sub">Benefício mantido</div>
    </div>
    <div class="card red">
        <div class="card-label">Abaixo de 75%</div>
        <div class="card-value"><?= $totalNaoAtendePBF ?></div>
        <div class="card-sub">Risco de bloqueio do PBF</div>
    </div>
    <div class="card yellow">
        <div class="card-label">Dias Letivos (período)</div>
        <div class="card-value"><?= $diasLetivos ?></div>
        <div class="card-sub">Registrados no sistema</div>
    </div>
</div>

<!-- TABELA -->
<div class="table-wrap">
    <div class="table-head">
        <h3>👥 Frequência Bimestral por Aluno</h3>
        <span style="font-size:.8rem;color:#64748b"><?= $bimSel['label'] ?> — <?= $dataInicio ?> a <?= $dataFim ?></span>
    </div>
    <table>
        <thead>
            <tr>
                <th>Aluno / Responsável</th>
                <th>Turma</th>
                <th style="text-align:center">Presenças</th>
                <th style="text-align:center">Faltas</th>
                <th style="text-align:center">% Freq.</th>
                <th style="text-align:center">PBF</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($alunos as $a): ?>
            <tr style="<?= !$a->atende_pbf ? 'background:#fff5f5' : '' ?>">
                <td>
                    <strong><?= e($a->nome) ?></strong>
                    <div style="font-size:.72rem;color:#64748b">Mat: <?= e($a->matricula) ?></div>
                    <?php if ($a->responsavel_nome): ?>
                    <div style="font-size:.7rem;color:#94a3b8">Resp: <?= e($a->responsavel_nome) ?></div>
                    <?php endif; ?>
                </td>
                <td><?= e($a->turma_nome) ?><br><small style="color:#94a3b8"><?= e($a->turno) ?></small></td>
                <td style="text-align:center;color:#10b981;font-weight:600"><?= $a->presencas ?></td>
                <td style="text-align:center;color:#ef4444;font-weight:600"><?= $a->faltas ?></td>
                <td style="text-align:center">
                    <?php $cor = $a->pct_frequencia >= 75 ? '#10b981' : ($a->pct_frequencia >= 60 ? '#f59e0b' : '#ef4444'); ?>
                    <span style="font-weight:700;color:<?= $cor ?>"><?= $a->pct_frequencia ?>%</span>
                    <?php if ($a->nao_lancado > 0): ?>
                    <br><small style="font-size:.65rem;color:#94a3b8">(<?= $a->nao_lancado ?> dias s/ registro)</small>
                    <?php endif; ?>
                </td>
                <td style="text-align:center">
                    <?php if ($a->atende_pbf): ?>
                        <span class="badge badge-green">✅ OK</span>
                    <?php else: ?>
                        <span class="badge badge-red">⚠️ Risco</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
