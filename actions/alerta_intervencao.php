<?php
// actions/alerta_intervencao.php — Registrar intervenção e/ou protocolo do Conselho Tutelar em um alerta
requer_login();
verificar_csrf();
requer_role('DIRETOR', 'VICE', 'ORIENTADORA');

// $id vem do roteador
$alerta = db_one(
    "SELECT * FROM alertas WHERE id = ? AND escola_id = ?",
    [(int)$id, escola_id()]
);

if (!$alerta) {
    flash('error', 'Alerta não encontrado.');
    redirect('/orientadora');
}

$descricao  = trim($_POST['intervencao_descricao'] ?? '');
$protocolo  = trim($_POST['conselho_tutelar_protocolo'] ?? '') ?: null;
$dataProtoc = trim($_POST['conselho_tutelar_data'] ?? '') ?: null;

if (empty($descricao)) {
    flash('error', 'Descreva a intervenção realizada.');
    redirect('/orientadora?mes=' . urlencode($alerta->mes_referencia));
}

db_run(
    "UPDATE alertas SET
        intervencao_descricao   = ?,
        intervencao_data        = NOW(),
        intervencao_usuario_id  = ?,
        conselho_tutelar_protocolo = ?,
        conselho_tutelar_data      = ?,
        updated_at              = NOW()
     WHERE id = ? AND escola_id = ?",
    [
        $descricao,
        usuario_logado()['id'],
        $protocolo,
        $dataProtoc,
        $alerta->id,
        escola_id()
    ]
);

flash('success', 'Intervenção registrada com sucesso! Rastreabilidade da Busca Ativa atualizada.');
redirect('/orientadora?mes=' . urlencode($alerta->mes_referencia));
