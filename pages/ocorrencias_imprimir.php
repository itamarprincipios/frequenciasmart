<?php
// pages/ocorrencias_imprimir.php — Relatório impresso de Ocorrência Disciplinar
requer_login();

if (!isset($id) || !is_numeric($id)) {
    die("ID de ocorrência inválido.");
}

$ocorr = db_one(
    "SELECT o.*, a.nome AS aluno_nome, a.matricula, t.nome AS turma_nome, e.nome AS escola_nome, u.nome AS usuario_nome
     FROM ocorrencias_disciplinares o
     JOIN alunos a ON a.id = o.aluno_id
     LEFT JOIN turmas t ON t.id = o.turma_id
     JOIN escolas e ON e.id = o.escola_id
     LEFT JOIN users u ON u.id = o.registrado_por
     WHERE o.id = ? AND o.escola_id = ?",
    [(int)$id, escola_id()]
);

if (!$ocorr) {
    die("Ocorrência não encontrada.");
}

function fmt_tipo_titulo($tipo) {
    switch ($tipo) {
        case 'INDISCIPLINA_PROFESSOR': return 'RELATÓRIO DE INDISCIPLINA COM PROFESSOR';
        case 'RECUSA_ATIVIDADE':       return 'RELATÓRIO DE RECUSA DE ATIVIDADES PEDAGÓGICAS';
        case 'BRIGA':                  return 'RELATÓRIO DE BRIGA / CONFLITO ESCOLAR';
        case 'FURTO':                  return 'RELATÓRIO DE SUBTRAÇÃO / FURTO DE BENS';
        default:                       return 'REGISTRO DE OCORRÊNCIA DISCIPLINAR';
    }
}

