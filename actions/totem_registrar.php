<?php
// actions/totem_registrar.php — Registra presença em tempo real vinda do Totem
header('Content-Type: application/json; charset=utf-8');
requer_login();

$alunoId = (int)($_POST['aluno_id'] ?? 0);

if (!$alunoId) {
    http_response_code(400);
    echo json_encode(['sucesso' => false, 'erro' => 'Aluno não especificado.']);
    exit;
}

// 1. Validar e carregar o aluno e sua turma
$aluno = db_one(
    "SELECT a.id, a.nome, a.turma_id, t.nome AS turma_nome, t.turno AS turma_turno
     FROM alunos a
     JOIN turmas t ON t.id = a.turma_id
     WHERE a.id = ? AND a.escola_id = ? AND a.ativo = 1 AND t.ativa = 1",
    [$alunoId, escola_id()]
);

if (!$aluno) {
    http_response_code(404);
    echo json_encode(['sucesso' => false, 'erro' => 'Aluno não encontrado ou inativo.']);
    exit;
}

$dataHoje = date('Y-m-d');
$horaHoje = date('H:i:s');

// 2. Verificar se a chamada do dia para este aluno já foi feita
$jaExiste = db_one(
    "SELECT id, status FROM frequencias WHERE aluno_id = ? AND data = ? AND escola_id = ?",
    [$aluno->id, $dataHoje, escola_id()]
);

if ($jaExiste) {
    echo json_encode([
        'sucesso'       => true,
        'ja_registrado' => true,
        'aluno_nome'    => $aluno->nome,
        'turma_nome'    => $aluno->turma_nome,
        'status'        => $jaExiste->status,
        'hora'          => date('H:i')
    ]);
    exit;
}

// 3. Registrar presença automática
db_insert(
    "INSERT INTO frequencias (aluno_id, turma_id, escola_id, data, status, registrado_por, created_at, updated_at)
     VALUES (?, ?, ?, ?, 'PRESENTE', ?, NOW(), NOW())",
    [$aluno->id, $aluno->turma_id, escola_id(), $dataHoje, usuario_logado()['id']]
);

// 4. Acionar o serviço de alertas da Busca Ativa de forma integrada e assíncrona
require_once __DIR__ . '/../services/AlertaService.php';
try {
    $alertaService = new AlertaService();
    $alertaService->verificar($aluno->id);
} catch (Exception $e) {
    // Log do erro se necessário, mas não interrompe a resposta do totem
    error_log("Erro no AlertaService no Totem: " . $e->getMessage());
}

echo json_encode([
    'sucesso'       => true,
    'ja_registrado' => false,
    'aluno_nome'    => $aluno->nome,
    'turma_nome'    => $aluno->turma_nome,
    'status'        => 'PRESENTE',
    'hora'          => date('H:i')
]);
exit;
