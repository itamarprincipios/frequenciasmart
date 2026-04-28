<?php
// actions/frequencia_manual_update.php
requer_login();
verificar_csrf();

$alunoId = (int)($_POST['aluno_id'] ?? 0);
$turmaId = (int)($_POST['turma_id'] ?? 0);
$data    = trim($_POST['data'] ?? '');
$status  = trim($_POST['status'] ?? '');
$usuarioId = $_SESSION['usuario']['id'];

if (!$alunoId || !$data || !in_array($status, ['PRESENTE', 'FALTA'])) {
    flash('error', 'Dados inválidos.');
    redirect("/frequencias?turma_id={$turmaId}&data={$data}");
}

// Verifica se o aluno pertence à mesma escola
$aluno = db_one("SELECT id FROM alunos WHERE id = ? AND escola_id = ?", [$alunoId, escola_id()]);
if (!$aluno) {
    flash('error', 'Aluno não encontrado.');
    redirect("/frequencias?turma_id={$turmaId}&data={$data}");
}

$existe = db_one("SELECT id FROM frequencias WHERE aluno_id = ? AND data = ?", [$alunoId, $data]);

if ($existe) {
    db_run(
        "UPDATE frequencias SET status = ?, registrado_por = ?, updated_at = NOW() WHERE id = ?",
        [$status, $usuarioId, $existe->id]
    );
} else {
    db_insert(
        "INSERT INTO frequencias (aluno_id, turma_id, escola_id, data, status, registrado_por, created_at, updated_at)
         VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())",
        [$alunoId, $turmaId, escola_id(), $data, $status, $usuarioId]
    );
}

if ($status === 'FALTA') {
    require_once __DIR__ . '/../services/AlertaService.php';
    $alertaService = new AlertaService();
    $alertaService->verificar((int)$alunoId);
}

flash('success', "Status alterado para {$status} com sucesso!");
redirect("/frequencias?turma_id={$turmaId}&data={$data}");
