<?php
// actions/escolas_store.php — Cria escola + diretor em transação
verificar_csrf();
requer_super_admin();

$nomeEscola   = trim($_POST['nome_escola']   ?? '');
$slug         = trim($_POST['slug']          ?? '');
$nomeDiretor  = trim($_POST['nome_diretor']  ?? '');
$emailDiretor = trim($_POST['email_diretor'] ?? '');
$senhaDiretor = trim($_POST['senha_diretor'] ?? '');

// Validação básica
if (!$nomeEscola || !$slug || !$nomeDiretor || !$emailDiretor || !$senhaDiretor) {
    salvar_old(['nome_escola', 'nome_diretor', 'email_diretor']);
    flash('error', 'Todos os campos são obrigatórios.');
    redirect('/escolas/criar');
}

if (strlen($senhaDiretor) < 6) {
    salvar_old(['nome_escola', 'nome_diretor', 'email_diretor']);
    flash('error', 'A senha deve ter no mínimo 6 caracteres.');
    redirect('/escolas/criar');
}

// Verifica duplicidade
if (db_one("SELECT id FROM escolas WHERE slug = ?", [$slug])) {
    salvar_old(['nome_escola', 'nome_diretor', 'email_diretor']);
    flash('error', 'Já existe uma escola com esse identificador (slug). Tente um nome diferente.');
    redirect('/escolas/criar');
}

if (db_one("SELECT id FROM users WHERE email = ?", [$emailDiretor])) {
    salvar_old(['nome_escola', 'nome_diretor', 'email_diretor']);
    flash('error', 'Este e-mail já está em uso no sistema.');
    redirect('/escolas/criar');
}

$hash = password_hash($senhaDiretor, PASSWORD_DEFAULT);

try {
    pdo()->beginTransaction();

    // 1. Cria a escola
    $escolaId = db_insert(
        "INSERT INTO escolas (nome, slug, ativa) VALUES (?, ?, 1)",
        [$nomeEscola, $slug]
    );

    // 2. Cria o diretor vinculado à escola
    db_insert(
        "INSERT INTO users (nome, email, password, role, ativo, escola_id) VALUES (?, ?, ?, 'DIRETOR', 1, ?)",
        [$nomeDiretor, $emailDiretor, $hash, $escolaId]
    );

    pdo()->commit();

    flash('success', "Escola \"{$nomeEscola}\" criada com sucesso! O gestor {$nomeDiretor} já pode fazer login.");
    redirect('/escolas');

} catch (Exception $e) {
    pdo()->rollBack();
    salvar_old(['nome_escola', 'nome_diretor', 'email_diretor']);
    flash('error', 'Erro ao criar escola. Tente novamente.');
    redirect('/escolas/criar');
}
