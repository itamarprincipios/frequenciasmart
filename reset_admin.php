<?php
// reset_admin.php — Script de reset de senha (USE UMA VEZ E DELETE!)
// Acesse: https://frequenciasmart.cloud/reset_admin.php

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

$novaSenha = 'admin123';
$hash = password_hash($novaSenha, PASSWORD_BCRYPT);

$atualizado = db_run(
    "UPDATE users SET password = ? WHERE email = 'admin@edutrack.com'",
    [$hash]
);

if ($atualizado) {
    echo '<div style="font-family:monospace;padding:2rem;background:#d1fae5;color:#065f46;border-radius:8px;max-width:500px;margin:2rem auto">';
    echo '<h2>✅ Senha resetada com sucesso!</h2>';
    echo '<p>Email: <strong>admin@edutrack.com</strong></p>';
    echo '<p>Senha: <strong>' . $novaSenha . '</strong></p>';
    echo '<p style="margin-top:1rem"><a href="/login" style="color:#065f46">→ Ir para o login</a></p>';
    echo '<p style="margin-top:1rem;font-size:.8rem;color:#6b7280">⚠️ DELETE este arquivo do servidor após usar!</p>';
    echo '</div>';
} else {
    echo '<div style="font-family:monospace;padding:2rem;background:#fee2e2;color:#991b1b">';
    echo 'Erro: usuário não encontrado ou nenhuma alteração feita.';
    echo '</div>';
}
