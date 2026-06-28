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

$nome               = trim($_POST['nome']               ?? '');
$matricula          = trim($_POST['matricula']          ?? '');
$turmaId            = (int)($_POST['turma_id']          ?? 0);
$dataNascimento     = trim($_POST['data_nascimento']    ?? '') ?: null;
$responsavelNome    = trim($_POST['responsavel_nome']   ?? '');
$responsavelCpf     = trim($_POST['responsavel_cpf']    ?? '') ?: null;
$responsavelTel     = trim($_POST['responsavel_telefone'] ?? '') ?: null;

$erros = [];

if (empty($nome))             $erros[] = 'O nome é obrigatório.';
if (strlen($nome) > 255)      $erros[] = 'Nome muito longo (máx. 255 caracteres).';
if (empty($matricula))        $erros[] = 'A matrícula é obrigatória.';
if (!$turmaId)                $erros[] = 'Selecione uma turma.';
if (empty($responsavelNome))  $erros[] = 'O nome do responsável é obrigatório.';

// Unicidade da matrícula (exceto o próprio aluno)
if ($matricula) {
    $existe = db_one("SELECT id FROM alunos WHERE matricula = ? AND id != ? AND escola_id = ?", [$matricula, $id, escola_id()]);
    if ($existe) $erros[] = 'Esta matrícula já está cadastrada para outro aluno nesta escola.';
}

if ($turmaId) {
    $turma = db_one("SELECT id FROM turmas WHERE id = ? AND escola_id = ? AND ativa = 1", [$turmaId, escola_id()]);
    if (!$turma) $erros[] = 'Turma não encontrada.';
}

if (!empty($erros)) {
    salvar_old(['nome', 'matricula', 'turma_id', 'data_nascimento', 'responsavel_nome', 'responsavel_cpf', 'responsavel_telefone']);
    $_SESSION['erros'] = $erros;
    redirect('/alunos/' . $id . '/editar');
}

db_run(
    "UPDATE alunos SET 
        nome = ?, data_nascimento = ?, matricula = ?, turma_id = ?,
        responsavel_nome = ?, responsavel_cpf = ?, responsavel_telefone = ?,
        updated_at = NOW()
     WHERE id = ? AND escola_id = ?",
    [$nome, $dataNascimento, $matricula, $turmaId,
     $responsavelNome, $responsavelCpf, $responsavelTel,
     $id, escola_id()]
);

flash('success', 'Aluno atualizado com sucesso!');
redirect('/alunos');
