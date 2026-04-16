<?php
// actions/alunos_store.php — Criar novo aluno
requer_login();
verificar_csrf();

$nome      = trim($_POST['nome']      ?? '');
$matricula = trim($_POST['matricula'] ?? '');
$turmaId   = (int)($_POST['turma_id'] ?? 0);

$erros = [];

// Validação
if (empty($nome))      $erros[] = 'O nome é obrigatório.';
if (strlen($nome) > 255) $erros[] = 'Nome muito longo (máx. 255 caracteres).';
if (empty($matricula)) $erros[] = 'A matricula é obrigatória.';
if (strlen($matricula) > 50) $erros[] = 'Matrícula muito longa (máx. 50 caracteres).';
if (!$turmaId)         $erros[] = 'Selecione uma turma.';

// Verifica unicidade da matricula
if ($matricula) {
    $existe = db_one("SELECT id FROM alunos WHERE matricula = ?", [$matricula]);
    if ($existe) $erros[] = 'Esta matricula já está cadastrada.';
}

// Verifica se a turma existe
if ($turmaId) {
    $turma = db_one("SELECT id FROM turmas WHERE id = ? AND ativa = 1", [$turmaId]);
    if (!$turma) $erros[] = 'Turma não encontrada.';
}

if (!empty($erros)) {
    salvar_old(['nome', 'matricula', 'turma_id']);
    $_SESSION['erros'] = $erros;
    redirect('/alunos/criar');
}

// Gera QR token único
$qrToken = 'ALU_' . strtoupper(bin2hex(random_bytes(5)));

db_insert(
    "INSERT INTO alunos (nome, matricula, qr_token, turma_id, ativo, created_at, updated_at)
     VALUES (?, ?, ?, ?, 1, NOW(), NOW())",
    [$nome, $matricula, $qrToken, $turmaId]
);

flash('success', 'Aluno cadastrado com sucesso!');
redirect('/alunos');
