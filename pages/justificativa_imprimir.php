<?php
// pages/justificativa_imprimir.php — Gerador de documento de justificativa de falta para impressão
requer_login();

if (!isset($id) || !is_numeric($id)) {
    die("ID de justificativa inválido.");
}

$just = db_one(
    "SELECT j.*, a.nome AS aluno_nome, a.matricula, t.nome AS turma_nome, f.data AS data_falta, e.nome AS escola_nome, u.nome AS usuario_nome
     FROM justificativas_faltas j
     JOIN alunos a ON a.id = j.aluno_id
     LEFT JOIN turmas t ON t.id = a.turma_id
     JOIN frequencias f ON f.id = j.frequencia_id
     JOIN escolas e ON e.id = j.escola_id
     LEFT JOIN users u ON u.id = j.registrado_por
     WHERE j.id = ? AND j.escola_id = ?",
    [(int)$id, escola_id()]
);

if (!$just) {
    die("Justificativa não encontrada.");
}

$tituloPagina = "Justificativa de Falta - " . $just->aluno_nome;
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
        .titulo-doc { text-align: center; font-weight: bold; margin-bottom: 25px; font-size: 1.25rem; letter-spacing: 0.02em; text-transform: uppercase; color: #0f172a; }
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
        .btn-print { background: #4f46e5; color: #fff; padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer; margin-bottom: 20px; font-weight: 600; font-size: 0.9rem; transition: background 0.2s; }
        .btn-print:hover { background: #4338ca; }
    </style>
</head>
<body>

<div style="text-align: center;" class="no-print">
    <button onclick="window.print()" class="btn-print">🖨️ Imprimir Termo de Justificativa</button>
</div>

<div class="documento">
    <div class="cabecalho">
        <h1><?= e($just->escola_nome) ?></h1>
        <p>Sistema FrequenciaSmart — Gestão de Assiduidade Escolar</p>
    </div>

    <div class="titulo-doc">TERMO DE JUSTIFICATIVA DE FALTA ESCOLAR</div>

    <div class="corpo">
        <p style="text-indent: 2em; margin-bottom: 20px;">
            Declaro para os devidos fins de acompanhamento pedagógico e registro de assiduidade escolar, que o(a) responsável abaixo identificado(a) compareceu a esta unidade de ensino na presente data para justificar formalmente a ausência de seu(sua) dependente.
        </p>

        <div class="dados-aluno">
            <table>
                <tr>
                    <td class="label">Aluno(a):</td>
                    <td><strong><?= e($just->aluno_nome) ?></strong></td>
                </tr>
                <tr>
                    <td class="label">Matrícula:</td>
                    <td><?= e($just->matricula) ?></td>
                </tr>
                <tr>
                    <td class="label">Turma/Turno:</td>
                    <td><?= e($just->turma_nome) ?></td>
                </tr>
                <tr>
                    <td class="label">Data da Falta:</td>
                    <td><strong style="color: #b91c1c;"><?= fmt_data($just->data_falta) ?></strong></td>
                </tr>
                <tr>
                    <td class="label">Responsável:</td>
                    <td><strong><?= e($just->responsavel_nome) ?></strong> (<?= e($just->parentesco) ?>)</td>
                </tr>
                <tr>
                    <td class="label">Comparecimento:</td>
                    <td><?= fmt_data($just->data_visita) ?></td>
                </tr>
            </table>
        </div>

        <p style="margin-top: 20px;">
            <strong>Motivação Declarada pelo Responsável:</strong>
        </p>
        <div style="background: #fff; border-left: 4px solid #4f46e5; padding: 12px 16px; margin: 10px 0; font-style: italic; background-color: #f8fafc; border-radius: 0 6px 6px 0;">
            "<?= nl2br(e($just->motivo)) ?>"
        </div>

        <?php if ($just->observacoes): ?>
            <p style="margin-top: 15px;"><strong>Observações da Coordenação/Orientação:</strong></p>
            <p style="font-size: 0.9rem; color: #475569;"><?= nl2br(e($just->observacoes)) ?></p>
        <?php endif; ?>

        <p style="margin-top: 25px; font-size: 0.85rem; color: #4b5563; text-align: justify;">
            <strong>Informação Legal:</strong> Este documento comprova a justificativa verbal apresentada pelo responsável do aluno perante a escola. Embora sirva como registro interno pedagógico de acompanhamento familiar, a frequência do aluno continuará registrada e o aluno deve manter esforço constante para cumprir a frequência mínima obrigatória de 75% imposta pela LDB (Lei nº 9.394/96).
        </p>
    </div>

    <div class="assinaturas">
        <div class="assinatura-box">
            <strong>Assinatura do Responsável</strong><br>
            <span style="font-size: 0.75rem; color: #64748b;">RG/CPF: _________________________</span>
        </div>
        <div class="assinatura-box">
            <strong>Orientador(a) Pedagógico(a) / Coordenação</strong><br>
            <span style="font-size: 0.75rem; color: #64748b;"><?= e($just->usuario_nome ?? 'Servidor Responsável') ?></span>
        </div>
    </div>

    <div class="footer-doc">
        Documento autenticado eletronicamente pelo Sistema FrequenciaSmart em <?= date('d/m/Y \à\s H:i') ?>
    </div>
</div>

</body>
</html>
