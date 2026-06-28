<?php
// actions/ocorrencias_store.php — Registrar uma nova ocorrência disciplinar
requer_login();
requer_role('DIRETOR', 'VICE', 'ORIENTADORA');
verificar_csrf();

$alunoId        = (int)($_POST['aluno_id']        ?? 0);
$tipo           = trim($_POST['tipo']             ?? '');
$dataOcorrencia = trim($_POST['data_ocorrencia']  ?? '');
$descricao      = trim($_POST['descricao']        ?? '');
$medidaTomada   = trim($_POST['medida_tomada']    ?? '');

$erros = [];

if (!$alunoId)          $erros[] = 'Selecione um aluno.';
if (empty($tipo))       $erros[] = 'Selecione o tipo de ocorrência.';
if (empty($dataOcorrencia)) $erros[] = 'A data da ocorrência é obrigatória.';
if (empty($descricao))  $erros[] = 'A descrição da ocorrência é obrigatória.';

$tiposValidos = ['INDISCIPLINA_PROFESSOR', 'RECUSA_ATIVIDADE', 'BRIGA', 'FURTO', 'OUTRO'];
if (!in_array($tipo, $tiposValidos)) $erros[] = 'Tipo de ocorrência inválido.';

// Busca as informações do aluno e de sua turma
$aluno = null;
if ($alunoId) {
    $aluno = db_one(
        "SELECT id, turma_id FROM alunos WHERE id = ? AND escola_id = ? AND ativo = 1",
        [$alunoId, escola_id()]
    );
    if (!$aluno) $erros[] = 'Aluno não encontrado.';
}

if (!empty($erros)) {
    $_SESSION['erros'] = $erros;
    redirect('/ocorrencias/criar?aluno_id=' . $alunoId);
}

// Inserir ocorrência
$ocorrId = db_insert(
    "INSERT INTO ocorrencias_disciplinares
        (escola_id, aluno_id, turma_id, tipo, data_ocorrencia, descricao, medida_tomada, registrado_por, created_at, updated_at)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())",
    [
        escola_id(),
        $alunoId,
        $aluno->turma_id ?: null,
        $tipo,
        $dataOcorrencia,
        $descricao,
        $medidaTomada ?: null,
        $_SESSION['usuario']['id'] ?? null
    ]
);

flash('success', 'Ocorrência disciplinar registrada com sucesso!');
redirect('/ocorrencias/' . $ocorrId . '/imprimir');
