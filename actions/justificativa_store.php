<?php
// actions/justificativa_store.php — Registrar justificativa de falta
requer_login();
requer_role('DIRETOR', 'VICE', 'ORIENTADORA');
verificar_csrf();

$frequenciaId    = (int)($_POST['frequencia_id']    ?? 0);
$responsavelNome = trim($_POST['responsavel_nome']  ?? '');
$parentesco      = trim($_POST['parentesco']         ?? '');
$dataVisita      = trim($_POST['data_visita']        ?? '');
$motivo          = trim($_POST['motivo']             ?? '');
$observacoes     = trim($_POST['observacoes']        ?? '');

$erros = [];

if (!$frequenciaId)           $erros[] = 'Falta não identificada.';
if (empty($responsavelNome))  $erros[] = 'O nome do responsável é obrigatório.';
if (empty($parentesco))       $erros[] = 'O parentesco é obrigatório.';
if (empty($dataVisita))       $erros[] = 'A data da visita é obrigatória.';
if (empty($motivo))           $erros[] = 'O motivo é obrigatório.';

$parentescosValidos = ['PAI','MAE','AVO','AVIA','TIO','TIA','OUTRO'];
if (!in_array($parentesco, $parentescosValidos)) $erros[] = 'Parentesco inválido.';

// Verifica se a frequência existe, pertence à escola e é uma FALTA
$frequencia = null;
if ($frequenciaId) {
    $frequencia = db_one(
        "SELECT f.id, f.aluno_id, f.status FROM frequencias f
         JOIN alunos a ON a.id = f.aluno_id
         WHERE f.id = ? AND a.escola_id = ? AND f.status = 'FALTA'",
        [$frequenciaId, escola_id()]
    );
    if (!$frequencia) $erros[] = 'Falta não encontrada ou já justificada.';
}

// Verifica se já existe justificativa para essa frequência
if ($frequencia) {
    $jaExiste = db_one(
        "SELECT id FROM justificativas_faltas WHERE frequencia_id = ?",
        [$frequenciaId]
    );
    if ($jaExiste) $erros[] = 'Esta falta já possui uma justificativa registrada.';
}

if (!empty($erros)) {
    $_SESSION['erros'] = $erros;
    redirect('/justificativas/criar?frequencia_id=' . $frequenciaId);
}

// Insere a justificativa
$justId = db_insert(
    "INSERT INTO justificativas_faltas
        (frequencia_id, aluno_id, escola_id, responsavel_nome, parentesco, data_visita, motivo, observacoes, registrado_por, created_at)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())",
    [
        $frequenciaId,
        $frequencia->aluno_id,
        escola_id(),
        $responsavelNome,
        $parentesco,
        $dataVisita,
        $motivo,
        $observacoes ?: null,
        $_SESSION['usuario']['id'] ?? null
    ]
);

// Atualiza status da falta para FALTA_JUSTIFICADA
db_run(
    "UPDATE frequencias SET status = 'FALTA_JUSTIFICADA', updated_at = NOW() WHERE id = ?",
    [$frequenciaId]
);

flash('success', 'Justificativa registrada com sucesso!');
redirect('/justificativas/' . $justId . '/imprimir');
