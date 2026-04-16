<?php
// services/AlertaService.php — Lógica de alertas em PHP puro (sem Laravel)

class AlertaService
{
    /**
     * Ponto de entrada: verifica as duas regras para um aluno
     */
    public function verificar(int $alunoId): void
    {
        $this->verificarFaltasConsecutivas($alunoId);
        $this->verificarFaltasMensais($alunoId);
    }

    /**
     * Regra 1: 3 ou mais faltas consecutivas
     */
    public function verificarFaltasConsecutivas(int $alunoId): void
    {
        $frequencias = db_all(
            "SELECT status FROM frequencias WHERE aluno_id = ? ORDER BY data DESC",
            [$alunoId]
        );

        $consecutivas = 0;
        foreach ($frequencias as $f) {
            if ($f->status === 'FALTA') {
                $consecutivas++;
            } else {
                break;
            }
        }

        if ($consecutivas >= 3) {
            $this->criarAlertaSeNaoExistir($alunoId, 'CONSECUTIVA');
        }
    }

    /**
     * Regra 2: 10 ou mais faltas no mês atual
     */
    public function verificarFaltasMensais(int $alunoId): void
    {
        $mes = date('Y-m');

        $row = db_one(
            "SELECT COUNT(*) AS total FROM frequencias
             WHERE aluno_id = ? AND status = 'FALTA' AND DATE_FORMAT(data,'%Y-%m') = ?",
            [$alunoId, $mes]
        );

        if ($row && $row->total >= 10) {
            $this->criarAlertaSeNaoExistir($alunoId, 'INTERCALADA');
        }
    }

    /**
     * Cria o alerta e dispara notificações se ainda não existir no mês
     */
    private function criarAlertaSeNaoExistir(int $alunoId, string $tipo): void
    {
        $mes = date('Y-m');

        $jaExiste = db_one(
            "SELECT id FROM alertas WHERE aluno_id = ? AND tipo = ? AND mes_referencia = ?",
            [$alunoId, $tipo, $mes]
        );

        if ($jaExiste) return;

        $alertaId = db_insert(
            "INSERT INTO alertas (aluno_id, tipo, mes_referencia, enviado, created_at, updated_at)
             VALUES (?, ?, ?, 0, NOW(), NOW())",
            [$alunoId, $tipo, $mes]
        );

        $this->dispararNotificacoes($alertaId, $alunoId, $tipo);

        db_run("UPDATE alertas SET enviado = 1, updated_at = NOW() WHERE id = ?", [$alertaId]);
    }

    /**
     * Envia notificações para ORIENTADORA, DIRETOR e VICE
     */
    private function dispararNotificacoes(int $alertaId, int $alunoId, string $tipo): void
    {
        $aluno = db_one("SELECT nome FROM alunos WHERE id = ?", [$alunoId]);
        if (!$aluno) return;

        $tipoLabel = $tipo === 'CONSECUTIVA' ? '3 faltas consecutivas' : '10 faltas no mês';
        $titulo    = 'Alerta de Frequência';
        $mensagem  = "O aluno {$aluno->nome} atingiu o limite de faltas ({$tipoLabel}).";

        $usuarios = db_all(
            "SELECT id FROM users WHERE role IN ('ORIENTADORA','DIRETOR','VICE') AND ativo = 1"
        );

        foreach ($usuarios as $usuario) {
            db_insert(
                "INSERT INTO notificacoes (usuario_id, titulo, mensagem, lida, created_at, updated_at)
                 VALUES (?, ?, ?, 0, NOW(), NOW())",
                [$usuario->id, $titulo, $mensagem]
            );
        }
    }
}
