<?php
// pages/relatorio_imprimir.php — Gerador de Relatório Consolidado para Impressão
requer_login();
requer_role('DIRETOR', 'VICE');

$mes      = $_GET['mes']      ?? date('Y-m');
$turmaId  = $_GET['turma_id'] ?? null;
$escolaId = escola_id();

// 1. Informações da Escola
$escola = db_one("SELECT * FROM escolas WHERE id = ?", [$escolaId]);

// 2. Filtros e Parâmetros
$params = [$escolaId, $mes];
$whereTurma = $turmaId ? " AND a.turma_id = ?" : "";
if ($turmaId) $params[] = $turmaId;

// 3. Métricas Básicas
// Total de alunos ativos na escola/turma
$totalAlunos = db_one(
    "SELECT COUNT(*) as total FROM alunos a WHERE a.escola_id = ? AND a.ativo = 1 $whereTurma",
    $turmaId ? [$escolaId, $turmaId] : [$escolaId]
)->total;

// Total de faltas no mês
$totalFaltasMes = db_one(
    "SELECT COUNT(*) as total FROM frequencias f 
     JOIN alunos a ON a.id = f.aluno_id
     WHERE f.escola_id = ? AND f.status = 'FALTA' AND DATE_FORMAT(f.data, '%Y-%m') = ? $whereTurma",
    $params
)->total;

// 4. Lista de Alunos Críticos (5+ faltas no mês)
$alunosCriticos = db_all(
    "SELECT a.nome, a.matricula, t.nome as turma_nome, COUNT(f.id) as total_faltas
     FROM frequencias f
     JOIN alunos a ON a.id = f.aluno_id
     JOIN turmas t ON t.id = a.turma_id
     WHERE f.escola_id = ? AND f.status = 'FALTA' AND DATE_FORMAT(f.data, '%Y-%m') = ? $whereTurma
     GROUP BY a.id
     HAVING total_faltas >= 5
     ORDER BY total_faltas DESC",
    $params
);

// 5. Resumo por Turma
$resumoTurmas = db_all(
    "SELECT t.nome, t.turno, 
            (SELECT COUNT(*) FROM alunos WHERE turma_id = t.id AND ativo = 1) as total_alunos,
            COUNT(f.id) as total_faltas
     FROM turmas t
     LEFT JOIN alunos a ON a.turma_id = t.id AND a.ativo = 1
     LEFT JOIN frequencias f ON f.aluno_id = a.id AND f.status = 'FALTA' AND DATE_FORMAT(f.data, '%Y-%m') = ?
     WHERE t.escola_id = ? AND t.ativa = 1
     GROUP BY t.id
     ORDER BY t.nome ASC",
    [$mes, $escolaId]
);

$tituloPagina = "Relatório de Frequência - " . fmt_mes_ano($mes);

