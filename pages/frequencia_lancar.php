<?php
// pages/frequencia_lancar.php — Lançamento de frequência via QR Code
requer_login();

$turmas = db_all("SELECT * FROM turmas WHERE ativa = 1 ORDER BY nome");

// Pré-carregar todos os alunos por turma (para uso no JS)
$alunosPorTurma = [];
foreach ($turmas as $turma) {
    $alunos = db_all(
        "SELECT id, nome, matricula, qr_token FROM alunos WHERE turma_id = ? AND ativo = 1 ORDER BY nome",
        [$turma->id]
    );
    $alunosPorTurma[$turma->id] = $alunos;
}

$tituloPagina = 'Lançar Frequência';
include __DIR__ . '/../layout/header.php';
?>

<div id="app">
    <!-- ETAPA 1: Identificar Turma -->
    <div id="etapa1" class="table-wrap" style="max-width:500px;margin:0 auto;">
        <div class="table-head"><h3>🏫 Identificar Turma</h3></div>
        <div style="padding:1.5rem;">
            <p style="text-align:center;margin-bottom:1.5rem;color:#475569;">
                Escaneie o QR Code da turma para iniciar a chamada.
            </p>

            <div id="qr-reader-turma" style="width:100%;margin:0 auto;border-radius:8px;overflow:hidden;"></div>

            <div id="scanErroTurma" class="alert alert-error" style="display:none;margin-top:.75rem"></div>

            <div class="form-group" style="margin-top:1.5rem;">
                <label>Data da Chamada</label>
                <input type="date" class="form-control" id="data" value="<?= date('Y-m-d') ?>">
            </div>

            <div style="margin-top:2rem;text-align:center;">
                <button class="btn btn-outline" onclick="toggleManual()" style="font-size:.75rem">Problemas com a câmera?</button>
            </div>

            <div id="selecaoManual" style="display:none;margin-top:1rem;border-top:1px dashed #cbd5e1;padding-top:1rem;">
                <label style="display:block;font-size:.8rem;margin-bottom:.5rem;">Seleção Manual de Emergência</label>
                <select class="form-control" id="turmaSelectManual" onchange="selecionarTurmaManual(this.value)">
                    <option value="">-- Escolha a Turma --</option>
                    <?php foreach ($turmas as $turma): ?>
                    <option value="<?= e($turma->id) ?>"><?= e($turma->nome) ?> – <?= e($turma->turno) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>

    <!-- ETAPA 2: Scanner + Lista de alunos -->
    <div id="etapa2" style="display:none;">
        <div class="cards" style="grid-template-columns:1fr 1fr 1fr;">
            <div class="card green">
                <div class="card-label">Presentes</div>
                <div class="card-value" id="ctPresentes" style="color:var(--success)">0</div>
            </div>
            <div class="card red">
                <div class="card-label">Faltantes</div>
                <div class="card-value" id="ctFaltantes" style="color:var(--danger)">0</div>
            </div>
            <div class="card">
                <div class="card-label">Total</div>
                <div class="card-value" id="ctTotal">0</div>
            </div>
        </div>

        <div class="table-wrap" style="margin-bottom:1rem;">
            <div class="table-head">
                <h3>📷 Escanear Alunos</h3>
                <div>
                    <button class="btn btn-outline" id="btnScannerToggle" onclick="toggleScannerAluno()" style="font-size:.75rem">▶️ Escanear</button>
                </div>
            </div>
            <div style="padding:1rem;">
                <div id="qr-reader-aluno" style="width:100%;max-width:400px;margin:0 auto;"></div>
                <div id="ultimoScan" class="alert alert-success" style="display:none;margin-top:.75rem;margin-bottom:0;"></div>
                <div id="scanErroAluno" class="alert alert-error" style="display:none;margin-top:.75rem;margin-bottom:0;"></div>
            </div>
        </div>

        <div class="table-wrap">
            <div class="table-head">
                <h3>👥 Turma: <span id="turmaNomeTitulo" style="color:var(--primary)"></span></h3>
                <span class="badge badge-blue" id="dataTitulo"></span>
            </div>
            <table>
                <thead><tr><th>Nome</th><th>Status</th></tr></thead>
                <tbody id="listaAlunos"></tbody>
            </table>
        </div>

        <div style="display:flex;gap:1rem;margin-top:1rem;flex-wrap:wrap;">
            <button class="btn btn-primary" style="flex:1;justify-content:center;padding:.75rem;" onclick="finalizarChamada()">
                ✅ Finalizar Chamada
            </button>
            <button class="btn btn-outline" style="justify-content:center;padding:.75rem;" onclick="voltarEtapa1()">
                ← Trocar Turma
            </button>
        </div>
    </div>

    <!-- ETAPA 3: Confirmação -->
    <div id="etapa3" style="display:none;" class="table-wrap" style2="max-width:500px;margin:0 auto;">
        <div class="table-head"><h3>📊 Resumo da Chamada</h3></div>
        <div style="padding:1.5rem;text-align:center;">
            <div class="cards" style="grid-template-columns:1fr 1fr;margin-bottom:1.5rem;">
                <div class="card green">
                    <div class="card-label">Presentes</div>
                    <div class="card-value" id="resumoPresentes" style="color:var(--success)">0</div>
                </div>
                <div class="card red">
                    <div class="card-label">Faltas</div>
                    <div class="card-value" id="resumoFaltas" style="color:var(--danger)">0</div>
                </div>
            </div>
            <p style="margin-bottom:1rem;font-size:.9rem;color:#475569;">
                Confirmar a frequência de <strong id="resumoTurma"></strong> para <strong id="resumoData"></strong>?
            </p>
            <form method="POST" action="/frequencia/registrar" id="formFinal">
                <?php csrf_field(); ?>
                <input type="hidden" name="turma_id" id="inputTurmaId">
                <input type="hidden" name="data" id="inputData">
                <div id="inputsPresentes"></div>
                <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;padding:.75rem;">
                    💾 Confirmar e Salvar
                </button>
            </form>
            <button class="btn btn-outline" style="width:100%;justify-content:center;padding:.75rem;margin-top:.5rem;" onclick="voltarParaScanner()">
                ← Voltar ao Scanner
            </button>
        </div>
    </div>
