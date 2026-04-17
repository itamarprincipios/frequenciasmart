<?php
// pages/turmas_imprimir.php — Impressão em massa para adesivos
requer_login();

// $id vem do roteador
$id = (int)$id;

$turma = db_one("SELECT * FROM turmas WHERE id = ?", [$id]);
if (!$turma) {
    http_response_code(404);
    die('Turma não encontrada.');
}

$alunos = db_all(
    "SELECT * FROM alunos WHERE turma_id = ? AND ativo = 1 ORDER BY nome",
    [$id]
);

if (empty($alunos)) {
    die('<p style="font-family:sans-serif;padding:2rem">Esta turma não possui alunos cadastrados.</p>');
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Imprimir Etiquetas – <?= e($turma->nome) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; background: #f8fafc; color: #1e293b; padding: 2rem; }

        /* Barra de Ação (Apenas Visualização) */
        .actions-bar {
            max-width: 900px; margin: 0 auto 2rem;
            background: #fff; padding: 1rem 1.5rem;
            border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
            display: flex; align-items: center; justify-content: space-between;
        }
        .btn {
            padding: .6rem 1.25rem; border-radius: 8px; font-size: .875rem; font-weight: 600;
            cursor: pointer; border: none; font-family: inherit; text-decoration: none;
            display: inline-flex; align-items: center; gap: .5rem; transition: opacity .2s;
        }
        .btn-primary { background: #4f46e5; color: #fff; }
        .btn-secondary { background: #64748b; color: #fff; }
        .btn:hover { opacity: 0.9; }

        /* Grade de Impressão */
        .print-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr); /* 3 colunas para A4 */
            gap: 0; /* As linhas de corte serão as bordas */
            max-width: 210mm; /* Largura A4 aprox */
            margin: 0 auto;
            background: #fff;
        }

        .label-card {
            border: 1px dashed #cbd5e1; /* Linha de corte */
            padding: 1.5rem 1rem;
            text-align: center;
            background: #fff;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 200px;
        }

        .school-mini { font-size: 0.6rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; margin-bottom: 0.5rem; }
        .qr-img { width: 140px; height: 140px; margin-bottom: 0.75rem; }
        .aluno-nome { font-size: 0.85rem; font-weight: 700; line-height: 1.2; max-width: 100%; word-wrap: break-word; }
        .turma-info { font-size: 0.7rem; color: #64748b; margin-top: 0.3rem; }

        /* Estilos de Impressão */
        @media print {
            body { background: #fff; padding: 0; margin: 0; }
            .actions-bar { display: none !important; }
            .print-grid {
                max-width: 100%; width: 100%;
                display: grid !important;
                grid-template-columns: repeat(3, 33.33%) !important;
            }
            .label-card {
                border: 1px dashed #000; /* Força preta na impressão */
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>

<div class="actions-bar">
    <div>
        <h2 style="font-size:1.1rem">🖨️ Etiquetas: <?= e($turma->nome) ?></h2>
        <p style="font-size:0.8rem;color:#64748b"><?= count($alunos) ?> etiquetas prontas para imprimir.</p>
    </div>
    <div style="display:flex;gap:0.75rem">
        <a href="/turmas" class="btn btn-secondary">Voltar</a>
        <button onclick="window.print()" class="btn btn-primary">Imprimir / Salvar PDF</button>
    </div>
</div>

<div class="print-grid">
    <?php foreach ($alunos as $aluno):
        $payload = json_encode(['aluno_id' => (int)$aluno->id, 'qr_token' => $aluno->qr_token]);
    ?>
    <div class="label-card">
        <div class="school-mini">FrequenciaSmart</div>
        
        <img src="<?= e(qr_url($payload, 150)) ?>" class="qr-img" alt="QR">
        
        <div class="aluno-nome"><?= e($aluno->nome) ?></div>
        <div class="turma-info"><?= e($turma->nome) ?> – <?= e($turma->turno) ?></div>
        <div style="font-size:0.6rem;color:#cbd5e1;margin-top:auto;font-family:monospace"><?= e($aluno->qr_token) ?></div>
    </div>
    <?php endforeach; ?>
</div>

</body>
</html>
