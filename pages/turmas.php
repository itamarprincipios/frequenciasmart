<?php
// pages/turmas.php
requer_role('DIRETOR', 'VICE');

$turmas = db_all(
    "SELECT t.*, COUNT(a.id) AS alunos_count
     FROM turmas t
     LEFT JOIN alunos a ON a.turma_id = t.id AND a.ativo = 1
     WHERE t.escola_id = ? AND t.ativa = 1
     GROUP BY t.id
     ORDER BY t.nome",
    [escola_id()]
);

$tituloPagina = 'Turmas';
include __DIR__ . '/../layout/header.php';
?>

<div class="table-wrap">
    <div class="table-head">
        <h3>🏫 Turmas Ativas</h3>
    </div>
    <table>
        <thead>
            <tr>
                <th style="width: 30%">Nome</th>
                <th style="width: 25%">Turno</th>
                <th style="width: 20%">Alunos</th>
                <th style="text-align:center; width: 25%">Ações / QR Code</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($turmas)): ?>
            <tr><td colspan="4" style="text-align:center;color:#94a3b8;padding:1.5rem">Nenhuma turma cadastrada</td></tr>
            <?php else: foreach ($turmas as $turma): ?>
            <tr>
                <td><strong><?= e($turma->nome) ?></strong></td>
                <td>
                    <?php if ($turma->turno === 'MANHA'): ?>
                        <span class="badge badge-blue">☀️ Manhã</span>
                    <?php elseif ($turma->turno === 'TARDE'): ?>
                        <span class="badge badge-yellow">🌤️ Tarde</span>
                    <?php else: ?>
                        <span class="badge badge-gray">🌙 Noite</span>
                    <?php endif; ?>
                </td>
                <td><span class="badge badge-green"><?= e($turma->alunos_count) ?> alunos</span></td>
                <td>
                    <div style="display:flex;gap:.4rem;justify-content:center;align-items:center;">
                        <a href="/turmas/<?= e($turma->id) ?>/qrcode" target="_blank" class="btn btn-outline" style="font-size:.7rem;padding:.3rem .5rem" title="Ver QR da Turma">
                            📱 QR
                        </a>
                        <a href="/turmas/<?= e($turma->id) ?>/imprimir" target="_blank" class="btn btn-primary" style="font-size:.7rem;padding:.3rem .5rem;background:#4f46e5" title="Imprimir Etiquetas">
                            🖨️ Etiquetas
                        </a>
                        <form method="POST" action="/turmas/<?= e($turma->id) ?>/excluir" style="display:inline"
                               onsubmit="return confirm('Excluir turma <?= e($turma->nome) ?>? Todos os alunos perderão o vínculo.')">
                            <?php csrf_field(); ?>
                            <button type="submit" class="btn btn-danger" style="font-size:.7rem;padding:.3rem .5rem" title="Excluir Turma">🗑️</button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
