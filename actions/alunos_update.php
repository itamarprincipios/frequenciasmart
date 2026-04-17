<?php
// actions/alunos_update.php — Atualizar aluno existente
requer_login();
verificar_csrf();

// $id vem do roteador
$aluno = db_one("SELECT * FROM alunos WHERE id = ? AND escola_id = ?", [$id, escola_id()]);
if (!$aluno) {
    http_response_code(404);
    die('<p>Aluno não encontrado ou sem permissão.</p>');
}

$nome      = trim($_POST['nome']      ?? '');
$matricula = trim($_POST['matricula'] ?? '');
$turmaId   = (int)($_POST['turma_id'] ?? 0);

$erros = [];

if (empty($nome))        $erros[] = 'O nome é obrigatório.';
if (strlen($nome) > 255) $erros[] = 'Nome muito longo (máx. 255 caracteres).';
if (empty($matricula))   $erros[] = 'A matricula é obrigatória.';
if (!$turmaId)           $erros[] = 'Selecione uma turma.';

// Unicidade da matricula (exceto o próprio aluno)
if ($matricula) {
    $existe = db_one("SELECT id FROM alunos WHERE matricula = ? AND id != ? AND escola_id = ?", [$matricula, $id, escola_id()]);
    if ($existe) $erros[] = 'Esta matricula já está cadastrada para outro aluno nesta escola.';
}

if ($turmaId) {
    $turma = db_one("SELECT id FROM turmas WHERE id = ? AND escola_id = ? AND ativa = 1", [$turmaId, escola_id()]);
    if (!$turma) $erros[] = 'Turma não encontrada.';
}

if (!empty($erros)) {
    salvar_old(['nome', 'matricula', 'turma_id']);
    $_SESSION['erros'] = $erros;
    redirect('/alunos/' . $id . '/editar');
}

db_run(
    "UPDATE alunos SET nome = ?, matricula = ?, turma_id = ?, updated_at = NOW() WHERE id = ? AND escola_id = ?",
    [$nome, $matricula, $turmaId, $id, escola_id()]
);

flash('success', 'Aluno atualizado com sucesso!');
redirect('/alunos');