$tituloPagina = "Relatório de Ocorrência - " . $ocorr->aluno_nome;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title><?= e($tituloPagina) ?></title>
    <style>
        body { font-family: 'Inter', sans-serif; line-height: 1.5; color: #333; padding: 20px; background: #f8fafc; }
        .documento { max-width: 800px; margin: 0 auto; border: 1px solid #ccc; padding: 40px; background: #fff; font-size: 0.95rem; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1); }
        .cabecalho { text-align: center; margin-bottom: 25px; border-bottom: 2px solid #333; padding-bottom: 15px; }
        .cabecalho h1 { font-size: 1.4rem; margin: 0; text-transform: uppercase; letter-spacing: 0.05em; }
        .cabecalho p { margin: 4px 0; font-size: 0.85rem; color: #555; }
        .titulo-doc { text-align: center; font-weight: bold; margin-bottom: 25px; font-size: 1.2rem; letter-spacing: 0.02em; text-transform: uppercase; color: #0f172a; border: 1px solid #0f172a; padding: 8px 12px; border-radius: 4px; background-color: #f8fafc; }
        .corpo { margin-bottom: 30px; text-align: justify; }
        .dados-aluno { background: #f8fafc; padding: 15px; border: 1px solid #e2e8f0; margin-bottom: 20px; border-radius: 6px; }
        .dados-aluno table { width: 100%; border-collapse: collapse; }
        .dados-aluno td { padding: 4px 0; font-size: 0.9rem; }
        .dados-aluno td.label { font-weight: bold; width: 30%; color: #475569; }
        .assinaturas { margin-top: 50px; display: grid; grid-template-columns: 1fr 1fr; gap: 30px; }
        .assinatura-box { border-top: 1px solid #333; text-align: center; padding-top: 8px; font-size: 0.85rem; }
        .footer-doc { margin-top: 40px; font-size: 0.75rem; text-align: center; color: #777; border-top: 1px dashed #ddd; padding-top: 15px; }
        @media print {
            body { padding: 0; background: none; }
            .documento { border: none; padding: 0; width: 100%; max-width: none; box-shadow: none; }
            .no-print { display: none; }
        }
        .btn-print { background: #ef4444; color: #fff; padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer; margin-bottom: 20px; font-weight: 600; font-size: 0.9rem; transition: background 0.2s; }
        .btn-print:hover { background: #dc2626; }
    </style>
</head>
<body>

<div style="text-align: center;" class="no-print">
    <button onclick="window.print()" class="btn-print">🖨️ Imprimir Termo de Indisciplina</button>
</div>

<div class="documento">
    <div class="cabecalho">
        <h1><?= e($ocorr->escola_nome) ?></h1>
        <p>Sistema FrequenciaSmart — Controle Disciplinar e Acompanhamento Escolar</p>
    </div>

    <div class="titulo-doc"><?= e(fmt_tipo_titulo($ocorr->tipo)) ?></div>

    <div class="corpo">
        <p style="text-indent: 2em; margin-bottom: 15px;">
            Registramos, para fins de acompanhamento ético-disciplinar e ciência da família, que o discente abaixo qualificado incorreu em conduta contrária às normas de convivência desta instituição de ensino, conforme relato técnico dos fatos.
        </p>

        <div class="dados-aluno">
            <table>
                <tr>
                    <td class="label">Estudante:</td>
                    <td><strong><?= e($ocorr->aluno_nome) ?></strong></td>
                </tr>
                <tr>
                    <td class="label">Matrícula:</td>
                    <td><?= e($ocorr->matricula) ?></td>
                </tr>
                <tr>
                    <td class="label">Turma/Turno:</td>
                    <td><?= e($ocorr->turma_nome ?? 'Não definida') ?></td>
                </tr>
                <tr>
                    <td class="label">Data do Ocorrido:</td>
                    <td><strong><?= fmt_data($ocorr->data_ocorrencia) ?></strong></td>
                </tr>
                <tr>
                    <td class="label">Emitido Por:</td>
                    <td><?= e($ocorr->usuario_nome ?? 'Coordenação Escolar') ?></td>
                </tr>
            </table>
        </div>

        <p style="margin-top: 20px; font-weight: bold; color: #1e293b;">
            I. Histórico e Relato Pormenorizado do Ocorrido:
        </p>
        <div style="background: #f8fafc; border-left: 4px solid #ef4444; padding: 12px 16px; margin: 10px 0; font-size: 0.95rem; text-align: justify; line-height: 1.6; border-radius: 0 6px 6px 0; color: #1e293b;">
            <?= nl2br(e($ocorr->descricao)) ?>
        </div>

        <p style="margin-top: 20px; font-weight: bold; color: #1e293b;">
            II. Providências e Medidas Educativas Adotadas pela Escola:
        </p>
        <div style="background: #fff8f8; border-left: 4px solid #ef4444; padding: 12px 16px; margin: 10px 0; font-size: 0.95rem; font-weight: 500; border-radius: 0 6px 6px 0; color: #b91c1c;">
            👉 <?= e($ocorr->medida_tomada ?: 'Advertência e acompanhamento pedagógico') ?>
        </div>

        <p style="margin-top: 30px; font-size: 0.825rem; color: #4b5563; text-align: justify; border-top: 1px dashed #e2e8f0; padding-top: 15px;">
            <strong>Nota aos Pais/Responsáveis:</strong> A parceria entre a família e a escola é de suma importância para o desenvolvimento integral de nossas crianças e adolescentes. O ambiente escolar exige cooperação, empatia e respeito mútuo. Solicitamos especial atenção à atitude reportada e diálogo familiar com o discente, a fim de que os fatos descritos não voltem a ocorrer.
        </p>
    </div>

    <div class="assinaturas">
        <div class="assinatura-box">
            <strong>Responsável Pelo Aluno (Pai/Mãe/Tutor)</strong><br>
            <span style="font-size: 0.75rem; color: #64748b;">Declaro ciência dos fatos descritos</span><br>
            <span style="font-size: 0.75rem; color: #64748b; margin-top: 5px; display: block;">Data: ____/____/2026</span>
        </div>
        <div class="assinatura-box">
            <strong>Orientador(a) / Coordenador(a) Pedagógico(a)</strong><br>
            <span style="font-size: 0.75rem; color: #64748b;"><?= e($ocorr->usuario_nome ?? 'Servidor Responsável') ?></span>
        </div>
    </div>

    <div class="footer-doc">
        Documento gerado e arquivado eletronicamente pelo Sistema FrequenciaSmart em <?= date('d/m/Y \à\s H:i') ?>
    </div>
</div>

</body>
</html>
