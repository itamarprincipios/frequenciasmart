<?php
// importar.php — Script para importar turmas e alunos de um arquivo CSV
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';

requer_login();
if (!tem_role('DIRETOR')) die("Acesso negado.");

$arquivoCsv = __DIR__ . '/matricula.csv';

echo "<h2>📥 Importador de Alunos e Turmas</h2>";

if (!file_exists($arquivoCsv)) {
    echo "<p style='color:red'>❌ Arquivo 'matricula.csv' não encontrado na pasta raiz.</p>";
    echo "<p>Por favor, salve seu Excel como CSV com o nome 'matricula.csv' e faça o upload.</p>";
    exit;
}

$escola_id = escola_id();
if (!$escola_id) {
    die("❌ Você precisa estar vinculado a uma escola para importar.");
}

try {
    $handle = fopen($arquivoCsv, "r");
    $header = fgetcsv($handle, 1000, ","); // Pula o cabeçalho

    $turmasCriadas = 0;
    $alunosCriados = 0;

    echo "<ul>";

    while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
        // Exemplo de colunas no CSV:
        // Index 0: Turma (ex: 2º Ano B)
        // Index 1: Turno (MANHA, TARDE ou NOITE)
        // Index 2: Nome do Aluno
        // Index 3: Matrícula
        
        $turmaNome  = trim($row[0]);
        $turmaTurno = strtoupper(trim($row[1] ?? 'MANHA'));
        $alunoNome  = trim($row[2]);
        $alunoMatr  = trim($row[3]);

        if (empty($turmaNome) || empty($alunoNome)) continue;

        // 1. Verificar/Criar Turma
        $turma = db_one("SELECT id FROM turmas WHERE nome = ? AND escola_id = ?", [$turmaNome, $escola_id]);
        if (!$turma) {
            $qrToken = 'TRM_' . bin2hex(random_bytes(5));
            $turmaId = db_insert(
                "INSERT INTO turmas (nome, turno, escola_id, qr_token, ativa) VALUES (?, ?, ?, ?, 1)",
                [$turmaNome, $turmaTurno, $escola_id, $qrToken]
            );
            $turmasCriadas++;
        } else {
            $turmaId = $turma->id;
        }

        // 2. Criar Aluno (Verifica se já existe por matrícula)
        $existe = db_one("SELECT id FROM alunos WHERE matricula = ?", [$alunoMatr]);
        if (!$existe) {
            $qrToken = bin2hex(random_bytes(16));
            db_insert(
                "INSERT INTO alunos (nome, matricula, qr_token, turma_id, escola_id, ativo) VALUES (?, ?, ?, ?, ?, 1)",
                [$alunoNome, $alunoMatr, $qrToken, $turmaId, $escola_id]
            );
            $alunosCriados++;
        }
    }

    fclose($handle);

    echo "</ul>";
    echo "<p style='color:green; font-weight:bold;'>✅ Importação concluída!</p>";
    echo "<p>Novas Turmas: $turmasCriadas</p>";
    echo "<p>Novos Alunos: $alunosCriados</p>";
    echo "<br><a href='/dashboard'>Ir para o Painel</a>";

} catch (Exception $e) {
    echo "<p style='color:red'>❌ Erro na importação: " . $e->getMessage() . "</p>";
}
