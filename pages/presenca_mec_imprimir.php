<?php
// pages/presenca_mec_imprimir.php — Versão impressão do relatório Sistema Presença MEC
// Decreto nº 12.064/2024, art. 39 — Portaria MDS nº 1.058/2025
requer_login();
requer_role('DIRETOR', 'VICE');

define('DIAS_LETIVOS_ANUAIS', 200);
define('FREQUENCIA_MINIMA_PBF', 0.75);

$bimestreParam = $_GET['bimestre'] ?? '1';
$ano = $_GET['ano'] ?? date('Y');

$bimestres = [
    '1' => ['label' => '1º Bimestre', 'inicio' => "$ano-02-01", 'fim' => "$ano-04-30"],
    '2' => ['label' => '2º Bimestre', 'inicio' => "$ano-05-01", 'fim' => "$ano-07-31"],
    '3' => ['label' => '3º Bimestre', 'inicio' => "$ano-08-01", 'fim' => "$ano-09-30"],
    '4' => ['label' => '4º Bimestre', 'inicio' => "$ano-10-01", 'fim' => "$ano-12-31"],
];

$bimSel     = $bimestres[$bimestreParam] ?? $bimestres['1'];
$dataInicio = $bimSel['inicio'];
$dataFim    = $bimSel['fim'];

$diasLetivos = db_one(
    "SELECT COUNT(DISTINCT data) AS total FROM frequencias 
     WHERE escola_id = ? AND data BETWEEN ? AND ?",
    [escola_id(), $dataInicio, $dataFim]
)->total;
if (!$diasLetivos) $diasLetivos = 50;

$escola = db_one("SELECT * FROM escolas WHERE id = ?", [escola_id()]);

$alunos = db_all(
    "SELECT 
        a.id, a.nome, a.matricula, a.data_nascimento,
        a.responsavel_nome, a.responsavel_cpf,
        t.nome AS turma_nome,
        COUNT(CASE WHEN f.status = 'PRESENTE' THEN 1 END) AS presencas,
        COUNT(CASE WHEN f.status = 'FALTA' THEN 1 END) AS faltas
     FROM alunos a
     JOIN turmas t ON t.id = a.turma_id AND t.ativa = 1
     LEFT JOIN frequencias f ON f.aluno_id = a.id AND f.data BETWEEN ? AND ?
     WHERE a.escola_id = ? AND a.ativo = 1
     GROUP BY a.id
     ORDER BY t.nome, a.nome",
    [$dataInicio, $dataFim, escola_id()]
);

foreach ($alunos as &$aluno) {
    $aluno->pct = $diasLetivos > 0 ? round(($aluno->presencas / $diasLetivos) * 100, 1) : 0;
    $aluno->atende = $aluno->pct >= 75;
}
unset($aluno);