</div>

<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
const turmas = <?= json_encode($turmas) ?>;
const papelUsuario = '<?= $_SESSION['usuario']['role'] ?>';

let turmaId = '', turmaNome = '', alunos = [], presentes = [];
let scannerTurma = null, scannerAluno = null, scannerAtivo = false;

// --- VALIDAÇÃO DE TURNO ---
function getPeriodoAtual() {
    const hora = new Date().getHours();
    const min  = new Date().getMinutes();
    const agora = hora + (min / 60);

    if (agora >= 5 && agora <= 12.5) return 'MANHA';
    if (agora > 12.5 && agora <= 18.5) return 'TARDE';
    return 'NOITE';
}

// --- ETAPAS ---
function mostrar(etapa) {
    document.getElementById('etapa1').style.display = (etapa===1)?'block':'none';
    document.getElementById('etapa2').style.display = (etapa===2)?'block':'none';
    document.getElementById('etapa3').style.display = (etapa===3)?'block':'none';
}

// --- SCANNER TURMA ---
function startScannerTurma() {
    const el = document.getElementById('qr-reader-turma');
    if (!el) return;
    if (scannerTurma) { try { scannerTurma.clear(); } catch(e){} }
    scannerTurma = new Html5Qrcode("qr-reader-turma");
    scannerTurma.start(
        { facingMode:"environment" },
        { fps:10, qrbox:{width:250,height:250} },
        (text) => onScanTurma(text),
        ()=>{}
    ).catch(()=>{
        document.getElementById('scanErroTurma').textContent='Erro na câmera. Use a seleção manual.';
        document.getElementById('scanErroTurma').style.display='block';
        document.getElementById('selecaoManual').style.display='block';
    });
}

function onScanTurma(text) {
    try {
        const payload = JSON.parse(text);
        if (!payload.turma_id || !payload.qr_token) throw new Error();
        const t = turmas.find(t => t.id == payload.turma_id);
        if (!t) { showErroTurma('Turma não encontrada!'); return; }
        if (t.qr_token !== payload.qr_token) { showErroTurma('QR Code inválido!'); return; }
        turmaId = payload.turma_id;
        scannerTurma.stop().then(() => iniciarChamada());
    } catch(e) { /* ignora frames ruins */ }
}

function toggleManual() {
    const el = document.getElementById('selecaoManual');
    el.style.display = el.style.display==='none' ? 'block' : 'none';
}

function selecionarTurmaManual(val) {
    if (!val) return;
    turmaId = val;
    if (scannerTurma) scannerTurma.stop().catch(()=>{});
    iniciarChamada();
}

function showErroTurma(msg) {
    const el = document.getElementById('scanErroTurma');
    el.textContent = msg;
    el.style.display = 'block';
    // Aumentado o tempo para 5 segundos para mensagens mais longas
    setTimeout(()=>{ el.style.display='none'; }, 5000);
}

