<?php
// pages/alunos_face.php — Interface para capturar foto e cadastrar assinatura facial
requer_login();
requer_role('DIRETOR', 'VICE', 'ORIENTADORA');

// $id vem do roteador
$aluno = db_one(
    "SELECT a.*, t.nome AS turma_nome 
     FROM alunos a 
     LEFT JOIN turmas t ON t.id=a.turma_id 
     WHERE a.id = ? AND a.escola_id = ?", 
    [$id, escola_id()]
);

if (!$aluno) {
    http_response_code(404);
    die('<p style="font-family:monospace;padding:2rem">Aluno não encontrado ou sem permissão.</p>');
}

$tituloPagina = "Mapear Rosto — " . $aluno->nome;
include __DIR__ . '/../layout/header.php';
?>

<div style="max-width: 680px; margin: 0 auto;">
    <div class="table-wrap">
        <div class="table-head">
            <h3>📸 Mapeamento Facial - FACEID — <?= e($aluno->nome) ?></h3>
            <a href="/alunos" class="btn btn-outline" style="font-size:.8rem">← Voltar</a>
        </div>
        <div style="padding: 1.5rem;">
            <p style="color: #64748b; font-size: 0.9rem; margin-bottom: 1.5rem;">
                Posicione o aluno de frente para a câmera em um local bem iluminado. O sistema detectará automaticamente o rosto e extrairá a assinatura biométrica para o Totem.
            </p>

            <div id="loaderModels" style="text-align: center; padding: 2rem;">
                <div style="font-size: 2.5rem; animation: spin 1s linear infinite; display: inline-block;">⚙️</div>
                <h4 style="margin-top: 1rem; color: #475569;">Carregando modelos de inteligência artificial...</h4>
                <p style="font-size: 0.8rem; color: #94a3b8;">Isso pode levar alguns segundos no primeiro acesso.</p>
            </div>

            <div id="camContainer" style="display: none; position: relative; width: 100%; max-width: 480px; margin: 0 auto; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);">
                <video id="webcam" autoplay muted playsinline style="width: 100%; display: block; background: #000; transform: scaleX(-1);"></video>
                <canvas id="overlay" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none; transform: scaleX(-1);"></canvas>
                
                <div id="scanStatus" style="position: absolute; bottom: 1rem; left: 50%; transform: translateX(-50%); background: rgba(15, 23, 42, 0.85); color: #fff; padding: 0.5rem 1rem; border-radius: 9999px; font-size: 0.8rem; pointer-events: none; white-space: nowrap;">
                    Aguardando detecção de rosto...
                </div>
            </div>

            <div id="actionButtons" style="display: none; text-align: center; margin-top: 1.5rem; gap: 1rem; justify-content: center;">
                <button id="btnCapture" class="btn btn-primary" style="padding: 0.8rem 1.8rem;">
                    📸 Salvar Biometria Facial
                </button>
                <button id="btnRetry" class="btn btn-outline" style="padding: 0.8rem 1.8rem;" onclick="restartCapture()">
                    🔄 Tentar Novamente
                </button>
            </div>

            <div id="successCard" style="display: none; background: #f0fdf4; border: 1px solid #bbf7d0; padding: 1.5rem; border-radius: 8px; text-align: center; margin-top: 1rem;">
                <div style="font-size: 3rem; color: #166534;">✅</div>
                <h4 style="color: #166534; margin-top: 0.5rem;">Biometria Facial Cadastrada!</h4>
                <p style="font-size: 0.85rem; color: #15803d; margin-bottom: 1.5rem;">A assinatura facial foi salva no banco de dados. O aluno já pode usar o Totem.</p>
                <a href="/alunos" class="btn btn-primary" style="display: inline-block;">Ir para Alunos</a>
            </div>

            <!-- CONSOLE DE DEBUG VISUAL -->
            <div id="consoleLog" style="margin-top: 1.5rem; background: #fff1f2; border: 1px solid #fda4af; border-radius: 8px; padding: 1rem; color: #9f1239; font-family: monospace; font-size: 0.8rem; display: none;">
                <h5 style="margin-bottom: 0.5rem; font-weight: bold; display: flex; align-items: center; gap: 6px;">🔴 Log de Depuração (Debug Console):</h5>
                <ul id="logList" style="margin: 0; padding-left: 1.25rem;"></ul>
            </div>
        </div>
    </div>
</div>

<style>
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>

<!-- Importa face-api.js -->
<script src="https://unpkg.com/face-api.js@0.22.2/dist/face-api.min.js"></script>

<script>
const video = document.getElementById('webcam');
const canvas = document.getElementById('overlay');
const loader = document.getElementById('loaderModels');
const container = document.getElementById('camContainer');
const statusText = document.getElementById('scanStatus');
const actionButtons = document.getElementById('actionButtons');
const btnCapture = document.getElementById('btnCapture');
const successCard = document.getElementById('successCard');

let localStream = null;
let currentDescriptor = null;
let detectionInterval = null;

// URL base para carregar os pesos/modelos
const MODEL_URL = 'https://justadudewhohacks.github.io/face-api.js/models/';

