<?php
// pages/relatorios.php — Interface de seleção para relatórios pedagógicos
requer_login();
requer_role('DIRETOR', 'VICE');

$turmas = db_all("SELECT * FROM turmas WHERE escola_id = ? AND ativa = 1 ORDER BY nome", [escola_id()]);

$tituloPagina = 'Relatórios Pedagógicos';
include __DIR__ . '/../layout/header.php';
?>

<div class="table-wrap" style="max-width: 600px; margin: 0 auto;">
    <div class="table-head">
        <h3>📊 Gerador de Relatório Mensal Consolidated</h3>
    </div>
    <div style="padding: 2rem;">
        <p style="color: #64748b; font-size: 0.9rem; margin-bottom: 2rem;">
            Selecione o mês e opcionalmente uma turma para gerar o documento consolidado de frequência. 
            Este documento está pronto para ser impresso ou salvo como PDF.
        </p>

        <form method="GET" action="/relatorios/imprimir" target="_blank">
            <div class="form-group">
                <label>Mês de Referência</label>
                <input type="month" name="mes" value="<?= date('Y-m') ?>" class="form-control" required>
            </div>

            <div class="form-group">
                <label>Filtrar por Turma (Opcional)</label>
                <select name="turma_id" class="form-control">
                    <option value="">-- Toda a Escola --</option>
                    <?php foreach ($turmas as $t): ?>
                        <option value="<?= e($t->id) ?>"><?= e($t->nome) ?> (<?= e($t->turno) ?>)</option>
                    <?php endforeach; ?>
                </select>
                <div style="font-size: 0.75rem; color: #94a3b8; margin-top: 0.25rem;">
                    Se vazio, o relatório trará estatísticas de todas as turmas ativas.
                </div>
            </div>

            <div style="margin-top: 2.5rem;">
                <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 0.8rem;">
                    🖨️ Gerar Relatório Para Impressão
                </button>
            </div>
        </form>
    </div>
</div>

<div class="cards" style="max-width: 600px; margin: 1.5rem auto 0;">
    <div class="card" style="border-left-color: var(--warning);">
        <div class="card-label">Dica de Uso</div>
        <div class="card-sub">
            Utilize este relatório nas reuniões de conselho de classe ou para envio periódico às secretarias de educação. 
            Ele agrupa automaticamente os alunos em situação crítica de infrequência.
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