// --- INICIAR CHAMADA ---
function iniciarChamada() {
    if (!turmaId) return;
    const t = turmas.find(t => t.id == turmaId);
    
    // Validação de Turno (Com Confirmação para correções)
    const periodoAtual = getPeriodoAtual();
    const dataSelecionada = document.getElementById('data').value;
    const dataHoje = new Date().toISOString().split('T')[0];

    if (t.turno !== periodoAtual) {
        const msg = `⚠️ Turno Diferente: Esta turma é do turno ${t.turno}, mas o período agora é ${periodoAtual}.\n\n` +
                    `Se você está tentando corrigir ou registrar uma chamada atrasada, clique em OK para continuar.\n\n` +
                    `Deseja prosseguir?`;
        
        if (!confirm(msg)) {
            // Reseta e volta
            if (scannerTurma && !scannerTurma.isScanning) startScannerTurma();
            document.getElementById('turmaSelectManual').value = '';
            turmaId = ''; 
            return;
        }
    }
    
    turmaNome = t ? (t.nome + ' – ' + t.turno) : 'Turma';
    alunos = alunosPorTurma[turmaId] || [];
    presentes = [];
    salvarEstado();
    mostrar(2);
    atualizarContadores();
    renderLista();
    document.getElementById('turmaNomeTitulo').textContent = turmaNome;
    document.getElementById('dataTitulo').textContent      = document.getElementById('data').value;
    setTimeout(() => startScannerAluno(), 800);
}

// --- SCANNER ALUNO ---
function startScannerAluno() {
    const el = document.getElementById('qr-reader-aluno');
    if (!el) return;
    if (scannerAluno) { try { scannerAluno.clear(); } catch(e){} }
    scannerAluno = new Html5Qrcode("qr-reader-aluno");
    scannerAtivo = true;
    document.getElementById('btnScannerToggle').textContent = '⏸️ Pausar';
    scannerAluno.start(
        { facingMode:"environment" },
        { fps:10, qrbox:{width:250,height:250} },
        (text) => onScanAluno(text),
        ()=>{}
    ).catch(err=>{
        document.getElementById('scanErroAluno').textContent = 'Falha ao abrir câmera: ' + err;
        document.getElementById('scanErroAluno').style.display='block';
        scannerAtivo = false;
    });
}

function onScanAluno(text) {
    document.getElementById('scanErroAluno').style.display='none';
    try {
        const payload = JSON.parse(text);
        const alunoId = payload.aluno_id;
        const qrToken = payload.qr_token;
        const aluno   = alunos.find(a => a.id === alunoId);
        if (!aluno) { showErroAluno('⚠️ Aluno não pertence a esta turma!'); return; }
        if (aluno.qr_token !== qrToken) { showErroAluno('⚠️ QR Code inválido!'); return; }
        if (presentes.includes(alunoId)) {
            mostrarUltimoScan('ℹ️ ' + aluno.nome + ' já foi lido.');
            return;
        }
        presentes.push(alunoId);
        salvarEstado();
        atualizarContadores();
        renderLista();
        mostrarUltimoScan('✅ ' + aluno.nome);
        playBeep();
        if (navigator.vibrate) navigator.vibrate(200);
    } catch(e) {}
}

function toggleScannerAluno() {
    if (scannerAtivo && scannerAluno) {
        scannerAluno.stop().then(()=>{ scannerAtivo=false; document.getElementById('btnScannerToggle').textContent='▶️ Escanear'; });
    } else {
        startScannerAluno();
    }
}

function showErroAluno(msg) {
    const el = document.getElementById('scanErroAluno');
    el.textContent = msg;
    el.style.display = 'block';
    if (navigator.vibrate) navigator.vibrate([100,50,100]);
    setTimeout(()=>{ el.style.display='none'; }, 3000);
}

function mostrarUltimoScan(msg) {
    const el = document.getElementById('ultimoScan');
    el.textContent = msg;
    el.style.display = 'block';
    setTimeout(()=>{ el.style.display='none'; }, 3000);
}

// --- LISTA E CONTADORES ---
function atualizarContadores() {
    document.getElementById('ctPresentes').textContent = presentes.length;
    document.getElementById('ctFaltantes').textContent = alunos.length - presentes.length;
    document.getElementById('ctTotal').textContent     = alunos.length;
}

