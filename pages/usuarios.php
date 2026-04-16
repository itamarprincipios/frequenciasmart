<?php
// pages/usuarios.php
requer_role('DIRETOR');

$usuarios = db_all("SELECT * FROM users ORDER BY nome");

$tituloPagina = 'Usuários';
include __DIR__ . '/../layout/header.php';
?>

<div class="table-wrap">
    <div class="table-head">
        <h3>👥 Usuários do Sistema</h3>
    </div>
    <table>
        <thead>
            <tr><th>Nome</th><th>E-mail</th><th>Perfil</th><th>Status</th><th>Cadastrado</th></tr>
        </thead>
        <tbody>
            <?php if (empty($usuarios)): ?>
            <tr><td colspan="5" style="text-align:center;color:#94a3b8;padding:1.5rem">Nenhum usuário</td></tr>
            <?php else: foreach ($usuarios as $u): ?>
            <?php
            $badgeClass = match($u->role) {
                'DIRETOR', 'VICE' => 'badge-blue',
                'ORIENTADORA'     => 'badge-green',
                default           => 'badge-gray',
            };
            ?>
            <tr>
                <td><strong><?= e($u->nome) ?></strong></td>
                <td style="color:#64748b;font-size:.8rem"><?= e($u->email) ?></td>
                <td><span class="badge <?= e($badgeClass) ?>"><?= e($u->role) ?></span></td>
                <td>
                    <?php if ($u->ativo): ?>
                        <span class="badge badge-green">● Ativo</span>
                    <?php else: ?>
                        <span class="badge badge-red">● Inativo</span>
                    <?php endif; ?>
                </td>
                <td style="color:#94a3b8;font-size:.8rem"><?= fmt_data($u->created_at) ?></td>
            </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
