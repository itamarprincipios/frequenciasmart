<?php
// pages/usuarios.php
requer_login();
if (!is_super_admin() && !tem_role('DIRETOR')) {
    include __DIR__ . '/403.php';
    exit;
}

$isMaster = is_super_admin();

if ($isMaster) {
    $usuarios = db_all(
        "SELECT u.*, e.nome AS escola_nome 
         FROM users u 
         LEFT JOIN escolas e ON e.id = u.escola_id 
         ORDER BY e.nome, u.nome"
    );
} else {
    $usuarios = db_all("SELECT * FROM users WHERE escola_id = ? ORDER BY nome", [escola_id()]);
}

$tituloPagina = $isMaster ? 'Gerenciar Usuários / Diretores' : 'Usuários';
include __DIR__ . '/../layout/header.php';
?>

<div class="table-wrap">
    <div class="table-head">
        <h3><?= $isMaster ? '👥 Usuários / Diretores de todas as Escolas' : '👥 Usuários da Escola' ?></h3>
        <a href="/usuarios/criar" class="btn btn-primary">+ Novo Usuário</a>
    </div>
    <table>
        <thead>
            <tr>
                <th>Nome</th>
                <th>E-mail</th>
                <?php if ($isMaster): ?><th>Escola</th><?php endif; ?>
                <th>Cargo</th>
                <th>Status</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($usuarios)): ?>
            <tr><td colspan="5" style="text-align:center;color:#94a3b8;padding:1.5rem">Nenhum usuário</td></tr>
            <?php else: foreach ($usuarios as $u): ?>
            <?php
            $roleLabel = match($u->role) {
                'DIRETOR'     => 'Gestor(a)',
                'VICE'        => 'Vice Gestor',
                'ORIENTADORA' => 'Orientador(a)',
                'ASSISTENTE'  => 'Assistente',
                default       => $u->role
            };
            $badgeClass = match($u->role) {
                'DIRETOR', 'VICE' => 'badge-blue',
                'ORIENTADORA'     => 'badge-green',
                default           => 'badge-gray',
            };
            ?>
            <tr>
                <td><strong><?= e($u->nome) ?></strong></td>
                <td style="color:#64748b;font-size:.8rem"><?= e($u->email) ?></td>
                <?php if ($isMaster): ?>
                    <td style="font-size:.75rem"><?= e($u->escola_nome ?? '--') ?></td>
                <?php endif; ?>
                <td><span class="badge <?= e($badgeClass) ?>"><?= e($roleLabel) ?></span></td>
                <td>
                    <?php if ($u->ativo): ?>
                        <span class="badge badge-green">● Ativo</span>
                    <?php else: ?>
                        <span class="badge badge-red">● Inativo</span>
                    <?php endif; ?>
                </td>
                <td>
                    <div style="display:flex; gap:.5rem">
                        <a href="/usuarios/<?= $u->id ?>/editar" class="btn btn-outline" style="padding:.3rem .6rem; font-size:.7rem">Editar</a>
                        <?php if ($u->id !== usuario_logado()['id']): ?>
                        <form method="POST" action="/usuarios/<?= $u->id ?>/excluir" onsubmit="return confirm('Excluir este usuário permanentemente?')">
                            <?php csrf_field(); ?>
                            <button type="submit" class="btn btn-danger" style="padding:.3rem .6rem; font-size:.7rem">Excluir</button>
                        </form>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