function renderLista() {
    const tbody = document.getElementById('listaAlunos');
    tbody.innerHTML = '';
    alunos.forEach(a => {
        const presente = presentes.includes(a.id);
        const tr = document.createElement('tr');
        if (presente) tr.classList.add('row-presente');
        tr.innerHTML = `<td style="padding:1rem .75rem;">
                            <div style="display:flex; justify-content:space-between; align-items:center;">
                                <div>
                                    <strong style="display:block; font-size:1rem; margin-bottom:.2rem;">${a.nome}</strong>
                                    <span style="font-size:.75rem; color:#64748b;">Matrícula: ${a.matricula}</span>
                                </div>
                                <div>
                                    ${presente 
                                        ? '<span class="badge badge-green" style="padding:.4rem .8rem; font-size:.8rem;">✓ PRESENTE</span>' 
                                        : '<span class="badge badge-gray" style="padding:.4rem .8rem; font-size:.8rem; opacity:.5;">FALTANTE</span>'}
                                </div>
                            </div>
                        </td>`;
        tbody.appendChild(tr);
    });
}

// --- NAVEGAÇÃO ---
function voltarEtapa1() {
    if (!confirm('Isso apagará os scans atuais. Continuar?')) return;
    limparEstado();
    if (scannerAluno) scannerAluno.stop().catch(()=>{});
    scannerAtivo = false;
    turmaId = ''; presentes = [];
    mostrar(1);
    setTimeout(() => startScannerTurma(), 300);
}

function finalizarChamada() {
    if (scannerAluno) scannerAluno.stop().catch(()=>{});
    scannerAtivo = false;
    const data = document.getElementById('data').value;
    document.getElementById('resumoPresentes').textContent = presentes.length;
    document.getElementById('resumoFaltas').textContent    = alunos.length - presentes.length;
    document.getElementById('resumoTurma').textContent     = turmaNome;
    document.getElementById('resumoData').textContent      = data;
    document.getElementById('inputTurmaId').value = turmaId;
    document.getElementById('inputData').value    = data;
    const cont = document.getElementById('inputsPresentes');
    cont.innerHTML = '';
    presentes.forEach(id => {
        const inp = document.createElement('input');
        inp.type='hidden'; inp.name='presentes[]'; inp.value=id;
        cont.appendChild(inp);
    });
    salvarEstado();
    mostrar(3);
}

function voltarParaScanner() {
    mostrar(2);
    setTimeout(() => startScannerAluno(), 300);
}

// --- PERSISTÊNCIA localStorage ---
function salvarEstado() {
    localStorage.setItem('freq_estado', JSON.stringify({
        turmaId, turmaNome, data: document.getElementById('data')?.value, presentes
    }));
}
function recuperarEstado() {
    const s = localStorage.getItem('freq_estado');
    if (!s) return false;
    try {
        const e = JSON.parse(s);
        if (e.data !== document.getElementById('data').value) return false;
        turmaId = e.turmaId; turmaNome = e.turmaNome; presentes = e.presentes || [];
        if (turmaId) {
            alunos = alunosPorTurma[turmaId] || [];
            return true;
        }
    } catch(e) {}
    return false;
}
function limparEstado() { localStorage.removeItem('freq_estado'); }

// --- ÁUDIO ---
function playBeep() {
    try {
        const ctx = new (window.AudioContext || window.webkitAudioContext)();
        const osc = ctx.createOscillator();
        const gain = ctx.createGain();
        osc.connect(gain); gain.connect(ctx.destination);
        osc.frequency.value = 1200; gain.gain.value = 1.0;
        osc.start(); osc.stop(ctx.currentTime + 0.15);
    } catch(e){}
}

// --- FORM SUBMIT ---
document.getElementById('formFinal').addEventListener('submit', () => limparEstado());

// --- INIT ---
document.addEventListener('DOMContentLoaded', () => {
    if (recuperarEstado() && turmaId) {
        mostrar(2);
        atualizarContadores();
        renderLista();
        document.getElementById('turmaNomeTitulo').textContent = turmaNome;
        document.getElementById('dataTitulo').textContent = document.getElementById('data').value;
        setTimeout(() => startScannerAluno(), 800);
    } else {
        mostrar(1);
        setTimeout(() => startScannerTurma(), 300);
    }
});
</script>

<style>
#qr-reader-turma, #qr-reader-aluno { background:#000; border-radius:12px; }
video { object-fit:cover; }
.row-presente td { background: #ecfdf5!important; border-left: 5px solid var(--success)!important; }
@media (max-width:768px) {
    .cards { grid-template-columns: 1fr 1fr!important; gap: .75rem; }
    #ctTotal { grid-column: span 2; }
    .card-value { font-size:1.75rem; }
    th { display:none; }
    td { border-bottom: 1px solid #f1f5f9; }
}
</style>

<?php include __DIR__ . '/../layout/footer.php'; ?>
