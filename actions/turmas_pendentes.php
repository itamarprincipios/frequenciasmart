<?php
// actions/turmas_pendentes.php — API JSON: turmas sem frequência no dia/turno atual
requer_login();

header('Content-Type: application/json; charset=utf-8');

$data  = trim($_GET['data']  ?? date('Y-m-d'));
$turno = trim($_GET['turno'] ?? '');

// Valida data
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data)) {
    $data = date('Y-m-d');
}

// Valida turno
$turnosValidos = ['MANHA', 'TARDE', 'NOITE'];
if (!in_array($turno, $turnosValidos)) {
    $turno = '';
}

// Query: turmas ativas que NÃO têm nenhum registro de frequência naquele dia
// Uma turma é considerada "lançada" quando ao menos 1 aluno dela tem frequência na data.
$sql = "
    SELECT t.id, t.nome, t.turno,
           COUNT(a.id) AS total_alunos
    FROM turmas t
    LEFT JOIN alunos a ON a.turma_id = t.id AND a.ativo = 1 AND a.escola_id = ?
    WHERE t.ativa = 1
      AND t.escola_id = ?
      AND t.id NOT IN (
          SELECT DISTINCT f.turma_id
          FROM frequencias f
          WHERE f.data = ? AND f.escola_id = ?
      )
";

$params = [escola_id(), escola_id(), $data, escola_id()];

if ($turno !== '') {
    $sql .= " AND t.turno = ?";
    $params[] = $turno;
}

$sql .= " GROUP BY t.id, t.nome, t.turno ORDER BY t.turno, t.nome";

$turmasPendentes = db_all($sql, $params);

echo json_encode([
    'data'    => $data,
    'turno'   => $turno,
    'total'   => count($turmasPendentes),
    'turmas'  => $turmasPendentes,
]);
