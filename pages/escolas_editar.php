<?php
// pages/escolas_editar.php — Editar dados de uma escola
requer_super_admin();

$escola = db_one("SELECT * FROM escolas WHERE id = ?", [$id]);
if (!$escola) {
    flash('error', 'Escola não encontrada.');
    redirect('/escolas');
}

$tituloPagina = 'Editar Escola';
include __DIR__ . '/../layout/header.php';
?>

<div style="max-width:600px;">
    <div style="display:flex; align-items:center; gap:1rem; margin-bottom:1.5rem;">
        <a href="/escolas" class="btn btn-outline" style="padding:.4rem .8rem; font-size:.8rem;">← Voltar</a>
        <h2 style="font-size:1.1rem; font-weight:600;">📝 Editar Escola</h2>
    </div>

    <div class="table-wrap">
        <div class="table-head"><h3>🏫 <?= e($escola->nome) ?></h3></div>
        <form method="POST" action="/escolas/<?= $escola->id ?>" style="padding:1.5rem;">
            <?php csrf_field(); ?>

            <div class="form-group">
                <label for="nome_escola">Nome da Escola</label>
                <input type="text" id="nome_escola" name="nome_escola" class="form-control"
                    value="<?= e($escola->nome) ?>" required>
            </div>

            <div class="form-group">
                <label>Identificador (Slug)</label>
                <input type="text" class="form-control" value="<?= e($escola->slug) ?>" readonly
                    style="background:#f8fafc; color:#64748b;" title="O slug não pode ser alterado após criação.">
                <small style="color:#94a3b8">O identificador não pode ser alterado após criação.</small>
            </div>

            <div class="form-group">
                <label for="ativa">Status da Escola</label>
                <select id="ativa" name="ativa" class="form-control">
                    <option value="1" <?= $escola->ativa ? 'selected' : '' ?>>✅ Ativa (diretores podem acessar)</option>
                    <option value="0" <?= !$escola->ativa ? 'selected' : '' ?>>🔴 Bloqueada (acesso suspenso)</option>
                </select>
            </div>

            <div style="margin-top:1.5rem; display:flex; gap:1rem;">
                <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                <a href="/escolas" class="btn btn-outline">Cancelar</a>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