$totalAtende    = count(array_filter($alunos, fn($a) => $a->atende));
$totalNaoAtende = count($alunos) - $totalAtende;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Sistema Presença — <?= e($bimSel['label']) ?>/<?= $ano ?></title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; color: #111; padding: 15px; }
        .doc { max-width: 900px; margin: 0 auto; }
        .cabecalho { text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 15px; }
        .cabecalho h1 { font-size: 14px; margin: 0; text-transform: uppercase; }
        .cabecalho p { margin: 2px 0; font-size: 10px; }
        .titulo-doc { text-align: center; font-size: 13px; font-weight: bold; text-decoration: underline; margin: 10px 0 15px; text-transform: uppercase; }
        .resumo { display: flex; gap: 10px; margin-bottom: 15px; }
        .resumo-card { flex: 1; border: 1px solid #ccc; padding: 8px; text-align: center; border-radius: 4px; }
        .resumo-card .val { font-size: 20px; font-weight: bold; }
        .resumo-card .lbl { font-size: 9px; text-transform: uppercase; color: #555; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; font-size: 10px; }
        th { background: #f0f0f0; padding: 5px 6px; border: 1px solid #ccc; text-align: center; font-size: 9px; }
        td { padding: 4px 6px; border: 1px solid #ddd; }
        .risco { background: #fff0f0; }
        .ok { color: #166534; font-weight: bold; }
        .nok { color: #991b1b; font-weight: bold; }
        .assinaturas { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-top: 50px; }
        .assinatura-box { border-top: 1px solid #000; text-align: center; padding-top: 5px; }
        .footer { margin-top: 20px; font-size: 8px; text-align: center; color: #888; border-top: 1px solid #ddd; padding-top: 8px; }
        .legal { background: #fffbeb; border: 1px solid #fde68a; padding: 8px; border-radius: 4px; font-size: 9px; color: #78350f; margin-bottom: 15px; }
        @media print { body { padding: 0; } .no-print { display: none; } }
        .btn-print { background: #4f46e5; color: #fff; padding: 8px 18px; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; }
    </style>
</head>
<body>
<div class="no-print" style="text-align:center;margin-bottom:15px">
    <button onclick="window.print()" class="btn-print">🖨️ Imprimir / Salvar PDF</button>
</div>
<div class="doc">
    <div class="cabecalho">
        <h1><?= e($escola->nome ?? 'Escola') ?></h1>
        <p>Relatório Bimestral de Frequência Escolar — Condicionalidades do Programa Bolsa Família</p>
        <p>Base Legal: Decreto nº 12.064/2024, art. 39 | Portaria MDS nº 1.058/2025</p>
    </div>

    <div class="titulo-doc">
        RELATÓRIO DO SISTEMA PRESENÇA — <?= strtoupper($bimSel['label']) ?> / <?= $ano ?>
    </div>

    <div class="resumo">
        <div class="resumo-card"><div class="val"><?= count($alunos) ?></div><div class="lbl">Total de Alunos (PBF)</div></div>
        <div class="resumo-card"><div class="val ok"><?= $totalAtende ?></div><div class="lbl">Atendem ≥75% (PBF OK)</div></div>
        <div class="resumo-card"><div class="val nok"><?= $totalNaoAtende ?></div><div class="lbl">Abaixo de 75% (Risco PBF)</div></div>
        <div class="resumo-card"><div class="val"><?= $diasLetivos ?></div><div class="lbl">Dias letivos no período</div></div>
        <div class="resumo-card"><div class="val"><?= $dataInicio ?> a <?= $dataFim ?></div><div class="lbl">Período de referência</div></div>
    </div>

    <div class="legal">
        <strong>Condicionalidade PBF:</strong> Frequência mínima de <strong>75%</strong> para beneficiários de 6 a 18 anos (Decreto 12.064/2024, art. 39, II).
        Para beneficiários de 4 a 6 anos incompletos: 60% (art. 39, I). Descumprimento: advertência → bloqueio (1 mês) → suspensão (2 meses) → cancelamento.
        <strong>Todos os alunos desta escola são beneficiários do Programa Bolsa Família.</strong>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th style="text-align:left">Nome do Aluno</th>
                <th style="text-align:left">Matrícula</th>
                <th style="text-align:left">Turma</th>
                <th style="text-align:left">Responsável / CPF</th>
                <th>Presenças</th>
                <th>Faltas</th>
                <th>% Freq.</th>
                <th>Situação PBF</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($alunos as $i => $a): ?>
            <tr class="<?= !$a->atende ? 'risco' : '' ?>">
                <td style="text-align:center"><?= $i + 1 ?></td>
                <td><?= e($a->nome) ?></td>
                <td><?= e($a->matricula) ?></td>
                <td><?= e($a->turma_nome) ?></td>
                <td>
                    <?= e($a->responsavel_nome ?? '—') ?>
                    <?php if ($a->responsavel_cpf): ?>
                    <br><small><?= e($a->responsavel_cpf) ?></small>
                    <?php endif; ?>
                </td>
                <td style="text-align:center"><?= $a->presencas ?></td>
                <td style="text-align:center"><?= $a->faltas ?></td>
                <td style="text-align:center" class="<?= $a->atende ? 'ok' : 'nok' ?>"><?= $a->pct ?>%</td>
                <td style="text-align:center" class="<?= $a->atende ? 'ok' : 'nok' ?>">
                    <?= $a->atende ? '✅ REGULAR' : '⚠️ IRREGULAR' ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="assinaturas">
        <div class="assinatura-box">
            <strong>Diretor(a) Escolar</strong><br>
            <small>Responsável pelo lançamento no Sistema Presença</small>
        </div>
        <div class="assinatura-box">
            <strong>Secretaria Municipal de Educação</strong><br>
            <small>Conferência e validação dos dados</small>
        </div>
    </div>

    <div class="footer">
        Documento gerado eletronicamente pelo Sistema FrequenciaSmart em <?= date('d/m/Y \à\s H:i') ?> |
        Escola: <?= e($escola->nome ?? '') ?> | Período: <?= $bimSel['label'] ?>/<?= $ano ?>
    </div>
</div>
</body>
</html>