// Função para registrar erros visualmente na tela
function logDebug(msg) {
    console.log(msg);
    const consoleLog = document.getElementById('consoleLog');
    const logList = document.getElementById('logList');
    consoleLog.style.display = 'block';
    const li = document.createElement('li');
    li.textContent = `[${new Date().toLocaleTimeString()}] ${msg}`;
    logList.appendChild(li);
}

// Captura erros globais
window.onerror = function(message, source, lineno, colno, error) {
    logDebug(`Erro Global: ${message} na linha ${lineno}`);
    return false;
};

async function init() {
    try {
        logDebug("Iniciando carregamento dos modelos de IA...");
        // Carrega os 3 modelos necessários para detecção ultra-leve (TINY), landmarks e reconhecimento
        await faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL);
        logDebug("Modelo Detector carregado.");
        await faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL);
        logDebug("Modelo Landmarks carregado.");
        await faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL);
        logDebug("Modelo Recognition carregado.");

        // Oculta loader e exibe câmera
        loader.style.display = 'none';
        container.style.display = 'block';

        startWebcam();
    } catch (err) {
        logDebug('Falha no init(): ' + err.message);
    }
}

function startWebcam() {
    logDebug("Requisitando acesso à câmera...");
    video.addEventListener('play', onPlay);

    navigator.mediaDevices.getUserMedia({ 
        video: { 
            width: { ideal: 640 }, 
            height: { ideal: 480 },
            facingMode: 'user'
        } 
    })
    .then(stream => {
        logDebug("Acesso à câmera concedido.");
        video.srcObject = stream;
        localStream = stream;
        if (video.readyState >= 2) {
            onPlay();
        }
    })
    .catch(err => {
        logDebug('Falha ao abrir webcam: ' + err.message);
        alert('Câmera não encontrada ou acesso negado.');
    });
}

function onPlay() {
    logDebug("Inicializando loop de detecção facial...");
    if (detectionInterval) clearInterval(detectionInterval);

    let displaySize = { width: video.videoWidth, height: video.videoHeight };
    logDebug(`Dimensões iniciais do vídeo: ${displaySize.width}x${displaySize.height}`);
    if (displaySize.width > 0 && displaySize.height > 0) {
        faceapi.matchDimensions(canvas, displaySize);
    }

    detectionInterval = setInterval(async () => {
        try {
            if (video.videoWidth > 0 && (displaySize.width !== video.videoWidth || displaySize.height !== video.videoHeight)) {
                displaySize = { width: video.videoWidth, height: video.videoHeight };
                faceapi.matchDimensions(canvas, displaySize);
                logDebug(`Dimensões do vídeo atualizadas: ${displaySize.width}x${displaySize.height}`);
            }

            if (displaySize.width === 0 || displaySize.height === 0) return;

            const detection = await faceapi.detectSingleFace(video, new faceapi.TinyFaceDetectorOptions())
                .withFaceLandmarks()
                .withFaceDescriptor();

            canvas.getContext('2d').clearRect(0, 0, canvas.width, canvas.height);

            if (detection) {
                const resizedDetections = faceapi.resizeResults(detection, displaySize);
                faceapi.draw.drawDetections(canvas, resizedDetections);
                
                statusText.innerHTML = '🟢 <strong style="color:#10b981">Rosto Detectado!</strong> Mantenha a posição.';
                actionButtons.style.display = 'flex';
                currentDescriptor = detection.descriptor;
            } else {
                statusText.innerHTML = 'Aguardando detecção de rosto...';
                currentDescriptor = null;
            }
        } catch (err) {
            logDebug('Erro no loop de detecção: ' + err.message);
            clearInterval(detectionInterval);
        }
    }, 200);
}

function restartCapture() {
    actionButtons.style.display = 'none';
    successCard.style.display = 'none';
    container.style.display = 'block';
    if (!localStream) {
        startWebcam();
    }
}

// Evento do botão de salvar
btnCapture.addEventListener('click', () => {
    if (!currentDescriptor) {
        alert('Nenhum rosto mapeado no momento. Fique de frente para a câmera.');
        return;
    }

    clearInterval(detectionInterval);
    if (localStream) {
        localStream.getTracks().forEach(track => track.stop());
        localStream = null;
    }

    statusText.innerHTML = 'Salvando assinatura facial...';
    
    // Envia o array de 128 posições por AJAX
    const formData = new FormData();
    formData.append('face_descriptor', JSON.stringify(Array.from(currentDescriptor)));
    formData.append('csrf_token', '<?= csrf_token() ?>');

    fetch('/alunos/<?= $aluno->id ?>/face', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.sucesso) {
            container.style.display = 'none';
            actionButtons.style.display = 'none';
            successCard.style.display = 'block';
        } else {
            alert('Erro ao salvar: ' + (data.erro || 'Desconhecido'));
            restartCapture();
        }
    })
    .catch(err => {
        console.error(err);
        alert('Erro de conexão com o servidor.');
        restartCapture();
    });
});

// Inicializa a carga dos modelos
window.onload = init;
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>
