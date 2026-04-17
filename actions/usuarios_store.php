<?php
// actions/usuarios_store.php
verificar_csrf();
requer_role('DIRETOR');

$nome     = trim($_POST['nome'] ?? '');
$email    = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');
$role     = $_POST['role'] ?? 'ASSISTENTE';
$ativo    = (int)($_POST['ativo'] ?? 1);

$role     = $_POST['role'] ?? 'ASSISTENTE';
$ativo    = (int)($_POST['ativo'] ?? 1);

$isMaster  = $_SESSION['usuario']['is_super_admin'] ?? false;
$escola_id = (int)($isMaster ? ($_POST['escola_id'] ?? 0) : escola_id());

if (!$nome || !$email || !$password || ($isMaster && !$escola_id)) {
    flash('error', 'Nome, Email, Senha e Escola (para Master) são obrigatórios.');
    redirect('/usuarios/criar');
}

// Verifica se email já existe
$existe = db_one("SELECT id FROM users WHERE email = ?", [$email]);
if ($existe) {
    flash('error', 'Este e-mail já está em uso.');
    redirect('/usuarios/criar');
}

$hash = password_hash($password, PASSWORD_DEFAULT);

try {
    db_insert(
        "INSERT INTO users (nome, email, password, role, ativo, escola_id) VALUES (?, ?, ?, ?, ?, ?)",
        [$nome, $email, $hash, $role, $ativo, $escola_id]
    );
    flash('success', 'Usuário cadastrado com sucesso.');
    redirect('/usuarios');
} catch (Exception $e) {
    flash('error', 'Erro ao cadastrar usuário.');
    redirect('/usuarios/criar');
}
