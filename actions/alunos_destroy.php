<?php
// actions/alunos_destroy.php — Soft-delete de aluno
requer_login();
verificar_csrf();

// $id vem do roteador
$aluno = db_one("SELECT * FROM alunos WHERE id = ?", [$id]);
if (!$aluno) {
    http_response_code(404);
    die('<p>Aluno não encontrado.</p>');
}

// Soft-delete: apenas marca como inativo
db_run("UPDATE alunos SET ativo = 0, updated_at = NOW() WHERE id = ?", [$id]);

flash('success', 'Aluno excluído com sucesso.');
redirect('/alunos');
