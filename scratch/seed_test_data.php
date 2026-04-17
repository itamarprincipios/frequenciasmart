<?php
// scratch/seed_test_data.php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../services/AlertaService.php';

echo "Iniciando semeação de dados de teste...\n";

// 1. Pegar a primeira escola disponível
$escola = db_one("SELECT id FROM escolas LIMIT 1");
if (!$escola) {
    echo "Erro: Nenhuma escola encontrada no banco. Execute as migrações primeiro.\n";
    exit;
}
$escolaId = $escola->id;
echo "Usando Escola ID: $escolaId\n";

// 2. Criar a turma 6º Ano
$turmaNome = '6º Ano - Teste';
$turma = db_one("SELECT id FROM turmas WHERE nome = ? AND escola_id = ?", [$turmaNome, $escolaId]);
if (!$turma) {
    $turmaId = db_insert(
        "INSERT INTO turmas (escola_id, nome, turno, ano_letivo, ativa, qr_token) VALUES (?, ?, 'MANHA', 2026, 1, ?)",
        [$escolaId, $turmaNome, 'TRM_TESTE_6ANO']
    );
    echo "Turma '$turmaNome' criada (ID: $turmaId).\n";
} else {
    $turmaId = $turma->id;
    echo "Turma '$turmaNome' já existe (ID: $turmaId).\n";
}

// 3. Criar alunos
$alunosData = [
    [
        'nome' => 'Bruno Consecutivo',
        'matricula' => 'MATR-001',
        'qr_token' => 'QR-001',
        'faltas_tipo' => 'consecutivas'
    ],
    [
        'nome' => 'Ana Intercalada',
        'matricula' => 'MATR-002',
        'qr_token' => 'QR-002',
        'faltas_tipo' => 'intercaladas'
    ]
];

$service = new AlertaService();
$hoje = date('Y-m-d');
$mesReferencia = date('Y-m');

foreach ($alunosData as $data) {
    $aluno = db_one("SELECT id FROM alunos WHERE matricula = ?", [$data['matricula']]);
    if (!$aluno) {
        $alunoId = db_insert(
            "INSERT INTO alunos (escola_id, nome, matricula, qr_token, turma_id, ativo) VALUES (?, ?, ?, ?, ?, 1)",
            [$escolaId, $data['nome'], $data['matricula'], $data['qr_token'], $turmaId]
        ) ;
        echo "Aluno '{$data['nome']}' criado.\n";
    } else {
        $alunoId = $aluno->id;
        echo "Aluno '{$data['nome']}' já existe.\n";
    }

    // Limpar faltas anteriores para o teste
    db_run("DELETE FROM frequencias WHERE aluno_id = ?", [$alunoId]);
    db_run("DELETE FROM alertas WHERE aluno_id = ?", [$alunoId]);

    if ($data['faltas_tipo'] === 'consecutivas') {
        // Criar 3 faltas consecutivas nos últimos 3 dias úteis (simplificado)
        for ($i = 0; $i < 3; $i++) {
            $dataFalta = date('Y-m-d', strtotime("-$i days"));
            db_insert(
                "INSERT INTO frequencias (aluno_id, escola_id, turma_id, data, status) VALUES (?, ?, ?, ?, 'FALTA')",
                [$alunoId, $escolaId, $turmaId, $dataFalta]
            );
        }
        echo "3 faltas consecutivas inseridas para {$data['nome']}.\n";
    } else {
        // Criar 8 faltas intercaladas no mês atual
        for ($i = 0; $i < 15; $i += 2) { // 0, 2, 4, 6, 8, 10, 12, 14 (total 8 dias)
            $dataFalta = date('Y-m-') . sprintf('%02d', $i + 1);
            if (strtotime($dataFalta) > strtotime($hoje)) continue;
            
            db_insert(
                "INSERT INTO frequencias (aluno_id, escola_id, turma_id, data, status) VALUES (?, ?, ?, ?, 'FALTA')",
                [$alunoId, $escolaId, $turmaId, $dataFalta]
            );
        }
        echo "8 faltas intercaladas inseridas para {$data['nome']}.\n";
    }

    // Rodar a verificação
    $service->verificar($alunoId);
    echo "Verificação de alertas concluída para {$data['nome']}.\n";
}

echo "Semeação concluída com sucesso!\n";
