<?php
// migrar_conformidade.php — Migração de conformidade com o Projeto de Implementação
// Executa as alterações de schema necessárias para atender ao projeto da Escola Hildemar Pereira de Figueiredo
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/aut.php';

requer_login();
if (!tem_role('DIRETOR') && !is_super_admin()) {
    die("Acesso negado. Apenas o Diretor pode executar migrações.");
}

$erros   = [];
$sucessos = [];

function migrar(string $descricao, string $sql): void {
    global $erros, $sucessos;
    try {
        db_run($sql);
        $sucessos[] = $descricao;
    } catch (Exception $e) {
        // Se a coluna já existe (MySQL erro 1060), ignoramos silenciosamente
        if (str_contains($e->getMessage(), 'Duplicate column name') ||
            str_contains($e->getMessage(), 'already exists')) {
            $sucessos[] = "$descricao (já existia — ignorado)";
        } else {
            $erros[] = "$descricao: " . $e->getMessage();
        }
    }
}

// ============================================================
// TABELA: alunos — campos do Projeto de Implementação (Fase 2)
// ============================================================
migrar(
    "alunos.data_nascimento — data de nascimento do aluno",
    "ALTER TABLE alunos ADD COLUMN data_nascimento DATE NULL AFTER nome"
);
migrar(
    "alunos.responsavel_nome — nome do responsável legal",
    "ALTER TABLE alunos ADD COLUMN responsavel_nome VARCHAR(255) NULL AFTER data_nascimento"
);
migrar(
    "alunos.responsavel_cpf — CPF do responsável legal",
    "ALTER TABLE alunos ADD COLUMN responsavel_cpf VARCHAR(20) NULL AFTER responsavel_nome"
);
migrar(
    "alunos.responsavel_telefone — telefone/WhatsApp do responsável",
    "ALTER TABLE alunos ADD COLUMN responsavel_telefone VARCHAR(30) NULL AFTER responsavel_cpf"
);

// ============================================================
// TABELA: alertas — campos de rastreabilidade da Busca Ativa
// ============================================================
migrar(
    "alertas.intervencao_descricao — descrição da intervenção realizada",
    "ALTER TABLE alertas ADD COLUMN intervencao_descricao TEXT NULL AFTER enviado"
);
migrar(
    "alertas.intervencao_data — data/hora da intervenção",
    "ALTER TABLE alertas ADD COLUMN intervencao_data DATETIME NULL AFTER intervencao_descricao"
);
migrar(
    "alertas.intervencao_usuario_id — usuário que registrou a intervenção",
    "ALTER TABLE alertas ADD COLUMN intervencao_usuario_id INT UNSIGNED NULL AFTER intervencao_data"
);
migrar(
    "alertas.conselho_tutelar_protocolo — número do protocolo de encaminhamento ao CT",
    "ALTER TABLE alertas ADD COLUMN conselho_tutelar_protocolo VARCHAR(100) NULL AFTER intervencao_usuario_id"
);
migrar(
    "alertas.conselho_tutelar_data — data de encaminhamento ao Conselho Tutelar",
    "ALTER TABLE alertas ADD COLUMN conselho_tutelar_data DATE NULL AFTER conselho_tutelar_protocolo"
);

// ============================================================
// Saída
// ============================================================
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Migração de Conformidade — FrequenciaSmart</title>
    <style>
        body { font-family: 'Inter', sans-serif; max-width: 700px; margin: 3rem auto; padding: 1rem; }
        h2 { color: #1e293b; }
        .ok { color: #166534; background: #dcfce7; padding: .4rem .8rem; border-radius: 6px; margin: .3rem 0; font-size: .9rem; }
        .err { color: #991b1b; background: #fee2e2; padding: .4rem .8rem; border-radius: 6px; margin: .3rem 0; font-size: .9rem; }
        .btn { display: inline-block; background: #4f46e5; color: #fff; padding: .7rem 1.5rem; border-radius: 8px; text-decoration: none; margin-top: 1.5rem; font-weight: 600; }
    </style>
</head>
<body>
    <h2>⚙️ Migração de Conformidade — FrequenciaSmart</h2>
    <p style="color:#64748b; font-size:.9rem;">Projeto: Escola Municipal Hildemar Pereira de Figueiredo — Rorainópolis/RR</p>
    <hr>
    <?php foreach ($sucessos as $s): ?>
        <div class="ok">✅ <?= htmlspecialchars($s) ?></div>
    <?php endforeach; ?>
    <?php foreach ($erros as $err): ?>
        <div class="err">❌ <?= htmlspecialchars($err) ?></div>
    <?php endforeach; ?>
    <hr>
    <?php if (empty($erros)): ?>
        <p style="color:#166534; font-weight:600;">✅ Migração concluída com sucesso! Todos os campos foram adicionados ao banco de dados.</p>
    <?php else: ?>
        <p style="color:#991b1b; font-weight:600;">⚠️ Migração concluída com erros. Verifique os itens acima.</p>
    <?php endif; ?>
    <a href="/dashboard" class="btn">← Voltar ao Dashboard</a>
    <p style="margin-top:2rem; font-size:.75rem; color:#94a3b8;">
        <strong>Segurança:</strong> Recomenda-se remover ou proteger este arquivo após a execução em produção.
    </p>
</body>
</html>
