<?php
// pages/justificativa_form.php — Formulário para registrar justificativa
requer_login();
requer_role('DIRETOR', 'VICE', 'ORIENTADORA');

$frequenciaId = $_GET['frequencia_id'] ?? null;
$alunoId      = $_GET['aluno_id'] ?? null;

$frequenciaPreSelecionada = null;
$alunoSelecionado = null;
$faltas = [];

if ($frequenciaId) {
    // Caso venha direto da tela de frequência
    $frequenciaPreSelecionada = db_one(
        "SELECT f.id, f.data, f.aluno_id, a.nome AS aluno_nome, t.nome AS turma_nome
         FROM frequencias f
         JOIN alunos a ON a.id = f.aluno_id
         LEFT JOIN turmas t ON t.id = a.turma_id
         WHERE f.id = ? AND a.escola_id = ? AND f.status = 'FALTA'",
        [(int)$frequenciaId, escola_id()]
    );
    if ($frequenciaPreSelecionada) {
        $alunoId = $frequenciaPreSelecionada->aluno_id;
        $alunoSelecionado = (object)[
            'id' => $frequenciaPreSelecionada->aluno_id,
            'nome' => $frequenciaPreSelecionada->aluno_nome,
            'turma_nome' => $frequenciaPreSelecionada->turma_nome
        ];
        $faltas = [$frequenciaPreSelecionada];
    }
} elseif ($alunoId) {
    // Caso tenha selecionado o aluno no dropdown
    $alunoSelecionado = db_one(
        "SELECT a.id, a.nome, t.nome AS turma_nome
         FROM alunos a
         LEFT JOIN turmas t ON t.id = a.turma_id
         WHERE a.id = ? AND a.escola_id = ? AND a.ativo = 1",
        [(int)$alunoId, escola_id()]
    );
    if ($alunoSelecionado) {
        $faltas = db_all(
            "SELECT f.id, f.data 
             FROM frequencias f
             WHERE f.aluno_id = ? AND f.status = 'FALTA'
             ORDER BY f.data DESC",
            [$alunoId]
        );
    }
}

// Lista de alunos para o select principal se não houver frequência pré-selecionada
$alunos = [];
if (!$frequenciaId) {
    $alunos = db_all(
        "SELECT a.id, a.nome, t.nome AS turma_nome 
         FROM alunos a 
         LEFT JOIN turmas t ON t.id = a.turma_id
         WHERE a.escola_id = ? AND a.ativo = 1 
         ORDER BY a.nome",
        [escola_id()]
    );
}

$tituloPagina = 'Registrar Justificativa';
include __DIR__ . '/../layout/header.php';
?>

<div style="max-width: 600px; margin: 0 auto">
    <div class="table-wrap">
        <div class="table-head">
            <h3>📝 Registrar Justificativa de Falta</h3>
            <a href="/justificativas" class="btn btn-outline" style="font-size: .8rem">Voltar</a>
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

            <!-- Etapa 1: Seleção do Aluno (se não pré-selecionado) -->
            <?php if (!$frequenciaId): ?>
                <div class="form-group">
                    <label>Selecionar Aluno</label>
                    <select class="form-control" onchange="window.location.href = '/justificativas/criar?aluno_id=' + this.value">
                        <option value="">-- Escolha um Aluno --</option>
                        <?php foreach ($alunos as $al): ?>
                            <option value="<?= $al->id ?>" <?= $alunoId == $al->id ? 'selected' : '' ?>>
                                <?= e($al->nome) ?> (<?= e($al->turma_nome ?? 'Sem Turma') ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php else: ?>
                <div class="form-group">
                    <label>Aluno Selecionado</label>
                    <input type="text" class="form-control" value="<?= e($alunoSelecionado->nome) ?> (<?= e($alunoSelecionado->turma_nome) ?>)" readonly style="background:#f1f5f9">
                </div>
            <?php endif; ?>

            <!-- Etapa 2: Formulário de Justificativa -->
            <?php if ($alunoSelecionado): ?>
                <form method="POST" action="/justificativas">
                    <?php csrf_field(); ?>
                    
                    <div class="form-group">
                        <label>Falta a Justificar</label>
                        <?php if (empty($faltas)): ?>
                            <div style="padding: .75rem; background: #fee2e2; color: #991b1b; border-radius: 8px; font-size: .85rem; font-weight: 500">
                                ⚠️ Este aluno não possui nenhuma falta pendente (não justificada) registrada no sistema.
                            </div>
                        <?php else: ?>
                            <select name="frequencia_id" class="form-control" required <?= $frequenciaId ? 'readonly' : '' ?>>
                                <?php foreach ($faltas as $f): ?>
                                    <option value="<?= $f->id ?>" selected>Falta no dia: <?= fmt_data($f->data) ?></option>
                                <?php endforeach; ?>
                            </select>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($faltas)): ?>
                        <div class="form-group">
                            <label>Nome do Responsável que Compareceu</label>
                            <input type="text" name="responsavel_nome" class="form-control" required placeholder="Nome completo do pai, mãe, etc.">
                        </div>

                        <div class="form-group">
                            <label>Parentesco</label>
                            <select name="parentesco" class="form-control" required>
                                <option value="">-- Selecione --</option>
                                <option value="MAE">Mãe</option>
                                <option value="PAI">Pai</option>
                                <option value="AVO">Avô</option>
                                <option value="AVIA">Avó</option>
                                <option value="TIO">Tio</option>
                                <option value="TIA">Tia</option>
                                <option value="OUTRO">Outro</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Data do Comparecimento à Escola</label>
                            <input type="date" name="data_visita" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>

                        <div class="form-group">
                            <label>Motivo da Ausência (Justificativa)</label>
                            <textarea name="motivo" class="form-control" rows="3" required placeholder="Descreva brevemente o motivo pelo qual o aluno faltou (ex: doente medicado em casa, problemas de locomoção, viagem familiar, etc...)"></textarea>
                        </div>

                        <div class="form-group">
                            <label>Observações Adicionais (Opcional)</label>
                            <textarea name="observacoes" class="form-control" rows="2" placeholder="Outros detalhes relevantes..."></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center">
                            💾 Registrar Justificativa e Imprimir Termo
                        </button>
                    <?php endif; ?>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
