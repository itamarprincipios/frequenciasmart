<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code – {{ $aluno->nome }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <!-- QR Code JS library -->
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; background: #fff; }

        /* Tela normal (pré-impressão) */
        .screen-bar {
            background: linear-gradient(135deg,#1e1b4b,#4f46e5);
            color:#fff; padding:.75rem 1.5rem;
            display:flex; align-items:center; justify-content:space-between;
        }
        .screen-bar h2 { font-size:1rem; }

        .container { padding:2rem; display:flex; justify-content:center; }

        /* Card de QR Code */
        .qr-card {
            border:2px solid #e2e8f0; border-radius:16px;
            padding:2rem 2.5rem; text-align:center;
            max-width:340px; width:100%;
        }
        .school-name { font-size:.75rem; font-weight:600; color:#64748b; letter-spacing:.05em; text-transform:uppercase; margin-bottom:1rem; }
        #qrcode { display:flex; justify-content:center; margin: 0 auto; }
        #qrcode img { border-radius: 8px; }
        .aluno-nome { font-size:1.2rem; font-weight:700; color:#1e293b; margin-top:1.25rem; }
        .aluno-mat  { font-size:.85rem; color:#64748b; margin-top:.25rem; }
        .turma-tag  {
            display:inline-block; margin-top:.75rem;
            background:#dbeafe; color:#1d4ed8;
            padding:.3rem .9rem; border-radius:999px;
            font-size:.8rem; font-weight:600;
        }
        .token-label {
            margin-top:1.25rem; font-size:.65rem; color:#94a3b8;
            font-family:monospace; word-break:break-all;
        }
        .cut-line {
            margin:1.5rem 0; border:none;
            border-top:2px dashed #cbd5e1;
        }

        /* Botões de tela */
        .actions { padding:0 2rem 1.5rem; display:flex; gap:1rem; justify-content:center; }
        .btn { padding:.6rem 1.25rem; border-radius:8px; font-size:.875rem; font-weight:500;
               cursor:pointer; border:none; font-family:inherit; text-decoration:none; display:inline-flex; align-items:center; gap:.4rem; }
        .btn-print   { background:#4f46e5; color:#fff; }
        .btn-back    { background:#f1f5f9; color:#475569; }

        /* IMPRESSÃO */
        @media print {
            .screen-bar, .actions { display: none !important; }
            body { padding: 0; }
            .container { padding: 1cm; }
            .qr-card { border: 1.5px solid #000; page-break-inside: avoid; }
        }
    </style>
</head>
<body>

<!-- Barra do sistema (não aparece na impressão) -->
<div class="screen-bar">
    <h2>📱 QR Code do Aluno</h2>
    <span style="font-size:.8rem;opacity:.8">EduTrack</span>
</div>

<div class="actions">
    <button class="btn btn-print" onclick="window.print()">🖨️ Imprimir / Salvar PDF</button>
    <a class="btn btn-back" href="/alunos">← Voltar para lista</a>
</div>

<div class="container">
    <div class="qr-card">
        <div class="school-name">EduTrack – Controle de Frequência</div>

        <!-- QR Code gerado por JavaScript -->
        <div id="qrcode"></div>

        <div class="aluno-nome">{{ $aluno->nome }}</div>
        <div class="aluno-mat">Matrícula: {{ $aluno->matricula }}</div>

        @if($aluno->turma)
        <div class="turma-tag">{{ $aluno->turma->nome }} – {{ $aluno->turma->turno }}</div>
        @endif

        <hr class="cut-line">

        <div class="token-label">Token: {{ $aluno->qr_token }}</div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        new QRCode(document.getElementById('qrcode'), {
            text: @json($aluno->qrPayload()),
            width: 220,
            height: 220,
            colorDark: '#1e293b',
            colorLight: '#ffffff',
            correctLevel: QRCode.CorrectLevel.M
        });
    });
</script>

</body>
</html>
