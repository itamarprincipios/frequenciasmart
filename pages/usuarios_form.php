<?php
// pages/usuarios_form.php
requer_role('DIRETOR');

$id = $id ?? null;
$u = null;
if ($id) {
    $u = db_one("SELECT * FROM users WHERE id = ? AND escola_id = ?", [$id, escola_id()]);
    if (!$u) {
        flash('error', 'Usuário não encontrado.');
        redirect('/usuarios');
    }
}

$isMaster = $_SESSION['usuario']['is_super_admin'] ?? false;
$escolas = [];
if ($isMaster) {
    $escolas = db_all("SELECT id, nome FROM escolas WHERE ativa = 1 ORDER BY nome");
}

$tituloPagina = $id ? 'Editar Usuário' : 'Novo Usuário';
include __DIR__ . '/../layout/header.php';
?>

<div class="table-wrap" style="max-width: 600px; margin: 0 auto;">
    <div class="table-head">
        <h3><?= $id ? '📝 Editar Usuário' : '➕ Cadastrar Novo Usuário' ?></h3>
    </div>
    
    <form method="POST" action="<?= $id ? "/usuarios/{$id}" : "/usuarios" ?>" style="padding: 1.5rem;">
        <?php csrf_field(); ?>

        <?php if ($isMaster): ?>
        <div class="form-group" style="margin-bottom: 1.5rem; padding: 1rem; background: #fef3c7; border-radius: 8px;">
            <label style="color: #92400e; font-weight: bold;">🏢 Vínculo Institucional (Escola)</label>
            <select name="escola_id" class="form-control" required style="border-color: #f59e0b">
                <option value="">-- Selecione a Escola --</option>
                <?php foreach ($escolas as $e): ?>
                    <option value="<?= $e->id ?>" <?= (($u && $u->escola_id == $e->id) || (!$u && count($escolas) == 1)) ? 'selected' : '' ?>>
                        <?= e($e->nome) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <small style="color: #b45309">Como Super Admin, você deve selecionar a qual escola este usuário pertencerá.</small>
        </div>
        <hr style="margin: 1.5rem 0; border: 0; border-top: 1px solid #e2e8f0;">
        <?php endif; ?>

        <div class="form-group">
            <label>Nome Completo</label>
            <input type="text" name="nome" class="form-control" value="<?= e($u->nome ?? '') ?>" required placeholder="Ex: Maria Oliveira">
        </div>

        <div class="form-group">
            <label>E-mail (Login)</label>
            <input type="email" name="email" class="form-control" value="<?= e($u->email ?? '') ?>" required placeholder="exemplo@fsmart.com">
        </div>

        <div class="form-group">
            <label>Cargo / Perfil</label>
            <select name="role" class="form-control" required>
                <option value="DIRETOR" <?= ($u && $u->role === 'DIRETOR') ? 'selected' : '' ?>>Gestor(a)</option>
                <option value="VICE" <?= ($u && $u->role === 'VICE') ? 'selected' : '' ?>>Vice Gestor</option>
                <option value="ORIENTADORA" <?= ($u && $u->role === 'ORIENTADORA') ? 'selected' : '' ?>>Orientador(a)</option>
                <option value="ASSISTENTE" <?= (($u && $u->role === 'ASSISTENTE') || !$u) ? 'selected' : '' ?>>Assistente</option>
            </select>
        </div>

        <div class="form-group">
            <label><?= $id ? 'Nova Senha (deixe em branco para manter)' : 'Senha de Acesso' ?></label>
            <input type="password" name="password" class="form-control" <?= $id ? '' : 'required' ?>>
        </div>

        <div class="form-group">
            <label>Status</label>
            <select name="ativo" class="form-control">
                <option value="1" <?= (!$u || $u->ativo) ? 'selected' : '' ?>>Ativo</option>
                <option value="0" <?= ($u && !$u->ativo) ? 'selected' : '' ?>>Inativo</option>
            </select>
        </div>

        <div style="margin-top: 1.5rem; display: flex; gap: 1rem;">
            <button type="submit" class="btn btn-primary">
                <?= $id ? 'Salvar Alterações' : 'Cadastrar Usuário' ?>
            </button>
            <a href="/usuarios" class="btn btn-outline">Cancelar</a>
        </div>
    </form>
</div>

<?php if (tem_role('DIRETOR')): ?>
<div class="alert alert-info" style="max-width: 600px; margin: 1.5rem auto 0 auto; font-size: .8rem;">
    <strong>ℹ️ Nota sobre cargos:</strong>
    <ul style="margin-left: 1.5rem; margin-top: .5rem;">
        <li><strong>Gestor(a)</strong>: Acesso total e gerencia usuários.</li>
        <li><strong>Vice e Orientador</strong>: Acesso total, menos gestão de usuários.</li>
        <li><strong>Assistente</strong>: Acesso apenas para lançamento de frequência.</li>
    </ul>
</div>
<?php endif; ?>

<?php include __DIR__ . '/../layout/footer.php'; ?>
