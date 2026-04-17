<?php
// pages/notificacao_imprimir.php — Gerador de documento de notificação para impressão
requer_login();

// O ID do alerta vem do index.php (variável $id)
if (!isset($id) || !is_numeric($id)) {
    die("ID de alerta inválido.");
}

$alerta = db_one(
    "SELECT al.*, a.nome AS aluno_nome, a.matricula, t.nome AS turma_nome, e.nome AS escola_nome
     FROM alertas al
     JOIN alunos a ON a.id = al.aluno_id
     LEFT JOIN turmas t ON t.id = a.turma_id
     JOIN escolas e ON e.id = al.escola_id
     WHERE al.id = ? AND al.escola_id = ?",
    [(int)$id, escola_id()]
);

if (!$alerta) {
    die("Alerta não encontrado.");
}

// Buscar as faltas que geraram o alerta
$faltas = [];
if ($alerta->tipo === 'CONSECUTIVA') {
    // Pega as últimas faltas consecutivas até encontrar uma presença
    $todas = db_all(
        "SELECT data FROM frequencias WHERE aluno_id = ? AND escola_id = ? ORDER BY data DESC",
        [$alerta->aluno_id, $alerta->escola_id]
    );
    foreach ($todas as $f) {
        $faltas[] = $f->data;
        if (count($faltas) >= 3) {
            // Verifica se as próximas também são faltas (opcional, vamos pegar as 3+ mais recentes)
        }
    }
    // Simplificando: vamos mostrar todas as faltas do mês do alerta
    $faltas = db_all(
        "SELECT data FROM frequencias 
         WHERE aluno_id = ? AND escola_id = ? AND status = 'FALTA' AND DATE_FORMAT(data, '%Y-%m') = ?
         ORDER BY data ASC",
        [$alerta->aluno_id, $alerta->escola_id, $alerta->mes_referencia]
    );
} else {
    // Intercalada: todas as faltas do mês
    $faltas = db_all(
        "SELECT data FROM frequencias 
         WHERE aluno_id = ? AND escola_id = ? AND status = 'FALTA' AND DATE_FORMAT(data, '%Y-%m') = ?
         ORDER BY data ASC",
        [$alerta->aluno_id, $alerta->escola_id, $alerta->mes_referencia]
    );
}

