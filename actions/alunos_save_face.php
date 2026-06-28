<?php
// actions/alunos_save_face.php — Salva a assinatura facial (vetor) do aluno
requer_login();
verificar_csrf();
requer_role('DIRETOR', 'VICE', 'ORIENTADORA');

// $id vem do roteador
$aluno = db_one("SELECT * FROM alunos WHERE id = ? AND escola_id = ?", [$id, escola_id()]);

if (!$aluno) {
    http_response_code(404);
    echo json_encode(['sucesso' => false, 'erro' => 'Aluno não encontrado ou sem permissão.']);
    exit;
}

$descriptorRaw = trim($_POST['face_descriptor'] ?? '');

if (empty($descriptorRaw)) {
    http_response_code(400);
    echo json_encode(['sucesso' => false, 'erro' => 'Assinatura facial não fornecida.']);
    exit;
}

// Opcional: Validar se é um formato JSON válido representando um array
$descriptorArray = json_decode($descriptorRaw, true);
if (!is_array($descriptorArray) || count($descriptorArray) !== 128) {
    http_response_code(400);
    echo json_encode(['sucesso' => false, 'erro' => 'Formato do mapeamento facial inválido (deve conter 128 pontos).']);
    exit;
}

db_run(
    "UPDATE alunos SET face_descriptor = ?, updated_at = NOW() WHERE id = ? AND escola_id = ?",
    [$descriptorRaw, $id, escola_id()]
);

flash('success', 'Biometria facial de ' . $aluno->nome . ' cadastrada com sucesso!');
echo json_encode(['sucesso' => true]);
exit;
