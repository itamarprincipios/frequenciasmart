<?php
// actions/usuarios_update.php
verificar_csrf();
requer_role('DIRETOR');

$id = (int)$id;
$isMaster = $_SESSION['usuario']['is_super_admin'] ?? false;

// Verifica se o usuário pertence à escola ou se é Super Admin
if ($isMaster) {
    $u = db_one("SELECT id, escola_id FROM users WHERE id = ?", [$id]);
} else {
    $u = db_one("SELECT id, escola_id FROM users WHERE id = ? AND escola_id = ?", [$id, escola_id()]);
}

if (!$u) {
    flash('error', 'Usuário não encontrado ou você não tem permissão.');
    redirect('/usuarios');
}

$nome      = trim($_POST['nome'] ?? '');
$email     = trim($_POST['email'] ?? '');
$password  = trim($_POST['password'] ?? '');
$role      = $_POST['role'] ?? 'ASSISTENTE';
$ativo     = (int)($_POST['ativo'] ?? 1);
$escola_id = $isMaster ? (int)($_POST['escola_id'] ?? $u->escola_id) : $u->escola_id;

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
            "UPDATE users SET nome = ?, email = ?, password = ?, role = ?, ativo = ?, escola_id = ? WHERE id = ?",
            [$nome, $email, $hash, $role, $ativo, $escola_id, $id]
        );
    } else {
        db_run(
            "UPDATE users SET nome = ?, email = ?, role = ?, ativo = ?, escola_id = ? WHERE id = ?",
            [$nome, $email, $role, $ativo, $escola_id, $id]
        );
    }
    flash('success', 'Usuário atualizado com sucesso.');
    redirect('/usuarios');
} catch (Exception $e) {
    flash('error', 'Erro ao atualizar usuário.');
    redirect("/usuarios/{$id}/editar");
}
