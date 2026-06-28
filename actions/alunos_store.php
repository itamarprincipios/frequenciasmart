<?php
// actions/alunos_store.php — Criar novo aluno
requer_login();
verificar_csrf();

$nome               = trim($_POST['nome']               ?? '');
$matricula          = trim($_POST['matricula']          ?? '');
$turmaId            = (int)($_POST['turma_id']          ?? 0);
$dataNascimento     = trim($_POST['data_nascimento']    ?? '') ?: null;
$responsavelNome    = trim($_POST['responsavel_nome']   ?? '');
$responsavelCpf     = trim($_POST['responsavel_cpf']    ?? '') ?: null;
$responsavelTel     = trim($_POST['responsavel_telefone'] ?? '') ?: null;

$erros = [];

if (empty($nome))              $erros[] = 'O nome é obrigatório.';
if (strlen($nome) > 255)       $erros[] = 'Nome muito longo (máx. 255 caracteres).';
if (empty($matricula))         $erros[] = 'A matrícula é obrigatória.';
if (strlen($matricula) > 50)   $erros[] = 'Matrícula muito longa (máx. 50 caracteres).';
if (!$turmaId)                 $erros[] = 'Selecione uma turma.';
if (empty($responsavelNome))   $erros[] = 'O nome do responsável é obrigatório.';

// Verifica unicidade da matrícula
if ($matricula) {
    $existe = db_one("SELECT id FROM alunos WHERE matricula = ? AND escola_id = ?", [$matricula, escola_id()]);
    if ($existe) $erros[] = 'Esta matrícula já está cadastrada nesta escola.';
}

// Verifica se a turma existe
if ($turmaId) {
    $turma = db_one("SELECT id FROM turmas WHERE id = ? AND escola_id = ? AND ativa = 1", [$turmaId, escola_id()]);
    if (!$turma) $erros[] = 'Turma não encontrada.';
}

if (!empty($erros)) {
    salvar_old(['nome', 'matricula', 'turma_id', 'data_nascimento', 'responsavel_nome', 'responsavel_cpf', 'responsavel_telefone']);
    $_SESSION['erros'] = $erros;
    redirect('/alunos/criar');
}

$qrToken = 'ALU_' . strtoupper(bin2hex(random_bytes(5)));

db_insert(
    "INSERT INTO alunos 
        (nome, data_nascimento, matricula, qr_token, turma_id, escola_id,
         responsavel_nome, responsavel_cpf, responsavel_telefone,
         ativo, created_at, updated_at)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1, NOW(), NOW())",
    [$nome, $dataNascimento, $matricula, $qrToken, $turmaId, escola_id(),
     $responsavelNome, $responsavelCpf, $responsavelTel]
);

flash('success', 'Aluno cadastrado com sucesso!');
redirect('/alunos');
