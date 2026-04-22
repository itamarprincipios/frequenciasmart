<?php
// actions/escolas_toggle.php — Alterna status ativa/bloqueada da escola
verificar_csrf();
requer_super_admin();

$escola = db_one("SELECT * FROM escolas WHERE id = ?", [$id]);
if (!$escola) {
    flash('error', 'Escola não encontrada.');
    redirect('/escolas');
}

$novoStatus = $escola->ativa ? 0 : 1;
db_run("UPDATE escolas SET ativa = ? WHERE id = ?", [$novoStatus, $id]);

$acao = $novoStatus ? 'ativada' : 'bloqueada (acesso suspenso)';
flash('success', "Escola \"{$escola->nome}\" {$acao} com sucesso.");
redirect('/escolas');
