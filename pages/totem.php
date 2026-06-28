<?php
// pages/totem.php — Interface do Totem de Autoatendimento com Reconhecimento Facial
requer_login();
requer_role('DIRETOR', 'VICE', 'ORIENTADORA', 'ASSISTENTE');

// Carrega todos os alunos da escola com face cadastrada
$alunosComFace = db_all(
    "SELECT a.id, a.nome, a.face_descriptor, t.nome AS turma_nome 
     FROM alunos a 
     JOIN turmas t ON t.id = a.turma_id
     WHERE a.escola_id = ? AND a.ativo = 1 AND a.face_descriptor IS NOT NULL AND t.ativa = 1",
    [escola_id()]
);

$escola = db_one("SELECT nome FROM escolas WHERE id = ?", [escola_id()]);

$tituloPagina = "Totem de Reconhecimento Facial — " . ($escola->nome ?? 'Escola');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($tituloPagina) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4f46e5;
            --success: #10b981;
            --danger: #ef4444;
            --background: #0f172a;
            --card: #1e293b;
            --text: #f8fafc;
            --text-sub: #94a3b8;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Outfit', sans-serif;
        }

        body {
            background-color: var(--background);
            color: var(--text);
            height: 100vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        /* HEADER */
        header {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(12px);
            padding: 1.25rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            z-index: 10;
        }

        .escola-info h1 {
            font-size: 1.4rem;
            font-weight: 800;
            letter-spacing: -0.02em;
            color: var(--text);
        }

        .escola-info p {
            font-size: 0.85rem;
            color: var(--text-sub);
        }

        .clock-container {
            text-align: right;
        }

        #clock {
            font-size: 1.6rem;
            font-weight: 800;
            color: #6366f1;
            line-height: 1;
        }

        #date {
            font-size: 0.8rem;
            color: var(--text-sub);
            margin-top: 2px;
        }

        /* MAIN CONTENT */
        main {
            flex: 1;
            display: flex;
            position: relative;
            padding: 2rem;
            gap: 2rem;
            align-items: center;
            justify-content: center;
        }

        /* CAMERA BOX */
        .camera-panel {
            position: relative;
            width: 100%;
            max-width: 640px;
            aspect-ratio: 4/3;
            border-radius: 24px;
            overflow: hidden;
            border: 4px solid rgba(255, 255, 255, 0.03);
            background: #000;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }

        #webcam {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transform: scaleX(-1);
        }

        #overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            transform: scaleX(-1);
        }

        .scan-line {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(to bottom, transparent, rgba(99, 102, 241, 0.8), transparent);
            animation: scan 3s linear infinite;
            pointer-events: none;
            z-index: 5;
        }

        @keyframes scan {
            0% { top: 0%; }
            50% { top: 100%; }
            100% { top: 0%; }
        }

        /* STATUS BADGE */
        .status-badge {
            position: absolute;
            top: 1.5rem;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(15, 23, 42, 0.85);
            backdrop-filter: blur(8px);
            padding: 0.6rem 1.5rem;
            border-radius: 9999px;
            font-size: 0.9rem;
            font-weight: 600;
            border: 1px solid rgba(255, 255, 255, 0.05);
            z-index: 8;
            letter-spacing: 0.02em;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .pulse-dot {
            width: 8px;
            height: 8px;
            background-color: var(--primary);
            border-radius: 50%;
            animation: pulse 1.5s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(0.9); opacity: 1; }
            50% { transform: scale(1.4); opacity: 0.4; }
            100% { transform: scale(0.9); opacity: 1; }
        }

        /* POPUP CONFIRMAÇÃO */
        .confirmation-card {
            position: absolute;
            bottom: 2rem;
            left: 50%;
            transform: translateX(-50%) translateY(150px);
            width: 90%;
            max-width: 480px;
            background: rgba(30, 41, 59, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 1.5rem 2rem;
            display: flex;
            align-items: center;
            gap: 1.25rem;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            border: 1px solid rgba(16, 185, 129, 0.2);
            transition: transform 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            z-index: 15;
        }

        .confirmation-card.show {
            transform: translateX(-50%) translateY(0);
        }

        .avatar-box {
            width: 64px;
            height: 64px;
            background: rgba(16, 185, 129, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            border: 2px solid var(--success);
        }

        .info-box h2 {
            font-size: 1.2rem;
            font-weight: 800;
            color: var(--text);
        }

        .info-box p {
            font-size: 0.85rem;
            color: var(--text-sub);
            margin-top: 2px;
        }

        /* LOADING MODELS SCREEN */
        #loadingScreen {
            position: fixed;
            inset: 0;
            background: var(--background);
            z-index: 100;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .loader-ring {
            width: 80px;
            height: 80px;
            border: 6px dashed var(--primary);
            border-radius: 50%;
            animation: spin 1.5s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* BACK BUTTON */
        .btn-voltar {
            background: rgba(255,255,255,0.05);
            color: var(--text);
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.85rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-voltar:hover {
            background: rgba(255,255,255,0.1);
        }
    </style>
</head>
<body>

    <div id="loadingScreen">
        <div class="loader-ring"></div>
        <h2 style="margin-top: 2rem; font-weight: 800;">Inicializando Reconhecimento Facial</h2>
        <p style="color: var(--text-sub); font-size: 0.9rem; margin-top: 0.5rem;">Carregando banco de biometria e modelos de IA...</p>
    </div>

    <header>
        <div style="display: flex; align-items: center; gap: 1.5rem;">
            <a href="/dashboard" class="btn-voltar">← Voltar</a>
            <div class="escola-info">
                <h1>🏢 <?= e($escola->nome ?? 'Escola Municipal') ?></h1>
                <p>Totem de Presença por Reconhecimento Facial</p>
            </div>
        </div>
        <div class="clock-container">
            <div id="clock">00:00:00</div>
            <div id="date">Carregando data...</div>
        </div>
    </header>

    <main>
        <div class="status-badge" id="totemStatus">
            <div class="pulse-dot"></div>
            <span>Inicializando câmera...</span>
        </div>

        <div class="camera-panel">
            <div class="scan-line"></div>
            <video id="webcam" autoplay muted playsinline></video>
            <canvas id="overlay"></canvas>
        </div>

        <!-- Pop-up de Confirmação -->
        <div class="confirmation-card" id="confirmCard">
            <div class="avatar-box" id="confirmAvatar">🎓</div>
            <div class="info-box">
                <h2 id="confirmNome">Nome do Aluno</h2>
                <p id="confirmTurma">Turma - Turno</p>
                <p id="confirmHora" style="font-weight: 600; color: var(--success); font-size: 0.8rem;"></p>
            </div>
        </div>
    </main>

    <!-- Importa face-api.js -->
    <script src="https://unpkg.com/face-api.js@0.22.2/dist/face-api.min.js"></script>

    <script>
        // Dados dos alunos convertidos para formato Float32Array para comparação rápida
        const alunos = [
            <?php foreach ($alunosComFace as $a): ?>
            {
                id: <?= $a->id ?>,
                nome: <?= json_encode($a->nome) ?>,
                turma: <?= json_encode($a->turma_nome) ?>,
                descriptor: new Float32Array(<?= $a->face_descriptor ?>)
            },
            <?php endforeach; ?>
        ];

        const video = document.getElementById('webcam');
        const canvas = document.getElementById('overlay');
        const loadingScreen = document.getElementById('loadingScreen');
        const statusBadge = document.getElementById('totemStatus');
        const confirmCard = document.getElementById('confirmCard');
        const confirmNome = document.getElementById('confirmNome');
        const confirmTurma = document.getElementById('confirmTurma');
        const confirmHora = document.getElementById('confirmHora');
        const confirmAvatar = document.getElementById('confirmAvatar');

        const MODEL_URL = 'https://justadudewhohacks.github.io/face-api.js/models/';
        let faceMatcher = null;
        let localStream = null;
        let isProcessing = false;
        let lastScannedId = null;
        let clearScanTimeout = null;
        let detectionInterval = null;

        // Atualizar Relógio do Totem
        function updateClock() {
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');
            document.getElementById('clock').textContent = `${hours}:${minutes}:${seconds}`;

            const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            document.getElementById('date').textContent = now.toLocaleDateString('pt-BR', options);
        }
        setInterval(updateClock, 1000);
        updateClock();

        // Inicializar Modelos e Banco Local de Faces
        async function init() {
            try {
                // Carrega pesos dos modelos (TINY)
                await faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL);
                await faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL);
                await faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL);

                // Cria o matcher se houver alunos
                if (alunos.length > 0) {
                    const labeledDescriptors = alunos.map(a => {
                        return new faceapi.LabeledFaceDescriptors(
                            String(a.id),
                            [a.descriptor]
                        );
                    });
                    faceMatcher = new faceapi.FaceMatcher(labeledDescriptors, 0.45); // Threshold de confiança (menor = mais rigoroso)
                }

                loadingScreen.style.display = 'none';
                statusBadge.querySelector('span').textContent = 'Mapeando ambiente...';

                startWebcam();
            } catch (err) {
                console.error(err);
                alert('Erro de carregamento do sistema. Verifique a internet do Totem.');
            }
        }

        // Iniciar WebCam
        function startWebcam() {
            // Registra o listener ANTES de abrir a webcam para evitar race conditions
            video.addEventListener('play', onPlay);

            navigator.mediaDevices.getUserMedia({
                video: {
                    width: { ideal: 640 },
                    height: { ideal: 480 },
                    facingMode: 'user'
                }
            })
            .then(stream => {
                video.srcObject = stream;
                localStream = stream;
                // Se a câmera já começou a reproduzir antes do listener disparar
                if (video.readyState >= 2) {
                    onPlay();
                }
            })
            .catch(err => {
                console.error(err);
                statusBadge.querySelector('span').textContent = '⚠️ Câmera desconectada';
                statusBadge.style.borderColor = 'var(--danger)';
            });
        }

        // Loop de reconhecimento facial contínuo
        function onPlay() {
            if (detectionInterval) clearInterval(detectionInterval);

            let displaySize = { width: video.videoWidth, height: video.videoHeight };
            if (displaySize.width > 0 && displaySize.height > 0) {
                faceapi.matchDimensions(canvas, displaySize);
            }

            statusBadge.querySelector('span').textContent = 'Aproxime-se para registrar';
            statusBadge.querySelector('.pulse-dot').style.backgroundColor = 'var(--success)';

            detectionInterval = setInterval(async () => {
                if (isProcessing) return;

                // Redimensiona o canvas caso as dimensões tenham carregado após a inicialização
                if (video.videoWidth > 0 && (displaySize.width !== video.videoWidth || displaySize.height !== video.videoHeight)) {
                    displaySize = { width: video.videoWidth, height: video.videoHeight };
                    faceapi.matchDimensions(canvas, displaySize);
                }

                if (displaySize.width === 0 || displaySize.height === 0) return;

                // Usa TinyFaceDetector para rodar de forma ultra-rápida mesmo em dispositivos móveis
                const detection = await faceapi.detectSingleFace(video, new faceapi.TinyFaceDetectorOptions())
                    .withFaceLandmarks()
                    .withFaceDescriptor();

                canvas.getContext('2d').clearRect(0, 0, canvas.width, canvas.height);

                if (detection && faceMatcher) {
                    const resizedDetection = faceapi.resizeResults(detection, displaySize);
                    
                    // Desenha marcação no canvas
                    faceapi.draw.drawDetections(canvas, resizedDetection);

                    // Realiza a comparação do rosto
                    const bestMatch = faceMatcher.findBestMatch(detection.descriptor);
                    
                    if (bestMatch.label !== 'unknown') {
                        const alunoId = parseInt(bestMatch.label);
                        
                        // Impede de escanear repetidamente o mesmo aluno no mesmo loop curto
                        if (alunoId !== lastScannedId) {
                            registrarPresenca(alunoId);
                        }
                    }
                }
            }, 200);
        }

        // Chamar API para registrar frequência no banco
        function registrarPresenca(alunoId) {
            isProcessing = true;
            lastScannedId = alunoId;
            
            // Timeout de 6 segundos para liberar biometria do mesmo aluno
            clearTimeout(clearScanTimeout);
            clearScanTimeout = setTimeout(() => {
                lastScannedId = null;
            }, 6000);

            const formData = new FormData();
            formData.append('aluno_id', alunoId);

            fetch('/api/totem/registrar', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.sucesso) {
                    // Exibir card de confirmação com animação
                    confirmNome.textContent = data.aluno_nome;
                    confirmTurma.textContent = data.turma_nome;
                    
                    if (data.ja_registrado) {
                        confirmHora.textContent = `Frequência já registrada às ${data.hora}!`;
                        confirmHora.style.color = 'var(--primary)';
                        confirmAvatar.textContent = '👌';
                        confirmAvatar.style.borderColor = 'var(--primary)';
                        falarMensagem(`Olá, ${data.aluno_nome.split(' ')[0]}. Sua frequência já foi registrada.`);
                    } else {
                        confirmHora.textContent = `Registrado às ${data.hora} com sucesso!`;
                        confirmHora.style.color = 'var(--success)';
                        confirmAvatar.textContent = '🎓';
                        confirmAvatar.style.borderColor = 'var(--success)';
                        falarMensagem(`Olá, ${data.aluno_nome.split(' ')[0]}. Presença confirmada!`);
                    }

                    confirmCard.classList.add('show');

                    // Oculta o card após 4.5 segundos
                    setTimeout(() => {
                        confirmCard.classList.remove('show');
                        isProcessing = false;
                    }, 4500);
                } else {
                    isProcessing = false;
                }
            })
            .catch(err => {
                console.error(err);
                isProcessing = false;
            });
        }

        // Sintetizar voz dando boas vindas
        function falarMensagem(texto) {
            if ('speechSynthesis' in window) {
                window.speechSynthesis.cancel(); // Para fala anterior
                const utterance = new SpeechSynthesisUtterance(texto);
                utterance.lang = 'pt-BR';
                utterance.rate = 1.05;
                window.speechSynthesis.speak(utterance);
            }
        }

        window.onload = init;
    </script>
</body>
</html>
