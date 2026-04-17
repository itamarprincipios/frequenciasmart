<?php
// pages/turmas.php
requer_role('DIRETOR', 'VICE');

$turmas = db_all(
    "SELECT t.*, COUNT(a.id) AS alunos_count
     FROM turmas t
     LEFT JOIN alunos a ON a.turma_id = t.id AND a.ativo = 1
     WHERE t.ativa = 1
     GROUP BY t.id
     ORDER BY t.nome"
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
            <tr><th>Nome</th><th>Turno</th><th>Ano Letivo</th><th>Alunos</th><th>QR Code</th></tr>
        </thead>
        <tbody>
            <?php if (empty($turmas)): ?>
            <tr><td colspan="5" style="text-align:center;color:#94a3b8;padding:1.5rem">Nenhuma turma cadastrada</td></tr>
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
                <td><?= e($turma->ano_letivo) ?></td>
                <td><span class="badge badge-green"><?= e($turma->alunos_count) ?> alunos</span></td>
                <td>
                    <div style="display:flex;gap:.5rem">
                        <a href="/turmas/<?= e($turma->id) ?>/qrcode" target="_blank" class="btn btn-outline" style="font-size:.75rem" title="Ver QR da Turma">
                            📱 QR
                        </a>
                        <a href="/turmas/<?= e($turma->id) ?>/imprimir" target="_blank" class="btn btn-primary" style="font-size:.75rem;background:#4f46e5" title="Imprimir Etiquetas">
                            🖨️ Etiquetas
                        </a>
                    </div>
                </td>
            </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
