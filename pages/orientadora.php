<?php
// pages/orientadora.php — Painel de alertas com rastreabilidade da Busca Ativa Escolar
requer_login();

$mes     = $_GET['mes']      ?? date('Y-m');
$turmaId = $_GET['turma_id'] ?? null;

$params = [escola_id(), $mes];
$where  = "WHERE al.escola_id = ? AND al.mes_referencia = ?";
if ($turmaId) {
    $where  .= " AND a.turma_id = ?";
    $params[] = $turmaId;
}

$alertas = db_all(
    "SELECT al.*,
            a.nome AS aluno_nome, a.matricula,
            a.responsavel_nome, a.responsavel_telefone, a.data_nascimento,
            t.nome AS turma_nome,
            u.nome AS intervencao_usuario_nome
     FROM alertas al
     JOIN alunos a ON a.id = al.aluno_id AND a.ativo = 1
     JOIN turmas t ON t.id = a.turma_id AND t.ativa = 1
     LEFT JOIN users u ON u.id = al.intervencao_usuario_id
     $where
     ORDER BY al.created_at DESC",
    $params
);

$turmas = db_all("SELECT * FROM turmas WHERE escola_id = ? AND ativa = 1 ORDER BY nome", [escola_id()]);

// Contadores
$totalAlertas        = count($alertas);
$totalConsec         = count(array_filter($alertas, fn($a) => $a->tipo === 'CONSECUTIVA'));
$totalIntercalada    = count(array_filter($alertas, fn($a) => $a->tipo === 'INTERCALADA'));
$totalSemIntervencao = count(array_filter($alertas, fn($a) => empty($a->intervencao_descricao)));
$totalEncaminhados   = count(array_filter($alertas, fn($a) => !empty($a->conselho_tutelar_protocolo)));

$tituloPagina = 'Alertas de Frequência — Busca Ativa Escolar';
include __DIR__ . '/../layout/header.php';
?>

<!-- FILTROS -->
<div class="table-wrap" style="margin-bottom:1.5rem">
    <div class="table-head"><h3>🔍 Filtros</h3></div>
    <div style="padding:1rem 1.25rem">
        <form method="GET" action="/orientadora" style="display:flex;gap:1rem;flex-wrap:wrap;align-items:flex-end">
            <div class="form-group" style="margin:0;flex:1;min-width:160px">
                <label>Mês</label>
                <input type="month" name="mes" value="<?= e($mes) ?>" class="form-control">
            </div>
            <div class="form-group" style="margin:0;flex:1;min-width:160px">
                <label>Turma</label>
                <select name="turma_id" class="form-control">
                    <option value="">Todas</option>
                    <?php foreach ($turmas as $t): ?>
                    <option value="<?= e($t->id) ?>" <?= $turmaId == $t->id ? 'selected' : '' ?>><?= e($t->nome) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Filtrar</button>
            <a href="/orientadora" class="btn btn-outline">Limpar</a>
        </form>
    </div>
</div>

<!-- CARDS RESUMO -->
<div class="cards" style="margin-bottom:1.5rem">
    <div class="card red">
        <div class="card-label">Alertas no período</div>
        <div class="card-value"><?= e($totalAlertas) ?></div>
    </div>
    <div class="card yellow">
        <div class="card-label">3 Faltas consecutivas</div>
        <div class="card-value"><?= e($totalConsec) ?></div>
    </div>
    <div class="card">
        <div class="card-label">8+ Faltas mensais</div>
        <div class="card-value"><?= e($totalIntercalada) ?></div>
    </div>
    <div class="card" style="border-color:<?= $totalSemIntervencao > 0 ? '#ef4444' : '#10b981' ?>">
        <div class="card-label">Aguardando intervenção</div>
        <div class="card-value" style="color:<?= $totalSemIntervencao > 0 ? '#ef4444' : '#10b981' ?>"><?= e($totalSemIntervencao) ?></div>
    </div>
    <div class="card green">
        <div class="card-label">Encaminhados ao CT</div>
        <div class="card-value"><?= e($totalEncaminhados) ?></div>
    </div>
</div>

<?php if ($totalSemIntervencao > 0): ?>
<div class="alert alert-error" style="margin-bottom:1.5rem;border-left:4px solid #ef4444">
    ⚠️ <strong><?= $totalSemIntervencao ?> alerta(s) sem intervenção registrada.</strong>
    Conforme o Projeto de Implementação, 100% dos alertas devem ter intervenção documentada. Registre a ação tomada para cada caso abaixo.
</div>
<?php endif; ?>

