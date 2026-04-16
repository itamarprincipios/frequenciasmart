<?php
// pages/403.php — Acesso negado
http_response_code(403);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>403 – Acesso Negado</title>
<style>
body{font-family:Inter,sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh;background:#f1f5f9;margin:0}
.box{text-align:center;padding:3rem;background:#fff;border-radius:16px;box-shadow:0 4px 24px rgba(0,0,0,.08)}
h1{font-size:4rem;color:#ef4444;margin:0}p{color:#64748b;margin:.5rem 0 1.5rem}a{color:#4f46e5;text-decoration:none;font-weight:600}
</style>
</head>
<body>
<div class="box">
    <h1>403</h1>
    <p>Você não tem permissão para acessar esta página.</p>
    <a href="/dashboard">← Voltar ao início</a>
</div>
</body>
</html>
