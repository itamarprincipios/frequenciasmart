<?php
// actions/usuarios_update.php
verificar_csrf();
requer_role('DIRETOR');

$id = (int)$id;

// Verifica se o usuário pertence à escola do gestor
$u = db_one("SELECT id FROM users WHERE id = ? AND escola_id = ?", [$id, escola_id()]);
if (!$u) {
    flash('error', 'Usuário não encontrado.');
    redirect('/usuarios');
}

$nome     = trim($_POST['nome'] ?? '');
$email    = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');
$role     = $_POST['role'] ?? 'ASSISTENTE';
$ativo    = (int)($_POST['ativo'] ?? 1);

if (!$nome || !$email) {
    flash('error', 'Nome e Email são obrigatórios.');
    redirect("/usuarios/{$id}/editar");
}

// Verifica e-mail duplicado (se mudou)
$existe = db_one("SELECT id FROM users WHERE email = ? AND id != ?", [$email, $id]);
if ($existe) {
    flash('error', 'Este e-mail já está em uso por outro usuário.');
    redirect("/usuarios/{$id}/editar");
}

try {
    if ($password) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        db_run(
            "UPDATE users SET nome = ?, email = ?, password = ?, role = ?, ativo = ? WHERE id = ?",
            [$nome, $email, $hash, $role, $ativo, $id]
        );
    } else {
        db_run(
            "UPDATE users SET nome = ?, email = ?, role = ?, ativo = ? WHERE id = ?",
            [$nome, $email, $role, $ativo, $id]
        );
    }
    flash('success', 'Usuário atualizado com sucesso.');
    redirect('/usuarios');
} catch (Exception $e) {
    flash('error', 'Erro ao atualizar usuário.');
    redirect("/usuarios/{$id}/editar");
}