$tituloPagina = "Notificação - " . $alerta->aluno_nome;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title><?= e($tituloPagina) ?></title>
    <style>
        body { font-family: 'Inter', sans-serif; line-height: 1.4; color: #333; padding: 20px; }
        .documento { max-width: 800px; margin: 0 auto; border: 1px solid #ccc; padding: 30px; background: #fff; font-size: 0.9rem; }
        .cabecalho { text-align: center; margin-bottom: 15px; border-bottom: 1px solid #333; padding-bottom: 10px; }
        .cabecalho h1 { font-size: 1.3rem; margin: 0; text-transform: uppercase; }
        .cabecalho p { margin: 2px 0; font-size: 0.8rem; }
        .titulo-doc { text-align: center; text-decoration: underline; font-weight: bold; margin-bottom: 15px; font-size: 1.1rem; }
        .corpo { margin-bottom: 15px; text-align: justify; }
        .dados-aluno { background: #f9f9f9; padding: 10px; border: 1px solid #eee; margin-bottom: 10px; display: flex; gap: 20px; }
        .faltas-lista { margin: 10px 0; }
        .faltas-grid { display: grid; grid-template-columns: repeat(6, 1fr); gap: 5px; margin-top: 5px; }
        .falta-item { border: 1px solid #ddd; padding: 3px; text-align: center; font-size: 0.75rem; }
        .assinaturas { margin-top: 40px; display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .assinatura-box { border-top: 1px solid #333; text-align: center; padding-top: 5px; font-size: 0.8rem; }
        .footer-doc { margin-top: 20px; font-size: 0.7rem; text-align: center; color: #777; }
        @media print {
            body { padding: 0; background: none; }
            .documento { border: none; padding: 0; width: 100%; max-width: none; }
            .no-print { display: none; }
        }
        .btn-print { background: #4f46e5; color: #fff; padding: 8px 16px; border: none; border-radius: 5px; cursor: pointer; margin-bottom: 10px; }
    </style>
</head>
<body>

<div style="text-align: center;" class="no-print">
    <button onclick="window.print()" class="btn-print">🖨️ Imprimir Notificação</button>
</div>

<div class="documento">
    <div class="cabecalho">
        <h1><?= e($alerta->escola_nome) ?></h1>
        <p>Sistema FrequenciaSmart — Gestão de Assiduidade Escolar</p>
    </div>

    <div class="titulo-doc">NOTIFICAÇÃO DE INFREQUÊNCIA ESCOLAR</div>

    <div class="corpo">
        <p>Prezados Responsáveis,</p>
        <p>Vimos por meio desta informar que o(a) aluno(a) abaixo identificado(a) apresenta um índice de faltas que requer atenção imediata, conforme os registros do sistema de frequência escolar desta unidade.</p>

        <div class="dados-aluno">
            <strong>Aluno(a):</strong> <?= e($alerta->aluno_nome) ?><br>
            <strong>Turma:</strong> <?= e($alerta->turma_nome) ?><br>
        </div>

        <p>
            <strong>Motivo da Notificação:</strong> 
            <?php if ($alerta->tipo === 'CONSECUTIVA'): ?>
                O(A) aluno(a) atingiu a marca de <strong>3 ou mais faltas consecutivas</strong>, o que pode prejudicar significativamente o aprendizado e o acompanhamento pedagógico.
            <?php else: ?>
                O(A) aluno(a) atingiu a marca de <strong>8 ou mais faltas intercaladas</strong> no mês de referência, totalizando <strong><?= count($faltas) ?> faltas</strong> até o presente momento.
            <?php endif; ?>
        </p>

        <div class="faltas-lista">
            <strong>Datas das Ausências Registradas:</strong>
            <div class="faltas-grid">
                <?php foreach ($faltas as $f): ?>
                    <div class="falta-item"><?= fmt_data($f->data) ?></div>
                <?php endforeach; ?>
            </div>
        </div>

        <p>Solicitamos o comparecimento dos responsáveis à escola para prestar esclarecimentos sobre as ausências, tendo em vista a necessidade de acompanhamento da frequência escolar e a garantia do direito à educação do aluno.</p>

        <div style="font-size: 0.85rem; margin-top: 30px; border-top: 1px dashed #ccc; padding-top: 15px; color: #444; text-align: justify;">
            <p style="margin-bottom: 10px;">
                De acordo com a Lei de Diretrizes e Bases da Educação Nacional (Lei nº 9.394/1996), o aluno deve cumprir mínimo de 75% de frequência ao longo dos 200 dias letivos para aprovação. O não cumprimento desse requisito pode resultar em reprovação por faltas, comprometendo seu progresso escolar.
            </p>
            <p style="margin-bottom: 10px;">
                Conforme o Estatuto da Criança e do Adolescente (Lei nº 8.069/1990), a escola deve acompanhar a frequência e, em casos de ausência excessiva ou risco de evasão, poderá acionar o Conselho Tutelar para garantir o direito à educação, sendo este um dever compartilhado entre família, escola e poder público.
            </p>
            <p style="margin-bottom: 15px;">
                Além disso, a Portaria Interministerial nº 378/2004 estabelece que a frequência escolar é critério para manutenção de benefícios sociais, como o Bolsa Família, podendo haver impacto direto no recebimento do benefício em caso de descumprimento.
            </p>
            <p style="font-weight: 500; color: #1e293b; border-left: 3px solid #4f46e5; padding-left: 10px;">
                Dessa forma, reforçamos a importância da presença dos responsáveis para que, em conjunto com a escola, sejam adotadas medidas que assegurem a frequência regular e o pleno desenvolvimento do aluno.
            </p>
        </div>
    </div>

    <div class="assinaturas">
        <div class="assinatura-box">
            <strong>Professor(a) da Turma</strong><br>
            <span style="font-size: 0.7rem;">Ciente das Faltas</span>
        </div>
        <div class="assinatura-box">
            <strong>Orientador(a) Pedagógico(a)</strong><br>
            <?= e($_SESSION['usuario']['nome'] ?? '') ?>
        </div>
        <div class="assinatura-box" style="grid-column: span 2; width: 60%; margin: 40px auto 0;">
            <strong>Assinatura do Responsável</strong><br>
            <span style="font-size: 0.7rem;">Data: ____/____/2026</span>
        </div>
    </div>

    <div class="footer-doc">
        Documento gerado eletronicamente pelo Sistema FrequenciaSmart em <?= date('d/m/Y \à\s H:i') ?>
    </div>
</div>

</body>
</html>
<?php
// Função auxiliar local para formatar mês/ano por extenso se desejar, 
// ou apenas formatar a string YYYY-MM
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
