<?php
// pages/alunos_index.php
requer_login();

$turmaId = $_GET['turma_id'] ?? null;
$busca   = $_GET['busca'] ?? null;

$where  = "WHERE a.escola_id = ? AND a.ativo = 1";
$params = [escola_id()];

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

$turmas = db_all("SELECT * FROM turmas WHERE escola_id = ? AND ativa = 1 ORDER BY nome", [escola_id()]);

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
                <input type="text" name="busca" value="<?= e($busca) ?>" class="form-control" placeholder="Nome ou matricula...">
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
                <th style="width: 40%">Nome</th>
                <th style="width: 25%">Matrícula</th>
                <th style="width: 20%">Turma</th>
                <th style="text-align:center; width: 15%">Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($alunos)): ?>
            <tr>
                <td colspan="4" style="text-align:center;color:#94a3b8;padding:2rem">
                    Nenhum aluno encontrado. <a href="/alunos/criar" style="color:#4f46e5">+ Cadastrar primeiro aluno</a>
                </td>
            </tr>
            <?php else: foreach ($alunos as $aluno): ?>
            <tr>
                <td><strong><?= e($aluno->nome) ?></strong></td>
                <td><span style="font-family:monospace;font-size:.8rem;color:#475569"><?= e($aluno->matricula) ?></span></td>
                <td>
                    <?php if ($aluno->turma_nome): ?>
                        <div style="display:flex; flex-direction:column; gap:.2rem">
                            <span class="badge badge-blue" style="width:fit-content"><?= e($aluno->turma_nome) ?></span>
                            <span style="font-size:.7rem;color:#94a3b8"><?= e($aluno->turno) ?></span>
                        </div>
                    <?php else: ?>
                        <span class="badge badge-gray">Sem turma</span>
                    <?php endif; ?>
                </td>
                <td>
                    <div style="display:flex;gap:.4rem;justify-content:center;align-items:center;">
                        <a href="/alunos/<?= e($aluno->id) ?>/qrcode" target="_blank"
                           class="btn btn-outline" style="font-size:.7rem;padding:.3rem .5rem" title="Imprimir QR Code">
                            📱 QR
                        </a>
                        <a href="/alunos/<?= e($aluno->id) ?>/editar"
                           class="btn btn-primary" style="font-size:.7rem;padding:.3rem .5rem" title="Editar aluno">
                            ✏️ Editar
                        </a>
                        <form method="POST" action="/alunos/<?= e($aluno->id) ?>/excluir"
                               onsubmit="return confirm('Excluir <?= e($aluno->nome) ?>?')">
                            <?php csrf_field(); ?>
                            <button type="submit" class="btn btn-danger"
                                    style="font-size:.7rem;padding:.3rem .5rem" title="Excluir">🗑️</button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
