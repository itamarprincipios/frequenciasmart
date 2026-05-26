<?php
// actions/ocorrencias_destroy.php — Excluir ocorrência disciplinar
requer_login();
requer_role('DIRETOR', 'VICE'); // Apenas direção (Diretor/Vice) para poder excluir
verificar_csrf();

$ocorrId = isset($id) ? (int)$id : 0;

$ocorrencia = db_one(
    "SELECT id FROM ocorrencias_disciplinares WHERE id = ? AND escola_id = ?",
    [$ocorrId, escola_id()]
);

if (!$ocorrencia) {
    flash('error', 'Ocorrência não encontrada.');
    redirect('/ocorrencias');
}

db_run("DELETE FROM ocorrencias_disciplinares WHERE id = ?", [$ocorrId]);

flash('success', 'Ocorrência disciplinar excluída com sucesso!');
redirect('/ocorrencias');
