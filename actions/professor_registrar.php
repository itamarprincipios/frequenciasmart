<?php
// actions/professor_registrar.php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../helpers.php';

verificar_csrf();

$nome           = trim($_POST['nome'] ?? '');
$email          = trim($_POST['email'] ?? '');
$password       = trim($_POST['password'] ?? '');
$escola_id      = (int)($_POST['escola_id'] ?? 0);
$turma_id       = (int)($_POST['turma_id'] ?? 0);
$spreadsheet_id = trim($_POST['spreadsheet_id'] ?? '');

if (!$nome || !$email || !$password || !$escola_id || !$turma_id) {
    flash('error', 'Por favor, preencha todos os campos obrigatórios.');
    redirect('/cadastro_professor.php');
}

if (strlen($password) < 6) {
    flash('error', 'A senha deve ter pelo menos 6 caracteres.');
    redirect('/cadastro_professor.php');
}

// Verifica se email já existe
$existe = db_one("SELECT id FROM users WHERE email = ?", [$email]);
if ($existe) {
    flash('error', 'Este e-mail já está cadastrado. Tente fazer login.');
    redirect('/cadastro_professor.php');
}

$hash = password_hash($password, PASSWORD_DEFAULT);

try {
    // Insere como DIRETOR (ou crie um cargo PROFESSOR se preferir, mas DIRETOR tem as permissões necessárias)
    db_insert(
        "INSERT INTO users (nome, email, password, role, escola_id, turma_id, spreadsheet_id, ativo) VALUES (?, ?, ?, 'DIRETOR', ?, ?, ?, 1)",
        [$nome, $email, $hash, $escola_id, $turma_id, $spreadsheet_id]
    );

    flash('success', 'Cadastro realizado com sucesso! Você já pode fazer login.');
    redirect('/login');

} catch (Exception $e) {
    error_log("Erro no cadastro de professor: " . $e->getMessage());
    flash('error', 'Erro ao realizar cadastro. Tente novamente mais tarde.');
    redirect('/cadastro_professor.php');
}
