<?php
// pages/alunos_qrcode.php — QR Code individual do aluno
requer_login();

// $id vem do roteador
$aluno = db_one(
    "SELECT a.*, t.nome AS turma_nome, t.turno 
     FROM alunos a 
     LEFT JOIN turmas t ON t.id = a.turma_id 
     WHERE a.id = ? AND a.escola_id = ?",
    [$id, escola_id()]
);

if (!$aluno) {
    http_response_code(404);
    die('<p style="font-family:monospace;padding:2rem">Aluno não encontrado.</p>');
}

$payload = json_encode(['aluno_id' => (int)$aluno->id, 'qr_token' => $aluno->qr_token]);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code – <?= e($aluno->nome) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing:border-box;margin:0;padding:0; }
        body { font-family:'Inter',sans-serif;background:#fff; }
        .screen-bar { background:linear-gradient(135deg,#1e1b4b,#4f46e5);color:#fff;padding:.75rem 1.5rem;display:flex;align-items:center;justify-content:space-between; }
        .screen-bar h2 { font-size:1rem; }
        .container { padding:2rem;display:flex;justify-content:center; }
        .qr-card { border:2px solid #e2e8f0;border-radius:16px;padding:2rem 2.5rem;text-align:center;max-width:340px;width:100%; }
        .school-name { font-size:.75rem;font-weight:600;color:#64748b;letter-spacing:.05em;text-transform:uppercase;margin-bottom:1rem; }
        .aluno-nome { font-size:1.2rem;font-weight:700;color:#1e293b;margin-top:1.25rem; }
        .aluno-mat  { font-size:.85rem;color:#64748b;margin-top:.25rem; }
        .turma-tag  { display:inline-block;margin-top:.75rem;background:#dbeafe;color:#1d4ed8;padding:.3rem .9rem;border-radius:999px;font-size:.8rem;font-weight:600; }
        .token-label { margin-top:1.25rem;font-size:.65rem;color:#94a3b8;font-family:monospace;word-break:break-all; }
        .cut-line { margin:1.5rem 0;border:none;border-top:2px dashed #cbd5e1; }
        .actions { padding:0 2rem 1.5rem;display:flex;gap:1rem;justify-content:center; }
        .btn { padding:.6rem 1.25rem;border-radius:8px;font-size:.875rem;font-weight:500;cursor:pointer;border:none;font-family:inherit;text-decoration:none;display:inline-flex;align-items:center;gap:.4rem; }
        .btn-print { background:#4f46e5;color:#fff; }
        .btn-back  { background:#f1f5f9;color:#475569; }
        @media print { .screen-bar,.actions { display:none!important; } body{padding:0;} .container{padding:1cm;} .qr-card{border:1.5px solid #000;page-break-inside:avoid;} }
    </style>
</head>
<body>

<div class="screen-bar">
    <h2>📱 QR Code do Aluno</h2>
    <span style="font-size:.8rem;opacity:.8">FrequenciaSmart</span>
</div>

<div class="actions">
    <button class="btn btn-print" onclick="window.print()">🖨️ Imprimir / Salvar PDF</button>
    <a class="btn btn-back" href="/alunos">← Voltar para lista</a>
</div>

<div class="container">
    <div class="qr-card">
        <div class="school-name">FrequenciaSmart – Controle de Frequência</div>

        <img src="<?= e(qr_url($payload, 220)) ?>" alt="QR Code de <?= e($aluno->nome) ?>"
             style="width:220px;height:220px;border-radius:8px">

        <div class="aluno-nome"><?= e($aluno->nome) ?></div>
        <div class="aluno-mat">Matrícula: <?= e($aluno->matricula) ?></div>

        <?php if ($aluno->turma_nome): ?>
        <div class="turma-tag"><?= e($aluno->turma_nome) ?> – <?= e($aluno->turno) ?></div>
        <?php endif; ?>

        <hr class="cut-line">
        <div class="token-label">Token: <?= e($aluno->qr_token) ?></div>
    </div>
</div>

</body>
</html>
