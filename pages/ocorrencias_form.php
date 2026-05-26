<?php
// pages/ocorrencias_form.php — Formulário inteligente de cadastro de ocorrências
requer_login();
requer_role('DIRETOR', 'VICE', 'ORIENTADORA');

$alunoId = $_GET['aluno_id'] ?? null;

$alunos = db_all(
    "SELECT a.id, a.nome, t.nome AS turma_nome 
     FROM alunos a 
     LEFT JOIN turmas t ON t.id = a.turma_id
     WHERE a.escola_id = ? AND a.ativo = 1 
     ORDER BY a.nome",
    [escola_id()]
);

$tituloPagina = 'Registrar Nova Ocorrência';
include __DIR__ . '/../layout/header.php';
?>

<div style="max-width: 700px; margin: 0 auto">
    <div class="table-wrap">
        <div class="table-head">
            <h3>⚠️ Registrar Nova Ocorrência Disciplinar</h3>
            <a href="/ocorrencias" class="btn btn-outline" style="font-size: .8rem">Voltar</a>
        </div>
        
        <div style="padding: 1.5rem">
            <?php if ($erros = $_SESSION['erros'] ?? null): unset($_SESSION['erros']); ?>
                <div class="alert alert-error" style="margin-bottom: 1.5rem">
                    <ul style="padding-left: 1rem">
                        <?php foreach ($erros as $erro): ?>
                            <li><?= e($erro) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" action="/ocorrencias" id="form-ocorrencia">
                <?php csrf_field(); ?>

                <!-- Seleção do Aluno -->
                <div class="form-group">
                    <label>Aluno</label>
                    <select name="aluno_id" id="aluno_id" class="form-control" required onchange="atualizarTemplate()">
                        <option value="">-- Selecione o Aluno --</option>
                        <?php foreach ($alunos as $al): ?>
                            <option value="<?= $al->id ?>" data-nome="<?= e($al->nome) ?>" data-turma="<?= e($al->turma_nome ?? 'Sem Turma') ?>" <?= $alunoId == $al->id ? 'selected' : '' ?>>
                                <?= e($al->nome) ?> (<?= e($al->turma_nome ?? 'Sem Turma') ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Data da Ocorrência -->
                <div class="form-group">
                    <label>Data do Ocorrido</label>
                    <input type="date" name="data_ocorrencia" id="data_ocorrencia" class="form-control" value="<?= date('Y-m-d') ?>" required onchange="atualizarTemplate()">
                </div>

                <!-- Classificação / Tipo -->
                <div class="form-group">
                    <label>Tipo de Ocorrência</label>
                    <select name="tipo" id="tipo" class="form-control" required onchange="atualizarTemplate()">
                        <option value="">-- Selecione a Ocorrência --</option>
                        <option value="INDISCIPLINA_PROFESSOR">Indisciplina com Professor</option>
                        <option value="RECUSA_ATIVIDADE">Recusa em Realizar Atividades</option>
                        <option value="BRIGA">Briga / Conflito Físico ou Verbal</option>
                        <option value="FURTO">Furto / Subtração de Pertences</option>
                        <option value="OUTRO">Outra Situação (Em branco)</option>
                    </select>
                </div>

                <!-- Descrição Detalhada / Relato (Preenchido por template + ajustável) -->
                <div class="form-group">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:.5rem;">
                        <label style="margin:0">Relato dos Fatos (Totalmente Editável)</label>
                        <span id="badge-template" class="badge badge-blue" style="display:none; font-size:.65rem;">Template Aplicado</span>
                    </div>
                    <textarea name="descricao" id="descricao" class="form-control" rows="6" required placeholder="Selecione um aluno e o tipo de ocorrência para carregar o modelo inicial, ou escreva livremente aqui..."></textarea>
                </div>

                <!-- Medida Adotada / Providências -->
                <div class="form-group">
                    <label>Medida Adotada / Providências da Escola</label>
                    <select name="medida_tomada" id="medida_tomada" class="form-control" onchange="toggleMedidaLivre(this.value)">
                        <option value="Advertência Verbal ao aluno">Advertência Verbal ao aluno</option>
                        <option value="Termo de Advertência Escrita assinado pelos pais">Termo de Advertência Escrita assinado pelos pais</option>
                        <option value="Convocação dos responsáveis para reunião pedagógica">Convocação dos responsáveis para reunião pedagógica</option>
                        <option value="Suspensão preventiva das atividades letivas por 1 dia">Suspensão preventiva das atividades letivas por 1 dia</option>
                        <option value="Suspensão preventiva das atividades letivas por 2 dias">Suspensão preventiva das atividades letivas por 2 dias</option>
                        <option value="Encaminhamento para atendimento especializado / Orientação">Encaminhamento para atendimento especializado / Orientação</option>
                        <option value="OUTRO">Outra providência (Digitar texto livre)</option>
                    </select>
                </div>

                <!-- Campo de Medida Livre (Exibido apenas se escolher "OUTRO") -->
                <div class="form-group" id="grupo-medida-livre" style="display:none">
                    <label>Especifique a medida adotada</label>
                    <input type="text" id="medida_livre" class="form-control" placeholder="Descreva a medida ou punição disciplinar aplicada...">
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center">
                    💾 Registrar Ocorrência e Imprimir Relatório
                </button>
            </form>
        </div>
    </div>
</div>

<script>
const templates = {
    'INDISCIPLINA_PROFESSOR': 'No dia {data}, o(a) aluno(a) {nome}, da turma {turma}, apresentou comportamento inadequado em sala de aula, agindo com indisciplina perante o(a) professor(a) ______________. O discente demonstrou desrespeito direto, utilizando vocabulário inapropriado, desobedecendo às regras básicas estipuladas e perturbando o andamento pedagógico da atividade letiva, afetando o aprendizado próprio e de seus colegas de classe.',
    
    'RECUSA_ATIVIDADE': 'No dia {data}, o(a) aluno(a) {nome}, da turma {turma}, demonstrou recusa reiterada em realizar e cooperar com as atividades e tarefas escolares planejadas e ministradas em sala de aula pelo docente regente. Mesmo recebendo acompanhamento especial, instruções personalizadas e advertência quanto à importância pedagógica dos exercícios, o discente permaneceu apático/inativo em suas funções acadêmicas.',
    
    'BRIGA': 'No dia {data}, o(a) aluno(a) {nome}, da turma {turma}, envolveu-se diretamente em uma situação de conflito/desentendimento físico e/ou de agressão verbal contra outro estudante nas dependências da instituição escolar (especificamente em/no ______________). Diante do ocorrido, foi necessária a intervenção imediata da equipe de apoio e coordenação para conter os ânimos, afastar os envolvidos e assegurar a integridade física de todos.',
    
    'FURTO': 'No dia {data}, o(a) aluno(a) {nome}, da turma {turma}, esteve envolvido(a) em um incidente de subtração não autorizada de pertences (especificamente ______________) pertencentes a outrem (aluno/servidor), ocorrido dentro das dependências desta escola. O ocorrido foi devidamente testemunhado e necessitou de ação imediata da direção escolar para apuração dos fatos.',
    
    'OUTRO': ''
};

function formatarData(dataStr) {
    if (!dataStr) return '__/__/____';
    const partes = dataStr.split('-');
    if (partes.length !== 3) return '__/__/____';
    return `${partes[2]}/${partes[1]}/${partes[0]}`;
}

function atualizarTemplate() {
    const selectAluno = document.getElementById('aluno_id');
    const selectTipo = document.getElementById('tipo');
    const inputData = document.getElementById('data_ocorrencia');
    const txtDescricao = document.getElementById('descricao');
    const badge = document.getElementById('badge-template');

    const tipoVal = selectTipo.value;
    if (!tipoVal) return;

    const optionSel = selectAluno.options[selectAluno.selectedIndex];
    const nome = optionSel.dataset.nome || '{Nome do Aluno}';
    const turma = optionSel.dataset.turma || '{Turma}';
    const dataFormatada = formatarData(inputData.value);

    let texto = templates[tipoVal];
    
    // Substituir marcadores
    texto = texto.replace(/{nome}/g, nome)
                 .replace(/{turma}/g, turma)
                 .replace(/{data}/g, dataFormatada);

    // Só preenche se o usuário ainda não escreveu nada, ou se for mudar de template ativamente
    if (tipoVal === 'OUTRO') {
        badge.style.display = 'none';
    } else {
        txtDescricao.value = texto;
        badge.style.display = 'inline-flex';
    }
}

function toggleMedidaLivre(val) {
    const grupoLivre = document.getElementById('grupo-medida-livre');
    const inputLivre = document.getElementById('medida_livre');
    if (val === 'OUTRO') {
        grupoLivre.style.display = 'block';
        inputLivre.setAttribute('name', 'medida_tomada');
        inputLivre.setAttribute('required', 'required');
        document.getElementById('medida_tomada').removeAttribute('name');
    } else {
        grupoLivre.style.display = 'none';
        inputLivre.removeAttribute('name');
        inputLivre.removeAttribute('required');
        document.getElementById('medida_tomada').setAttribute('name', 'medida_tomada');
    }
}

// Inicializar na carga se o aluno já estiver selecionado (via GET)
window.onload = function() {
    if (document.getElementById('aluno_id').value) {
        atualizarTemplate();
    }
};
</script>
<?php include __DIR__ . '/../layout/footer.php'; ?>
