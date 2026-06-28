<?php
// actions/alunos_save_face.php — Salva a assinatura facial (vetor) do aluno com diagnósticos detalhados
header('Content-Type: application/json; charset=utf-8');
requer_login();

// Substitui a chamada padrão de verificar_csrf() para retornar resposta em JSON caso o CSRF falhe
$token = $_POST['csrf_token'] ?? '';
if (!hash_equals(csrf_token(), $token)) {
    http_response_code(419);
    echo json_encode(['sucesso' => false, 'erro' => 'Token CSRF inválido ou expirado. Recarregue a página.']);
    exit;
}

requer_role('DIRETOR', 'VICE', 'ORIENTADORA');

// $id vem do roteador index.php
try {
    $aluno = db_one("SELECT * FROM alunos WHERE id = ? AND escola_id = ?", [$id, escola_id()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['sucesso' => false, 'erro' => 'Erro ao carregar dados do aluno: ' . $e->getMessage()]);
    exit;
}

if (!$aluno) {
    http_response_code(404);
    echo json_encode(['sucesso' => false, 'erro' => 'Aluno não encontrado ou sem permissão de acesso.']);
    exit;
}

$descriptorRaw = trim($_POST['face_descriptor'] ?? '');

if (empty($descriptorRaw)) {
    http_response_code(400);
    echo json_encode(['sucesso' => false, 'erro' => 'Assinatura facial não fornecida pela câmera.']);
    exit;
}

// Validar se é um formato JSON válido representando um array de float
$descriptorArray = json_decode($descriptorRaw, true);
if (!is_array($descriptorArray) || count($descriptorArray) !== 128) {
    http_response_code(400);
    echo json_encode([
        'sucesso' => false, 
        'erro' => 'Formato do mapeamento facial inválido. Certifique-se de que o rosto do aluno está focado na câmera.'
    ]);
    exit;
}

try {
    db_run(
        "UPDATE alunos SET face_descriptor = ?, updated_at = NOW() WHERE id = ? AND escola_id = ?",
        [$descriptorRaw, $id, escola_id()]
    );
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'sucesso' => false, 
        'erro' => 'Erro ao gravar no banco. Verifique se executou o comando SQL no phpMyAdmin: ' . $e->getMessage()
    ]);
    exit;
}

flash('success', 'Biometria facial de ' . $aluno->nome . ' cadastrada com sucesso!');
echo json_encode(['sucesso' => true]);
exit;
