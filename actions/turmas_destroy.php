<?php
// actions/turmas_destroy.php — Soft-delete de turma
requer_role('DIRETOR', 'VICE');
verificar_csrf();

// $id vem do roteador
$turma = db_one("SELECT * FROM turmas WHERE id = ? AND escola_id = ?", [$id, escola_id()]);
if (!$turma) {
    http_response_code(404);
    die('<p>Turma não encontrada ou sem permissão.</p>');
}

// Marcamos como inativa
db_run("UPDATE turmas SET ativa = 0, updated_at = NOW() WHERE id = ? AND escola_id = ?", [$id, escola_id()]);

flash('success', "Turma '{$turma->nome}' excluída com sucesso.");
redirect('/turmas');
