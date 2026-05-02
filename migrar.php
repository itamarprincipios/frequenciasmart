<?php
// migrar.php — Script para atualizar o banco de dados em produção
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/aut.php';

// Proteção básica: Apenas administradores podem rodar a migração
// Se você ainda não estiver logado no servidor, comente a linha abaixo temporariamente
requer_login();
if (!tem_role('DIRETOR')) {
    die("Acesso negado.");
}

echo "<h2>Iniciando Migração...</h2>";

try {
    // 1. Adicionar colunas de vínculo no usuário (Professor)
    try {
        db_run("ALTER TABLE users ADD COLUMN turma_id INT UNSIGNED DEFAULT NULL AFTER role");
        db_run("ALTER TABLE users ADD COLUMN spreadsheet_id VARCHAR(255) DEFAULT NULL AFTER turma_id");
        echo "<p style='color:green'>✅ Colunas 'turma_id' e 'spreadsheet_id' adicionadas na tabela 'users'.</p>";
    } catch (Exception $e) {
        echo "<p style='color:orange'>⚠️ Colunas já existem ou não puderam ser adicionadas: " . $e->getMessage() . "</p>";
    }

    echo "<hr><p><b>Migração concluída com sucesso!</b></p>";
    echo "<a href='/dashboard'>Voltar para o Dashboard</a>";

} catch (Exception $e) {
    echo "<p style='color:red'>❌ Erro crítico na migração: " . $e->getMessage() . "</p>";
}
