<?php
// pages/escolas_form.php — Cadastrar nova escola + diretor
requer_super_admin();

$tituloPagina = 'Nova Escola';
include __DIR__ . '/../layout/header.php';
?>

<style>
.form-section {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 1px 3px rgba(0,0,0,.06);
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    max-width: 680px;
}
.form-section h3 {
    font-size: .95rem;
    font-weight: 600;
    margin-bottom: 1.25rem;
    padding-bottom: .75rem;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    gap: .5rem;
}
.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}
@media (max-width: 600px) { .form-row { grid-template-columns: 1fr; } }
.slug-preview {
    font-size: .75rem;
    color: #64748b;
    margin-top: .3rem;
}
.slug-preview code {
    background: #f1f5f9;
    padding: .1rem .4rem;
    border-radius: 4px;
    color: #4f46e5;
}
</style>

<div style="max-width:680px;">
    <div style="display:flex; align-items:center; gap:1rem; margin-bottom:1.5rem;">
        <a href="/escolas" class="btn btn-outline" style="padding:.4rem .8rem; font-size:.8rem;">← Voltar</a>
        <h2 style="font-size:1.1rem; font-weight:600;">🏢 Cadastrar Nova Escola</h2>
    </div>

    <form method="POST" action="/escolas" id="form-escola">
        <?php csrf_field(); ?>

        <!-- BLOCO 1: DADOS DA ESCOLA -->
        <div class="form-section">
            <h3>🏫 Dados da Escola</h3>

            <div class="form-group">
                <label for="nome_escola">Nome da Escola <span style="color:#ef4444">*</span></label>
                <input
                    type="text"
                    id="nome_escola"
                    name="nome_escola"
                    class="form-control"
                    value="<?= old('nome_escola') ?>"
                    required
                    placeholder="Ex: Escola Municipal João da Silva"
                    oninput="gerarSlug(this.value)"
                >
                <div class="slug-preview" id="slug-preview" style="display:none">
                    Identificador: <code id="slug-value"></code>
                    <input type="hidden" name="slug" id="slug-input">
                </div>
            </div>
        </div>

        <!-- BLOCO 2: DIRETOR -->
        <div class="form-section">
            <h3>👤 Primeiro Gestor (Diretor)</h3>
            <p style="font-size:.8rem; color:#64748b; margin-bottom:1rem;">
                Este usuário será o administrador da escola e poderá criar outros usuários.
            </p>

            <div class="form-group">
                <label for="nome_diretor">Nome Completo <span style="color:#ef4444">*</span></label>
                <input type="text" id="nome_diretor" name="nome_diretor" class="form-control"
                    value="<?= old('nome_diretor') ?>" required placeholder="Ex: Maria Oliveira">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="email_diretor">E-mail de Acesso <span style="color:#ef4444">*</span></label>
                    <input type="email" id="email_diretor" name="email_diretor" class="form-control"
                        value="<?= old('email_diretor') ?>" required placeholder="diretor@escola.com">
                </div>
                <div class="form-group">
                    <label for="senha_diretor">Senha Inicial <span style="color:#ef4444">*</span></label>
                    <input type="password" id="senha_diretor" name="senha_diretor" class="form-control"
                        required placeholder="Mínimo 6 caracteres" minlength="6">
                </div>
            </div>
        </div>

        <!-- AÇÕES -->
        <div style="display:flex; gap:1rem; align-items:center;">
            <button type="submit" class="btn btn-primary" style="padding:.65rem 1.5rem; font-size:.9rem;">
                ✅ Criar Escola e Gestor
            </button>
            <a href="/escolas" class="btn btn-outline">Cancelar</a>
        </div>
    </form>
</div>

<script>
function gerarSlug(nome) {
    const slug = nome
        .toLowerCase()
        .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
        .replace(/[^a-z0-9\s-]/g, '')
        .trim()
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-')
        .substring(0, 60);

    document.getElementById('slug-value').textContent = slug;
    document.getElementById('slug-input').value = slug;
    const preview = document.getElementById('slug-preview');
    preview.style.display = slug ? 'block' : 'none';
}
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>