<!-- TABELA ALERTAS -->
<div class="table-wrap">
    <div class="table-head">
        <h3>🔔 Alertas — Busca Ativa Escolar</h3>
        <span style="font-size:.8rem;color:#64748b"><?= e($totalAlertas) ?> resultado(s)</span>
    </div>
    <table>
        <thead>
            <tr>
                <th>Aluno / Responsável</th>
                <th>Turma</th>
                <th>Tipo de Alerta</th>
                <th>Mês</th>
                <th style="text-align:center">Intervenção</th>
                <th style="text-align:center">CT</th>
                <th style="text-align:right">Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($alertas)): ?>
            <tr><td colspan="7" style="text-align:center;color:#94a3b8;padding:2rem">✅ Nenhum alerta para este período</td></tr>
            <?php else: foreach ($alertas as $alerta): ?>
            <tr style="<?= empty($alerta->intervencao_descricao) ? 'background:#fff5f5' : '' ?>">
                <td>
                    <strong><?= e($alerta->aluno_nome ?? '—') ?></strong>
                    <div style="font-size:.72rem;color:#64748b"><?= e($alerta->matricula ?? '') ?></div>
                    <?php if ($alerta->responsavel_nome): ?>
                    <div style="font-size:.72rem;color:#94a3b8">Resp: <?= e($alerta->responsavel_nome) ?>
                        <?php if ($alerta->responsavel_telefone): ?> — <?= e($alerta->responsavel_telefone) ?><?php endif; ?>
                    </div>
                    <?php endif; ?>
                </td>
                <td><?= e($alerta->turma_nome ?? '—') ?></td>
                <td>
                    <?php if ($alerta->tipo === 'CONSECUTIVA'): ?>
                        <span class="badge badge-red">⚠️ 3 Consecutivas</span>
                    <?php else: ?>
                        <span class="badge badge-yellow">📊 8 Mensais</span>
                    <?php endif; ?>
                </td>
                <td><?= e($alerta->mes_referencia) ?></td>
                <td style="text-align:center">
                    <?php if (!empty($alerta->intervencao_descricao)): ?>
                        <span class="badge badge-green" title="<?= e($alerta->intervencao_descricao) ?>">
                            ✅ <?= e($alerta->intervencao_usuario_nome ?? 'Registrado') ?><br>
                            <small style="font-size:.65rem"><?= $alerta->intervencao_data ? date('d/m/Y', strtotime($alerta->intervencao_data)) : '' ?></small>
                        </span>
                    <?php else: ?>
                        <span class="badge badge-red">❌ Pendente</span>
                    <?php endif; ?>
                </td>
                <td style="text-align:center">
                    <?php if (!empty($alerta->conselho_tutelar_protocolo)): ?>
                        <span class="badge badge-blue" title="Protocolo: <?= e($alerta->conselho_tutelar_protocolo) ?>">
                            📋 <?= e($alerta->conselho_tutelar_protocolo) ?>
                        </span>
                    <?php else: ?>
                        <span style="font-size:.72rem;color:#94a3b8">—</span>
                    <?php endif; ?>
                </td>
                <td style="text-align:right">
                    <div style="display:flex;gap:.3rem;justify-content:flex-end;flex-wrap:wrap">
                        <a href="/alertas/<?= e($alerta->id) ?>/imprimir" target="_blank"
                           class="btn btn-outline" style="padding:.3rem .5rem;font-size:.7rem">
                            📄 Notificação
                        </a>
                        <button onclick="abrirIntervencao(<?= $alerta->id ?>, '<?= e(addslashes($alerta->aluno_nome)) ?>')"
                                class="btn <?= empty($alerta->intervencao_descricao) ? 'btn-primary' : 'btn-outline' ?>"
                                style="padding:.3rem .5rem;font-size:.7rem">
                            <?= empty($alerta->intervencao_descricao) ? '✍️ Registrar' : '✏️ Atualizar' ?>
                        </button>
                    </div>
                </td>
            </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

<!-- MODAL DE INTERVENÇÃO -->
<div id="modalIntervencao" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:1000;align-items:center;justify-content:center">
    <div style="background:#fff;border-radius:12px;padding:2rem;width:100%;max-width:520px;margin:1rem">
        <h3 id="modalTitulo" style="margin:0 0 1rem">Registrar Intervenção</h3>
        <form id="formIntervencao" method="POST">
            <?php csrf_field(); ?>
            <div class="form-group">
                <label>Descreva a intervenção realizada *</label>
                <textarea name="intervencao_descricao" class="form-control" rows="4" required
                    placeholder="Ex: Família contactada por telefone em 28/06/2025. Responsável Maria informou que aluno estava doente com gripe. Solicitada atestado médico."></textarea>
                <small style="color:#64748b;font-size:.75rem">Esta descrição é o registro oficial da Busca Ativa Escolar para fins de auditoria e conformidade com o ECA.</small>
            </div>
            <div style="background:#fef3c7;border:1px solid #fde68a;border-radius:8px;padding:.85rem;margin-bottom:1rem;font-size:.8rem;color:#92400e">
                <strong>📋 Encaminhamento ao Conselho Tutelar</strong><br>
                Preencha apenas se o caso já foi formalmente encaminhado ao CT (LDB art. 12, VIII — 30%+ de faltas).
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;margin-bottom:1rem">
                <div class="form-group" style="margin:0">
                    <label>Nº Protocolo do CT</label>
                    <input type="text" name="conselho_tutelar_protocolo" class="form-control"
                           placeholder="Ex: CT/2025/001">
                </div>
                <div class="form-group" style="margin:0">
                    <label>Data do Encaminhamento</label>
                    <input type="date" name="conselho_tutelar_data" class="form-control">
                </div>
            </div>
            <div style="display:flex;gap:.75rem">
                <button type="submit" class="btn btn-primary">💾 Salvar Intervenção</button>
                <button type="button" onclick="fecharModal()" class="btn btn-outline">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<script>
function abrirIntervencao(alertaId, nomeAluno) {
    document.getElementById('modalTitulo').textContent = '✍️ Intervenção — ' + nomeAluno;
    document.getElementById('formIntervencao').action = '/alertas/' + alertaId + '/intervencao';
    const modal = document.getElementById('modalIntervencao');
    modal.style.display = 'flex';
}
function fecharModal() {
    document.getElementById('modalIntervencao').style.display = 'none';
}
document.getElementById('modalIntervencao').addEventListener('click', function(e) {
    if (e.target === this) fecharModal();
});
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>
