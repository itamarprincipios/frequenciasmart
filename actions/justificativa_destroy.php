<?php
// actions/justificativa_destroy.php — Remover uma justificativa e restaurar status de falta
requer_login();
requer_role('DIRETOR', 'VICE'); // Apenas Diretor/Vice podem excluir justificativas para segurança
verificar_csrf();

$justId = isset($id) ? (int)$id : 0;

$justificativa = db_one(
    "SELECT * FROM justificativas_faltas WHERE id = ? AND escola_id = ?",
    [$justId, escola_id()]
);

if (!$justificativa) {
    flash('error', 'Justificativa não encontrada.');
    redirect('/justificativas');
}

// 1. Restaurar o status da frequência para FALTA
db_run(
    "UPDATE frequencias SET status = 'FALTA', updated_at = NOW() WHERE id = ?",
    [$justificativa->frequencia_id]
);

// 2. Excluir o registro de justificativa
db_run(
    "DELETE FROM justificativas_faltas WHERE id = ?",
    [$justId]
);

flash('success', 'Justificativa removida com sucesso! A falta voltou a contar como pendente/não justificada.');
redirect('/justificativas');
