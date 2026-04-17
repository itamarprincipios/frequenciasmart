<?php
// actions/usuarios_destroy.php
verificar_csrf();
requer_role('DIRETOR');

$id = (int)$id;

// Impede auto-exclusão
if ($id === usuario_logado()['id']) {
    flash('error', 'Você não pode excluir sua própria conta.');
    redirect('/usuarios');
}

// Verifica se pertence à escola
$u = db_one("SELECT id FROM users WHERE id = ? AND escola_id = ?", [$id, escola_id()]);
if (!$u) {
    flash('error', 'Usuário não encontrado.');
    redirect('/usuarios');
}

try {
    db_run("DELETE FROM users WHERE id = ?", [$id]);
    flash('success', 'Usuário excluído permanentemente.');
} catch (Exception $e) {
    flash('error', 'Erro ao excluir usuário. Ele pode ter registros vinculados.');
}

redirect('/usuarios');
