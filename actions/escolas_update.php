<?php
// actions/escolas_update.php — Atualiza dados de uma escola
verificar_csrf();
requer_super_admin();

$escola = db_one("SELECT * FROM escolas WHERE id = ?", [$id]);
if (!$escola) {
    flash('error', 'Escola não encontrada.');
    redirect('/escolas');
}

$nome  = trim($_POST['nome_escola'] ?? '');
$ativa = (int)($_POST['ativa'] ?? 1);

if (!$nome) {
    flash('error', 'O nome da escola é obrigatório.');
    redirect("/escolas/{$id}/editar");
}

db_run(
    "UPDATE escolas SET nome = ?, ativa = ? WHERE id = ?",
    [$nome, $ativa, $id]
);

$status = $ativa ? 'ativada' : 'bloqueada';
flash('success', "Escola \"{$nome}\" atualizada — agora está {$status}.");
redirect('/escolas');
