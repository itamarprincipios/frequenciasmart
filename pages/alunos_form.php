<?php
// pages/alunos_form.php — Criar e Editar aluno
requer_login();

$editando = isset($id); // $id vem do roteador quando é edição
$aluno    = null;

if ($editando) {
    $aluno = db_one(
        "SELECT a.*, t.nome AS turma_nome 
         FROM alunos a 
         LEFT JOIN turmas t ON t.id=a.turma_id 
         WHERE a.id = ? AND a.escola_id = ?", 
        [$id, escola_id()]
    );
    if (!$aluno) {
        http_response_code(404);
        die('<p style="font-family:monospace;padding:2rem">Aluno não encontrado ou sem permissão.</p>');
    }
}

$turmas = db_all("SELECT * FROM turmas WHERE escola_id = ? AND ativa = 1 ORDER BY nome", [escola_id()]);
$erros  = $_SESSION['erros'] ?? [];
unset($_SESSION['erros']);

$tituloPagina = $editando ? 'Editar Aluno' : 'Novo Aluno';
include __DIR__ . '/../layout/header.php';
?>

<div style="max-width:560px">
    <div class="table-wrap">
        <div class="table-head">
            <h3><?= $editando ? '✏️ Editar Aluno' : '➕ Cadastrar Novo Aluno' ?></h3>
            <a href="/alunos" class="btn btn-outline" style="font-size:.8rem">← Voltar</a>
        </div>
        <div style="padding:1.5rem 1.25rem">

            <?php if (!empty($erros)): ?>
            <div class="alert alert-error" style="margin-bottom:1rem">
                <ul style="margin:0;padding-left:1.25rem">
                    <?php foreach ($erros as $e): ?><li><?= e($e) ?></li><?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <form method="POST" action="<?= $editando ? '/alunos/' . e($aluno->id) : '/alunos' ?>">
                <?php csrf_field(); ?>

                <div class="form-group">
                    <label for="nome">Nome completo *</label>
                    <input type="text" id="nome" name="nome"
                           value="<?= old('nome', $aluno->nome ?? '') ?>"
                           class="form-control" placeholder="Ex: João da Silva" required autofocus>
                </div>

                <div class="form-group">
                    <label for="matricula">Matrícula *</label>
                    <input type="text" id="matricula" name="matricula"
                           value="<?= old('matricula', $aluno->matricula ?? '') ?>"
                           class="form-control" placeholder="Ex: 2026001" required>
                    <small style="color:#94a3b8;font-size:.75rem">Deve ser única no sistema</small>
                </div>

                <div class="form-group">
                    <label for="turma_id">Turma *</label>
                    <select id="turma_id" name="turma_id" class="form-control" required>
                        <option value="">Selecione a turma...</option>
                        <?php foreach ($turmas as $t): ?>
                        <option value="<?= e($t->id) ?>"
                            <?= (old('turma_id', $aluno->turma_id ?? '') == $t->id) ? 'selected' : '' ?>>
                            <?= e($t->nome) ?> – <?= e($t->turno) ?> (<?= e($t->ano_letivo) ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <?php if ($editando && $aluno): ?>
                <!-- QR Code atual -->
                <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:1rem;margin-bottom:1rem;display:flex;align-items:center;gap:1rem">
                    <img src="<?= e(qr_url(json_encode(['aluno_id'=>$aluno->id,'qr_token'=>$aluno->qr_token]), 80)) ?>"
                         alt="QR Code" style="width:80px;height:80px;border-radius:6px">
                    <div>
                        <div style="font-size:.8rem;font-weight:600;color:#374151">QR Code atual</div>
                        <div style="font-family:monospace;font-size:.7rem;color:#94a3b8;word-break:break-all"><?= e($aluno->qr_token) ?></div>
                        <a href="/alunos/<?= e($aluno->id) ?>/qrcode" target="_blank"
                           style="font-size:.75rem;color:#4f46e5;text-decoration:none">🖨️ Imprimir</a>
                    </div>
                </div>
                <?php endif; ?>

                <div style="display:flex;gap:.75rem">
                    <button type="submit" class="btn btn-primary">
                        <?= $editando ? '💾 Salvar alterações' : '✅ Cadastrar aluno' ?>
                    </button>
                    <a href="/alunos" class="btn btn-outline">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php limpar_old(); include __DIR__ . '/../layout/footer.php'; ?>