function fmt_mes_ano($mesAno) {
    if (!$mesAno) return '';
    $partes = explode('-', $mesAno);
    if (count($partes) !== 2) return $mesAno;
    $meses = [
        '01' => 'Janeiro', '02' => 'Fevereiro', '03' => 'Março', '04' => 'Abril',
        '05' => 'Maio', '06' => 'Junho', '07' => 'Julho', '08' => 'Agosto',
        '09' => 'Setembro', '10' => 'Outubro', '11' => 'Novembro', '12' => 'Dezembro'
    ];
    return $meses[$partes[1]] . ' de ' . $partes[0];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title><?= e($tituloPagina) ?></title>
    <style>
        body { font-family: 'Inter', sans-serif; line-height: 1.5; color: #1e293b; padding: 20px; background: #f1f5f9; }
        .documento { max-width: 900px; margin: 0 auto; background: #fff; padding: 40px; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1); border-radius: 8px; }
        .head { border-bottom: 2px solid #1e293b; padding-bottom: 15px; margin-bottom: 25px; display: flex; justify-content: space-between; align-items: flex-end; }
        .head h1 { font-size: 1.5rem; margin: 0; color: #1e1b4b; text-transform: uppercase; }
        .head p { margin: 0; font-size: 0.85rem; color: #64748b; }
        
        .section-title { font-size: 1rem; font-weight: 700; background: #f8fafc; padding: 8px 12px; border-left: 4px solid #4f46e5; margin: 30px 0 15px; text-transform: uppercase; letter-spacing: 0.05em; }
        
        .stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 20px; }
        .stat-card { border: 1px solid #e2e8f0; padding: 15px; border-radius: 6px; text-align: center; }
        .stat-label { font-size: 0.7rem; color: #64748b; text-transform: uppercase; font-weight: 600; }
        .stat-value { font-size: 1.75rem; font-weight: 800; color: #1e293b; }

        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 0.85rem; }
        th { text-align: left; background: #f1f5f9; padding: 10px; border-bottom: 2px solid #e2e8f0; }
        td { padding: 10px; border-bottom: 1px solid #f1f5f9; }
        .text-center { text-align: center; }
        .text-red { color: #ef4444; font-weight: 600; }

        .footer { margin-top: 50px; border-top: 1px solid #e2e8f0; padding-top: 20px; font-size: 0.75rem; color: #94a3b8; display: flex; justify-content: space-between; }
        
        .no-print { text-align: center; margin-bottom: 20px; }
        .btn-print { background: #4f46e5; color: #fff; padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 0.9rem; }

        @media print {
            body { padding: 0; background: none; }
            .documento { box-shadow: none; border: none; max-width: none; width: 100%; padding: 0; }
            .no-print { display: none; }
            .stat-card { border-color: #333; }
        }
    </style>
</head>
<body>

<div class="no-print">
    <button onclick="window.print()" class="btn-print">🖨️ Imprimir agora</button>
    <p style="font-size: 0.8rem; margin-top: 10px; color: #64748b;">Dica: Salve como PDF na tela de impressão.</p>
</div>

<div class="documento">
    <div class="head">
        <div>
            <h1><?= e($escola->nome) ?></h1>
            <p>Relatório Pedagógico de Assiduidade e Frequência</p>
        </div>
        <div style="text-align: right;">
            <p><strong>Mês de Referência:</strong> <?= e(fmt_mes_ano($mes)) ?></p>
            <p><strong>Emitido em:</strong> <?= date('d/m/Y H:i') ?></p>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-label">Total de Alunos (Ativos)</div>
            <div class="stat-value"><?= e($totalAlunos) ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Total de Faltas no Mês</div>
            <div class="stat-value" style="color: #ef4444;"><?= e($totalFaltasMes) ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Alertas Críticos (5+)</div>
            <div class="stat-value"><?= count($alunosCriticos) ?></div>
        </div>
    </div>

    <div class="section-title">🚩 Alunos com Infrequência Crítica (5+ faltas no mês)</div>
    <table>
        <thead>
            <tr>
                <th>Aluno(a)</th>
                <th>Turma</th>
                <th class="text-center">Total de Faltas</th>
                <th>Situação</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($alunosCriticos)): ?>
                <tr><td colspan="4" class="text-center" style="padding: 20px; color: #94a3b8;">Nenhum aluno atingiu o limite crítico de faltas neste mês.</td></tr>
            <?php else: foreach ($alunosCriticos as $a): ?>
                <tr>
                    <td><strong><?= e($a->nome) ?></strong><br><small style="color:#64748b"><?= e($a->matricula) ?></small></td>
                    <td><?= e($a->turma_nome) ?></td>
                    <td class="text-center text-red"><?= e($a->total_faltas) ?></td>
                    <td><span style="color:#b45309; font-weight: 500;">⚠️ Risco de Evasão</span></td>
                </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>

    <div class="section-title">📊 Visão Consolidada por Turma</div>
    <table>
        <thead>
            <tr>
                <th>Turma</th>
                <th>Turno</th>
                <th class="text-center">Qtd. Alunos</th>
                <th class="text-center">Total de Faltas</th>
                <th class="text-center">Média Faltas/Aluno</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($resumoTurmas as $t): 
                $media = $t->total_alunos > 0 ? round($t->total_faltas / $t->total_alunos, 1) : 0;
            ?>
                <tr>
                    <td><strong><?= e($t->nome) ?></strong></td>
                    <td><?= e($t->turno) ?></td>
                    <td class="text-center"><?= e($t->total_alunos) ?></td>
                    <td class="text-center"><?= e($t->total_faltas) ?></td>
                    <td class="text-center">
                        <span style="color: <?= $media > 3 ? '#ef4444' : ($media > 1 ? '#f59e0b' : '#10b981') ?>">
                            <?= $media ?>
                        </span>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div style="margin-top: 40px; padding: 15px; background: #fffbeb; border: 1px solid #fef3c7; border-radius: 6px; font-size: 0.8rem; color: #92400e;">
        <strong>Nota Pedagógica:</strong> Este relatório serve como subsídio para intervenções junto aos responsáveis e ao Conselho Tutelar, visando garantir o direito constitucional à educação e combater a evasão escolar precoce.
    </div>

    <div class="footer">
        <div>Gerado pelo Sistema FrequenciaSmart — fqs.cloud</div>
        <div>Página 1 de 1</div>
    </div>
</div>

<script>
    // Se quiser que a página abra o diálogo de impressão ao carregar:
    // window.onload = () => window.print();
</script>

</body>
</html>
