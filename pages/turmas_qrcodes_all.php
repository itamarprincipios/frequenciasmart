<?php
// pages/turmas_qrcodes_all.php — Impressão em massa dos QR Codes de TODAS as turmas
requer_login();

// Apenas Diretores, Vices e Orientadoras podem acessar a lista de turmas
requer_role('DIRETOR', 'VICE', 'ORIENTADORA');

$turmas = db_all(
    "SELECT * FROM turmas WHERE escola_id = ? AND ativa = 1 ORDER BY nome",
    [escola_id()]
);

if (empty($turmas)) {
    die('<p style="font-family:sans-serif;padding:2rem">Nenhuma turma ativa encontrada para esta escola.</p>');
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Imprimir QR Codes - Todas as Turmas</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; background: #f8fafc; color: #1e293b; padding: 2rem; }

        /* Barra de Ações (Oculta na Impressão) */
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
            grid-template-columns: repeat(2, 1fr); /* 2 colunas para QRs legíveis */
            gap: 20px;
            max-width: 210mm;
            margin: 0 auto;
        }

        .qr-card {
            border: 2px solid #e2e8f0;
            border-radius: 16px;
            padding: 2rem 1.5rem;
            text-align: center;
            background: #fff;
            page-break-inside: avoid;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .school-name { font-size: 0.7rem; font-weight: 600; color: #64748b; text-transform: uppercase; margin-bottom: 1rem; letter-spacing: 0.025em; }
        .qr-img { width: 180px; height: 180px; border-radius: 8px; border: 1px solid #f1f5f9; }
        .turma-nome { font-size: 1.3rem; font-weight: 700; color: #1e293b; margin-top: 1.25rem; }
        .turma-tag { display: inline-block; margin-top: 0.5rem; background: #dbeafe; color: #1d4ed8; padding: 0.3rem 1rem; border-radius: 999px; font-size: 0.8rem; font-weight: 600; }
        .token-info { font-size: 0.65rem; color: #94a3b8; font-family: monospace; margin-top: 1.5rem; padding-top: 1rem; border-top: 1px dashed #e2e8f0; width: 100%; word-break: break-all; }

        @media print {
            body { background: #fff; padding: 0; margin: 0; }
            .actions-bar { display: none !important; }
            .print-grid {
                max-width: 100%; width: 100%;
                gap: 10mm;
                padding: 10mm;
            }
            .qr-card { border: 1.5px solid #000; }
        }
    </style>
</head>
<body>

<div class="actions-bar">
    <div>
        <h2 style="font-size:1.1rem">🖨️ QR Codes das Turmas</h2>
        <p style="font-size:0.8rem;color:#64748b"><?= count($turmas) ?> turmas prontas para impressão em massa.</p>
    </div>
    <div style="display:flex;gap:0.75rem">
        <a href="/turmas" class="btn btn-secondary">← Voltar</a>
        <button onclick="window.print()" class="btn btn-primary">Imprimir / Salvar PDF</button>
    </div>
</div>

<div class="print-grid">
    <?php foreach ($turmas as $turma): 
        $payload = json_encode([
            'turma_id' => (int)$turma->id,
            'qr_token' => $turma->qr_token,
        ]);
    ?>
    <div class="qr-card">
        <div class="school-name">FrequenciaSmart – Controle de Frequência</div>
        <img src="<?= e(qr_url($payload, 180)) ?>" class="qr-img" alt="QR Code">
        <div class="turma-nome"><?= e($turma->nome) ?></div>
        <div class="turma-tag"><?= e($turma->turno) ?></div>
        <div class="token-info">Token: <?= e($turma->qr_token) ?></div>
    </div>
    <?php endforeach; ?>
</div>

</body>
</html>
