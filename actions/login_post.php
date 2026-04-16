<?php
// actions/login_post.php
verificar_csrf();

$email    = trim($_POST['email']    ?? '');
$password = trim($_POST['password'] ?? '');

if (!$email || !$password) {
    flash('error', 'Preencha todos os campos.');
    redirect('/login');
}

$user = db_one(
    "SELECT * FROM users WHERE email = ? AND ativo = 1",
    [$email]
);

if (!$user || !password_verify($password, $user->password)) {
    salvar_old(['email']);
    flash('error', 'Email ou senha incorretos.');
    redirect('/login');
}

// Regenera ID da sessão por segurança
session_regenerate_id(true);

$_SESSION['usuario'] = [
    'id'    => $user->id,
    'nome'  => $user->nome,
    'email' => $user->email,
    'role'  => $user->role,
];

// Redireciona por role
if ($user->role === 'ORIENTADORA') {
    redirect('/orientadora');
} else {
    redirect('/dashboard');
}
