<?php
// actions/frequencia_registrar.php
requer_login();
verificar_csrf();

$turmaId   = (int)($_POST['turma_id'] ?? 0);
$data      = trim($_POST['data'] ?? '');
$presentes = $_POST['presentes'] ?? [];
$usuarioId = $_SESSION['usuario']['id'];

// Validação básica
if (!$turmaId || !$data) {
    flash('error', 'Turma e data são obrigatórios.');
    redirect('/frequencia/lancar');
}

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data)) {
    flash('error', 'Data inválida.');
    redirect('/frequencia/lancar');
}

// Verifica se a turma existe
$turma = db_one("SELECT id FROM turmas WHERE id = ? AND escola_id = ? AND ativa = 1", [$turmaId, escola_id()]);
if (!$turma) {
    flash('error', 'Turma não encontrada.');
    redirect('/frequencia/lancar');
}

// Busca todos os alunos ativos da turma
$todosAlunos = db_all(
    "SELECT id FROM alunos WHERE turma_id = ? AND escola_id = ? AND ativo = 1",
    [$turmaId, escola_id()]
);

require_once __DIR__ . '/../services/AlertaService.php';
$alertaService = new AlertaService();

$countPresentes = 0;
$countFaltas    = 0;

foreach ($todosAlunos as $aluno) {
    $status = in_array((string)$aluno->id, array_map('strval', $presentes)) ? 'PRESENTE' : 'FALTA';

    // updateOrCreate: atualiza se já existe, senão insere
    $existe = db_one(
        "SELECT id FROM frequencias WHERE aluno_id = ? AND data = ? AND escola_id = ?",
        [$aluno->id, $data, escola_id()]
    );

    if ($existe) {
        db_run(
            "UPDATE frequencias SET turma_id = ?, status = ?, registrado_por = ?, updated_at = NOW() WHERE id = ? AND escola_id = ?",
            [$turmaId, $status, $usuarioId, $existe->id, escola_id()]
        );
    } else {
        db_insert(
            "INSERT INTO frequencias (aluno_id, turma_id, escola_id, data, status, registrado_por, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())",
            [$aluno->id, $turmaId, escola_id(), $data, $status, $usuarioId]
        );
    }

    if ($status === 'FALTA') {
        $alertaService->verificar((int)$aluno->id);
        $countFaltas++;
    } else {
        $countPresentes++;
    }
}

// --- SINCRONIZAÇÃO COM GOOGLE SHEETS ---
$turmaCompleta = db_one("SELECT * FROM turmas WHERE id = ?", [$turmaId]);
if ($turmaCompleta && $turmaCompleta->spreadsheet_id && class_exists('\Google\Client')) {
    try {
        require_once __DIR__ . '/../services/GoogleSheetsService.php';
        $sheetsService = new \Services\GoogleSheetsService($turmaCompleta->spreadsheet_id);
        
        $listaFrequencia = [];
        // Re-mapear para usar os nomes dos alunos como chave
        foreach ($todosAlunos as $aluno) {
            $status = in_array((string)$aluno->id, array_map('strval', $presentes)) ? 'PRESENTE' : 'FALTA';
            // Buscamos o nome completo do aluno para bater com a planilha
            $alunoInfo = db_one("SELECT nome FROM alunos WHERE id = ?", [$aluno->id]);
            if ($alunoInfo) {
                $listaFrequencia[$alunoInfo->nome] = $status;
            }
        }

        $aba = "Frequência " . get_bimestre_atual($data);
        $sheetsService->lancarFrequencia($aba, $data, $listaFrequencia);
        
    } catch (Exception $e) {
        error_log("Falha ao sincronizar com Google Sheets: " . $e->getMessage());
        // Opcional: Avisar ao usuário que o Google falhou mas o local salvou
        flash('warning', 'Frequência salva localmente, mas houve um erro ao sincronizar com o Google Sheets.');
    }
}

$total = count($todosAlunos);
flash('success', "Frequência registrada! {$countPresentes} presentes, {$countFaltas} falta(s) de {$total} alunos.");
redirect('/frequencias?turma_id=' . $turmaId . '&data=' . $data);
