@extends('layouts.app')
@section('titulo', 'Lançar Frequência')

@section('content')
<div id="app" x-data="frequenciaApp()" x-cloak>

    {{-- ETAPA 1: Escanear Turma --}}
    <div x-show="etapa === 1" class="table-wrap" style="max-width:500px;margin:0 auto;">
        <div class="table-head"><h3>🏫 Identificar Turma</h3></div>
        <div style="padding:1.5rem;">
            
            <p style="text-align:center;margin-bottom:1.5rem;color:#475569;">
                Escaneie o QR Code da turma (na porta ou mesa) para iniciar a chamada.
            </p>

            <div id="qr-reader-turma" style="width:100%;margin:0 auto;border-radius:8px;overflow:hidden;"></div>

            <div x-show="scanErro" style="margin-top:.75rem;text-align:center;">
                <div class="alert alert-error" style="margin-bottom:0" x-text="scanErro"></div>
            </div>

            <div class="form-group" style="margin-top:1.5rem;">
                <label>Data da Chamada</label>
                <input type="date" class="form-control" x-model="data">
            </div>

            {{-- Fallback manual caso câmera falhe (opcional, pode ser removido se quiser forçar 100%) --}}
            <div style="margin-top:2rem;text-align:center;">
                 <button class="btn btn-outline" @click="ativarSelecaoManual = !ativarSelecaoManual" style="font-size:.75rem">Problemas com a câmera?</button>
            </div>

            <div x-show="ativarSelecaoManual" style="margin-top:1rem;border-top:1px dashed #cbd5e1;padding-top:1rem;">
                <label style="display:block;font-size:.8rem;margin-bottom:.5rem;">Seleção Manual de Emergência</label>
                <select class="form-control" x-model="turmaId" @change="iniciarChamada()">
                    <option value="">-- Escolha a Turma --</option>
                    @foreach($turmas as $turma)
                    <option value="{{ $turma->id }}">{{ $turma->nome }} – {{ $turma->turno }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    {{-- ETAPA 2: Scanner Alunos + lista --}}
    <div x-show="etapa === 2">
        {{-- Header com info --}}
        <div class="cards" style="grid-template-columns:1fr 1fr 1fr;">
            <div class="card green">
                <div class="card-label">Presentes</div>
                <div class="card-value" x-text="presentes.length" style="color:var(--success)"></div>
            </div>
            <div class="card red">
                <div class="card-label">Faltantes</div>
                <div class="card-value" x-text="totalAlunos - presentes.length" style="color:var(--danger)"></div>
            </div>
            <div class="card">
                <div class="card-label">Total</div>
                <div class="card-value" x-text="totalAlunos"></div>
            </div>
        </div>

        {{-- Scanner de QR Alunos --}}
        <div class="table-wrap" style="margin-bottom:1rem;">
            <div class="table-head">
                <h3>📷 Escanear Alunos</h3>
                <div>
                    <button class="btn btn-outline" @click="toggleScannerAluno()" x-text="scannerAtivo ? '⏸️ Pausar' : '▶️ Scanear'" style="font-size:.75rem"></button>
                </div>
            </div>
            <div style="padding:1rem;">
                <div id="qr-reader-aluno" style="width:100%;max-width:400px;margin:0 auto;"></div>
                <div x-show="ultimoScan" style="margin-top:.75rem;text-align:center;">
                    <div class="alert alert-success" style="margin-bottom:0" x-text="ultimoScan"></div>
                </div>
                <div x-show="scanErro" style="margin-top:.75rem;text-align:center;">
                    <div class="alert alert-error" style="margin-bottom:0" x-text="scanErro"></div>
                </div>
            </div>
        </div>

        {{-- Lista de alunos --}}
        <div class="table-wrap">
            <div class="table-head">
                <h3>👥 Turma: <span x-text="turmaNome" style="color:var(--primary)"></span></h3>
                <span class="badge badge-blue" x-text="data"></span>
            </div>
            <table>
                <thead>
                    <tr><th>Nome</th><th>Status</th></tr>
                </thead>
                <tbody>
                    <template x-for="aluno in alunos" :key="aluno.id">
                        <tr :style="presentes.includes(aluno.id) ? 'background:#d1fae5' : ''">
                            <td>
                                <strong x-text="aluno.nome"></strong><br>
                                <span style="font-size:.75rem;color:#64748b" x-text="aluno.matricula"></span>
                            </td>
                            <td>
                                <span x-show="presentes.includes(aluno.id)" class="badge badge-green">✅ Presente</span>
                                <span x-show="!presentes.includes(aluno.id)" class="badge badge-gray">⏳</span>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        {{-- Botões de ação --}}
        <div style="display:flex;gap:1rem;margin-top:1rem;flex-wrap:wrap;">
            <button class="btn btn-primary" style="flex:1;justify-content:center;padding:.75rem;"
                    @click="finalizarChamada()">
                ✅ Finalizar Chamada
            </button>
            <button class="btn btn-outline" style="justify-content:center;padding:.75rem;"
                    @click="voltarEtapa1()">
                ← Trocar Turma
            </button>
        </div>
    </div>

    {{-- ETAPA 3: Confirmação --}}
    <div x-show="etapa === 3" class="table-wrap" style="max-width:500px;margin:0 auto;">
        <div class="table-head"><h3>📊 Resumo da Chamada</h3></div>
        <div style="padding:1.5rem;text-align:center;">
            <div class="cards" style="grid-template-columns:1fr 1fr;margin-bottom:1.5rem;">
                <div class="card green">
                    <div class="card-label">Presentes</div>
                    <div class="card-value" x-text="presentes.length" style="color:var(--success)"></div>
                </div>
                <div class="card red">
                    <div class="card-label">Faltas</div>
                    <div class="card-value" x-text="totalAlunos - presentes.length" style="color:var(--danger)"></div>
                </div>
            </div>

            <p style="margin-bottom:1rem;font-size:.9rem;color:#475569;">
                Confirmar e salvar a frequência de <strong x-text="turmaNome"></strong> para <strong x-text="data"></strong>?
            </p>

            <form method="POST" action="/frequencia/registrar" @submit="submitFinal()">
                @csrf
                <input type="hidden" name="turma_id" :value="turmaId">
                <input type="hidden" name="data" :value="data">
                <template x-for="id in presentes" :key="id">
                    <input type="hidden" name="presentes[]" :value="id">
                </template>
                <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;padding:.75rem;">
                    💾 Confirmar e Salvar
                </button>
            </form>

            <button class="btn btn-outline" style="width:100%;justify-content:center;padding:.75rem;margin-top:.5rem;"
                    @click="retornarParaScannerAluno()">
                ← Voltar ao Scanner
            </button>
        </div>
    </div>
</div>

{{-- Biblioteca html5-qrcode --}}
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

<script>
    // Dados dos alunos passados do servidor
    const alunosPorTurma = @json($alunosPorTurma);
    // Dados das turmas (para validação do token da turma)
    const turmas = @json($turmas);

    function frequenciaApp() {
        return {
            etapa: 1,
            turmaId: '',
            turmaNome: '',
            data: '{{ now()->toDateString() }}',
            alunos: [],
            totalAlunos: 0,
            presentes: [],
            
            scannerAtivo: false,
            scannerTurma: null,
            scannerAluno: null,
            
            ultimoScan: '',
            scanErro: '',
            ativarSelecaoManual: false,

            init() {
                // Tentar recuperar estado anterior
                if (!this.recuperarEstado()) {
                    // Se não recuperou nada, inicia scanner de turma normalmente
                     this.$nextTick(() => this.startScannerTurma());
                }
            },

            // --- PERSISTÊNCIA ---
            salvarEstado() {
                const estado = {
                    turmaId: this.turmaId,
                    turmaNome: this.turmaNome,
                    layout_data: this.data,
                    presentes: this.presentes,
                    etapa: this.etapa
                };
                localStorage.setItem('frequencia_estado', JSON.stringify(estado));
            },

            recuperarEstado() {
                const salvo = localStorage.getItem('frequencia_estado');
                if (salvo) {
                    try {
                        const estado = JSON.parse(salvo);
                        // Só recupera se for do mesmo dia
                        if (estado.layout_data === this.data) {
                            this.turmaId = estado.turmaId;
                            this.turmaNome = estado.turmaNome;
                            this.presentes = estado.presentes || [];
                            this.etapa = estado.etapa;

                            // Se recuperou e estava na etapa 2 ou 3, carrega os dados alunos
                            if (this.turmaId && this.etapa > 1) {
                                this.alunos = alunosPorTurma[this.turmaId] || [];
                                this.totalAlunos = this.alunos.length;
                                
                                // Se estava escaneando (etapa 2), retoma o scanner automaticamente
                                if (this.etapa === 2) {
                                    this.$nextTick(() => {
                                        setTimeout(() => this.startScannerAluno(), 800);
                                    });
                                }
                            }
                            return true;
                        }
                    } catch(e) {
                        console.error('Erro ao recuperar estado:', e);
                    }
                }
                return false;
            },

            limparEstado() {
                localStorage.removeItem('frequencia_estado');
            },

            // --- SCANNER DE TURMA (Etapa 1) ---
            startScannerTurma() {
                 if (this.scannerAluno) this.scannerAluno.clear(); // Garantir que o outro scanner tá limpo

                this.etapa = 1;
                this.scanErro = '';
                
                this.$nextTick(() => {
                    this.scannerTurma = new Html5Qrcode("qr-reader-turma");
                    this.scannerTurma.start(
                        { facingMode: "environment" },
                        { fps: 10, qrbox: { width: 250, height: 250 } },
                        (decodedText) => this.onScanTurma(decodedText),
                        () => {}
                    ).catch(err => {
                        this.scanErro = 'Erro na câmera. Verifique permissões.';
                    });
                });
            },

            onScanTurma(text) {
                try {
                    // Esperado: JSON { turma_id: 1, qr_token: '...', ... }
                    const payload = JSON.parse(text);
                    
                    if (!payload.turma_id || !payload.qr_token) throw new Error('Formato inválido');

                    // Validar token da turma
                    const turmaUnica = turmas.find(t => t.id == payload.turma_id);
                    
                    if (!turmaUnica) {
                        this.showError('Turma não encontrada!');
                        return;
                    }
                    if (turmaUnica.qr_token !== payload.qr_token) {
                        this.showError('QR Code da turma inválido/antigo!');
                        return;
                    }

                    // Sucesso!
                    this.turmaId = payload.turma_id;
                    this.playBeep();
                    
                    // Parar scanner de turma e ir para chamada
                    this.scannerTurma.stop().then(() => {
                        this.iniciarChamada();
                    });

                } catch (e) {
                    this.showError('QR Code ilegível ou não é de turma.');
                }
            },

            iniciarChamada() {
                if (!this.turmaId) return;

                // Pegar nome da turma selecionada
                const turmaObj = turmas.find(t => t.id == this.turmaId);
                this.turmaNome = turmaObj ? (turmaObj.nome + ' – ' + turmaObj.turno) : 'Turma Desconhecida';

                // Carregar alunos
                this.alunos = alunosPorTurma[this.turmaId] || [];
                this.totalAlunos = this.alunos.length;
                this.presentes = [];
                this.ultimoScan = '';
                this.scanErro = '';
                
                // Mudar para Etapa 2
                this.etapa = 2;
                this.salvarEstado(); // Salva que iniciou a chamada nesta turma

                // Parar scanner anterior se ainda estiver rodando (segurança)
                if(this.scannerTurma) {
                    this.scannerTurma.clear().catch(e => console.log('Limpeza scanner turma:', e));
                }

                // Iniciar scanner de alunos com delay maior para garantir que a DIV existe
                this.$nextTick(() => {
                    setTimeout(() => this.startScannerAluno(), 800);
                });

                // Tentar pré-aquecer vibração (hack para alguns navegadores)
                if (navigator.vibrate) navigator.vibrate(1);
            },

            // --- SCANNER DE ALUNOS (Etapa 2) ---
            startScannerAluno() {
                console.log('Iniciando Scanner Aluno...');
                // Limpar instância anterior se existir
                if (this.scannerAluno) {
                    try { this.scannerAluno.clear(); } catch(e){}
                }
                
                // Garantir que o elemento existe
                const el = document.getElementById("qr-reader-aluno");
                if(!el) {
                    console.error('Elemento do scanner não encontrado!');
                    this.scanErro = 'Erro: Câmera não inicializou (elemento não encontrado). Tente recarregar.';
                    return;
                }

                try {
                    this.scannerAluno = new Html5Qrcode("qr-reader-aluno");
                    this.scannerAtivo = true;

                    this.scannerAluno.start(
                        { facingMode: "environment" },
                        { fps: 10, qrbox: { width: 250, height: 250 } },
                        (decodedText) => this.onScanAluno(decodedText),
                        (errorMessage) => { 
                            // Ignora erros de frame vazio, mas loga outros críticos
                            // console.log(errorMessage); 
                        }
                    ).then(() => {
                        console.log('Scanner Aluno iniciado com sucesso!');
                    }).catch(err => {
                        console.error('Erro fatal ao iniciar scanner aluno:', err);
                        this.scanErro = 'Falha ao abrir câmera: ' + err;
                        this.scannerAtivo = false;
                    });
                } catch(e) {
                    console.error('Exceção no startScannerAluno:', e);
                }
            },

            onScanAluno(text) {
                this.scanErro = '';
                try {
                    const payload = JSON.parse(text);
                    const alunoId = payload.aluno_id;
                    const qrToken = payload.qr_token;

                    // 1. Aluno pertence à turma?
                    const aluno = this.alunos.find(a => a.id === alunoId);
                    if (!aluno) {
                        this.showError('⚠️ Aluno não pertence a esta turma!');
                        return;
                    }

                    // 2. Token válido?
                    if (aluno.qr_token !== qrToken) {
                        this.showError('⚠️ QR Code inválido!');
                        return;
                    }

                    // 3. Já registrado?
                    if (this.presentes.includes(alunoId)) {
                        this.ultimoScan = `ℹ️ ${aluno.nome} já foi lido.`;
                        setTimeout(() => this.ultimoScan = '', 2000);
                        return;
                    }

                    // SUCESSO
                    this.presentes.push(alunoId);
                    this.salvarEstado(); // Salva o novo aluno presente

                    this.ultimoScan = `✅ ${aluno.nome}`;
                    // Tentar múltiplos padrões de vibração
                    try {
                        if (navigator.vibrate) {
                            navigator.vibrate(200); // Padrão simples
                            navigator.vibrate([200]); // Array (alguns devices exigem)
                        }
                    } catch(e){}
                    
                    this.playBeep();

                    setTimeout(() => this.ultimoScan = '', 3000);

                } catch (e) {
                    // Ignora QR que não seja JSON (pode ser qualquer coisa) ou erro de parse
                }
            },

            toggleScannerAluno() {
                if (this.scannerAtivo) {
                    this.scannerAluno.stop().then(() => this.scannerAtivo = false);
                } else {
                    this.startScannerAluno();
                }
            },

            // --- NAVEGAÇÃO ---
            voltarEtapa1() {
                if (confirm('Tem certeza? Isso apagará os scans atuais.')) {
                    this.limparEstado(); // Limpa se cancelar/voltar
                    if (this.scannerAluno) this.scannerAluno.stop().catch(()=>{});
                    this.scannerAtivo = false;
                    this.turmaId = '';
                    this.presentes = [];
                    this.startScannerTurma(); 
                }
            },

            finalizarChamada() {
                if (this.scannerAluno) this.scannerAluno.stop().catch(()=>{});
                this.scannerAtivo = false;
                this.etapa = 3;
                this.salvarEstado(); // Salva que está na etapa de resumo
            },

            submitFinal() {
                 this.limparEstado(); // Limpa ao submeter o form final com sucesso
                 return true; // deixa o form seguir
            },

            retornarParaScannerAluno() {
                this.etapa = 2;
                this.$nextTick(() => this.startScannerAluno());
            },

            // UTILS
            showError(msg) {
                this.scanErro = msg;
                if (navigator.vibrate) navigator.vibrate([100,50,100]);
                setTimeout(() => this.scanErro = '', 3000);
            },

            playBeep() {
                try {
                    const ctx = new (window.AudioContext || window.webkitAudioContext)();
                    const osc = ctx.createOscillator();
                    const gain = ctx.createGain();
                    
                    osc.connect(gain);
                    gain.connect(ctx.destination);
                    
                    // Aumentar frequência e volume
                    osc.frequency.value = 1200; // Mais agudo (chama mais atenção)
                    gain.gain.value = 1.0;      // Volume máximo (era 0.1)
                    
                    osc.start();
                    osc.stop(ctx.currentTime + 0.15); // Um pouco mais longo
                } catch(e) {}
            }
        };
    }
</script>

<style>
    [x-cloak] { display: none !important; }
    #qr-reader-turma, #qr-reader-aluno { background:#000; }
    video { object-fit: cover; }
    
    @media (max-width: 768px) {
        .cards { grid-template-columns: 1fr 1fr 1fr !important; }
        .card-value { font-size: 1.5rem; }
    }
</style>
@endsection
