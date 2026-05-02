<?php
// cadastro_professor.php — Página pública para professores se cadastrarem
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';

iniciar_sessao();

// Se já estiver logado, vai para o dashboard
if (está_logado()) {
    redirect('/dashboard');
}

$escolas = db_all("SELECT id, nome FROM escolas WHERE ativa = 1");
$turmas  = db_all("SELECT id, nome, turno, escola_id FROM turmas WHERE ativa = 1 ORDER BY nome");

$tituloPagina = "Cadastro de Professor";
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $tituloPagina ?> - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="/assets/css/style.css"> <!-- Ajuste o caminho se necessário -->
    <style>
        body { font-family: 'Inter', sans-serif; background: #f8fafc; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; }
        .card { background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); width: 100%; max-width: 450px; }
        .header { text-align: center; margin-bottom: 2rem; }
        .header h1 { font-size: 1.5rem; color: #1e293b; margin: 0; }
        .form-group { margin-bottom: 1.2rem; }
        label { display: block; font-size: 0.875rem; color: #64748b; margin-bottom: 0.4rem; }
        .form-control { width: 100%; padding: 0.7rem; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 1rem; box-sizing: border-box; }
        .btn { width: 100%; padding: 0.8rem; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; transition: 0.2s; }
        .btn-primary { background: #4f46e5; color: white; }
        .btn-primary:hover { background: #4338ca; }
        .alert { padding: 1rem; border-radius: 8px; margin-bottom: 1rem; font-size: 0.9rem; }
        .alert-error { background: #fee2e2; color: #991b1b; }
        .alert-success { background: #dcfce7; color: #166534; }
        .integration-box { background: #f0fdf4; border: 1px solid #bbf7d0; padding: 1rem; border-radius: 8px; margin-top: 1rem; }
    </style>
</head>
<body>

<div class="card">
    <div class="header">
        <h1>👨‍🏫 Cadastro de Professor</h1>
        <p style="color:#64748b; font-size:0.9rem;">Crie sua conta para gerenciar sua frequência</p>
    </div>

    <?php include __DIR__ . '/layout/flash.php'; ?>

    <form action="/actions/professor_registrar.php" method="POST">
        <?php csrf_field(); ?>
        
        <div class="form-group">
            <label>Nome Completo</label>
            <input type="text" name="nome" class="form-control" placeholder="Seu nome" required>
        </div>

        <div class="form-group">
            <label>E-mail (Será seu login)</label>
            <input type="email" name="email" class="form-control" placeholder="exemplo@email.com" required>
        </div>

        <div class="form-group">
            <label>Senha de Acesso</label>
            <input type="password" name="password" class="form-control" placeholder="Mínimo 6 caracteres" required>
        </div>

        <div class="form-group">
            <label>Selecione sua Escola</label>
            <select name="escola_id" class="form-control" required id="escola_select">
                <option value="">-- Selecione a Escola --</option>
                <?php foreach ($escolas as $e): ?>
                    <option value="<?= $e->id ?>"><?= e($e->nome) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="integration-box">
            <label style="color:#166534; font-weight:bold;">🔗 Vínculo com Diário Online</label>
            
            <div class="form-group" style="margin-top: 0.5rem;">
                <label>Sua Turma</label>
                <select name="turma_id" class="form-control" required>
                    <option value="">-- Selecione a Turma --</option>
                    <?php foreach ($turmas as $t): ?>
                        <option value="<?= $t->id ?>" class="turma-opt escola-<?= $t->escola_id ?>" style="display:none;">
                            <?= e($t->nome) ?> (<?= e($t->turno) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>ID da Planilha Google (Opcional agora)</label>
                <input type="text" name="spreadsheet_id" class="form-control" placeholder="Pode configurar depois se preferir">
            </div>
        </div>

        <button type="submit" class="btn btn-primary" style="margin-top: 1.5rem;">Criar Minha Conta</button>
        
        <p style="text-align:center; font-size:0.8rem; margin-top:1.5rem;">
            Já tem conta? <a href="/login" style="color:#4f46e5; text-decoration:none; font-weight:600;">Faça Login</a>
        </p>
    </form>
</div>

<script>
    // Filtro de turmas por escola
    document.getElementById('escola_select').addEventListener('change', function() {
        const escolaId = this.value;
        const options = document.querySelectorAll('.turma-opt');
        options.forEach(opt => {
            if (opt.classList.contains('escola-' + escolaId)) {
                opt.style.display = 'block';
            } else {
                opt.style.display = 'none';
            }
        });
    });
</script>

</body>
</html>
