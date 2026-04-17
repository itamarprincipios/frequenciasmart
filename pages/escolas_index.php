<?php
// pages/escolas_index.php — Painel Master
requer_login();

// Verifica se é super admin
if (!($_SESSION['usuario']['is_super_admin'] ?? false)) {
    include __DIR__ . '/403.php';
    exit;
}

$escolas = db_all(
    "SELECT e.*, 
            (SELECT COUNT(*) FROM users u WHERE u.escola_id = e.id) AS users_count,
            (SELECT COUNT(*) FROM alunos a WHERE a.escola_id = e.id) AS alunos_count
     FROM escolas e
     ORDER BY e.nome"
);

$tituloPagina = 'Gerenciamento de Escolas';
include __DIR__ . '/../layout/header.php';
?>

<div class="table-wrap">
    <div class="table-head">
        <h3>🏢 Escolas Cadastradas no Sistema</h3>
        <button class="btn btn-primary" onclick="alert('Funcionalidade de cadastro em breve!')">
            + Nova Escola
        </button>
    </div>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nome da Escola</th>
                <th>Identificador (Slug)</th>
                <th style="text-align:center">Usuários</th>
                <th style="text-align:center">Alunos</th>
                <th style="text-align:center">Status</th>
                <th>Criada em</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($escolas as $e): ?>
            <tr>
                <td><?= $e->id ?></td>
                <td><strong><?= e($e->nome) ?></strong></td>
                <td><code style="background:#f1f5f9;padding:.1rem .4rem;border-radius:4px"><?= e($e->slug) ?></code></td>
                <td style="text-align:center"><?= $e->users_count ?></td>
                <td style="text-align:center"><?= $e->alunos_count ?></td>
                <td style="text-align:center">
                    <?php if ($e->ativa): ?>
                        <span class="badge badge-green">ATIVA</span>
                    <?php else: ?>
                        <span class="badge badge-red">BLOQUEADA</span>
                    <?php endif; ?>
                </td>
                <td style="color:#94a3b8;font-size:.8rem"><?= fmt_data($e->created_at) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="alert alert-success" style="margin-top:2rem">
    <strong>💡 Dica do Super Admin:</strong> Você está vendo todas as escolas cadastradas. 
    Para cadastrar uma nova escola e vender o sistema, você precisará criar o registro da escola e o primeiro usuário com perfil 'DIRETOR' vinculado a ela.
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
