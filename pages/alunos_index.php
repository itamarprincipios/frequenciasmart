<?php
// pages/alunos_index.php
requer_login();

$turmaId = $_GET['turma_id'] ?? null;
$busca   = $_GET['busca'] ?? null;

$where  = "WHERE a.ativo = 1";
$params = [];

if ($turmaId) {
    $where  .= " AND a.turma_id = ?";
    $params[] = $turmaId;
}
if ($busca) {
    $where  .= " AND (a.nome LIKE ? OR a.matricula LIKE ?)";
    $params[] = "%$busca%";
    $params[] = "%$busca%";
}

$alunos = db_all(
    "SELECT a.*, t.nome AS turma_nome, t.turno
     FROM alunos a
     LEFT JOIN turmas t ON t.id = a.turma_id
     $where
     ORDER BY a.nome",
    $params
);

$turmas = db_all("SELECT * FROM turmas WHERE ativa = 1 ORDER BY nome");

$tituloPagina = 'Gestão de Alunos';
include __DIR__ . '/../layout/header.php';
?>

<!-- FILTROS -->
<div class="table-wrap" style="margin-bottom:1.5rem">
    <div class="table-head"><h3>🔍 Filtros</h3></div>
    <div style="padding:1rem 1.25rem">
        <form method="GET" action="/alunos" style="display:flex;gap:1rem;flex-wrap:wrap;align-items:flex-end">
            <div class="form-group" style="margin:0;flex:2;min-width:200px">
                <label>Buscar aluno</label>
                <input type="text" name="busca" value="<?= e($busca) ?>" class="form-control" placeholder="Nome ou matrícula...">
            </div>
            <div class="form-group" style="margin:0;flex:1;min-width:180px">
                <label>Turma</label>
                <select name="turma_id" class="form-control">
                    <option value="">Todas as turmas</option>
                    <?php foreach ($turmas as $t): ?>
                    <option value="<?= e($t->id) ?>" <?= $turmaId == $t->id ? 'selected' : '' ?>><?= e($t->nome) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Filtrar</button>
            <a href="/alunos" class="btn btn-outline">Limpar</a>
        </form>
    </div>
</div>

<!-- TABELA -->
<div class="table-wrap">
    <div class="table-head">
        <h3>🎓 Alunos (<?= e(count($alunos)) ?>)</h3>
        <a href="/alunos/criar" class="btn btn-primary">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4"/></svg>
            Novo Aluno
        </a>
    </div>
    <table>
        <thead>
            <tr>
                <th>Nome</th>
                <th>Matrícula</th>
                <th>Turma</th>
                <th>QR Token</th>
                <th style="text-align:center">Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($alunos)): ?>
            <tr>
                <td colspan="5" style="text-align:center;color:#94a3b8;padding:2rem">
                    Nenhum aluno encontrado. <a href="/alunos/criar" style="color:#4f46e5">+ Cadastrar primeiro aluno</a>
                </td>
            </tr>
            <?php else: foreach ($alunos as $aluno): ?>
            <tr>
                <td><strong><?= e($aluno->nome) ?></strong></td>
                <td><span style="font-family:monospace;font-size:.8rem;color:#475569"><?= e($aluno->matricula) ?></span></td>
                <td>
                    <?php if ($aluno->turma_nome): ?>
                        <span class="badge badge-blue"><?= e($aluno->turma_nome) ?></span>
                        <span style="font-size:.7rem;color:#94a3b8;margin-left:.3rem"><?= e($aluno->turno) ?></span>
                    <?php else: ?>
                        <span class="badge badge-gray">Sem turma</span>
                    <?php endif; ?>
                </td>
                <td><span style="font-family:monospace;font-size:.7rem;color:#94a3b8"><?= e(substr($aluno->qr_token, 0, 12)) ?>…</span></td>
                <td>
                    <div style="display:flex;gap:.5rem;justify-content:center;flex-wrap:wrap">
                        <a href="/alunos/<?= e($aluno->id) ?>/qrcode" target="_blank"
                           class="btn btn-outline" style="font-size:.75rem;padding:.35rem .7rem" title="Imprimir QR Code">
                            📱 QR
                        </a>
                        <a href="/alunos/<?= e($aluno->id) ?>/editar"
                           class="btn btn-primary" style="font-size:.75rem;padding:.35rem .7rem" title="Editar aluno">
                            ✏️ Editar
                        </a>
                        <form method="POST" action="/alunos/<?= e($aluno->id) ?>/excluir"
                              onsubmit="return confirm('Excluir <?= e($aluno->nome) ?>?')">
                            <?php csrf_field(); ?>
                            <button type="submit" class="btn btn-danger"
                                    style="font-size:.75rem;padding:.35rem .7rem" title="Excluir">🗑️</button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
