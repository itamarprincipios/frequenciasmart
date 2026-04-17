<?php
// services/AlertaService.php — Lógica de alertas em PHP puro (sem Laravel)

class AlertaService
{
    /**
     * Ponto de entrada: verifica as duas regras para um aluno
     */
    public function verificar(int $alunoId): void
    {
        $aluno = db_one("SELECT escola_id FROM alunos WHERE id = ?", [$alunoId]);
        if (!$aluno) return;
        
        $escolaId = (int)$aluno->escola_id;

        $this->verificarFaltasConsecutivas($alunoId, $escolaId);
        $this->verificarFaltasMensais($alunoId, $escolaId);
    }

    /**
     * Regra 1: 3 ou mais faltas consecutivas
     */
    public function verificarFaltasConsecutivas(int $alunoId, int $escolaId): void
    {
        $frequencias = db_all(
            "SELECT status FROM frequencias WHERE aluno_id = ? AND escola_id = ? ORDER BY data DESC",
            [$alunoId, $escolaId]
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
            $this->criarAlertaSeNaoExistir($alunoId, 'CONSECUTIVA', $escolaId);
        }
    }

    /**
     * Regra 2: 10 ou mais faltas no mês atual
     */
    public function verificarFaltasMensais(int $alunoId, int $escolaId): void
    {
        $mes = date('Y-m');

        $row = db_one(
            "SELECT COUNT(*) AS total FROM frequencias
             WHERE aluno_id = ? AND escola_id = ? AND status = 'FALTA' AND DATE_FORMAT(data,'%Y-%m') = ?",
            [$alunoId, $escolaId, $mes]
        );

        if ($row && $row->total >= 10) {
            $this->criarAlertaSeNaoExistir($alunoId, 'INTERCALADA', $escolaId);
        }
    }

    /**
     * Cria o alerta e dispara notificações se ainda não existir no mês
     */
    private function criarAlertaSeNaoExistir(int $alunoId, string $tipo, int $escolaId): void
    {
        $mes = date('Y-m');

        $jaExiste = db_one(
            "SELECT id FROM alertas WHERE aluno_id = ? AND tipo = ? AND mes_referencia = ? AND escola_id = ?",
            [$alunoId, $tipo, $mes, $escolaId]
        );

        if ($jaExiste) return;

        $alertaId = db_insert(
            "INSERT INTO alertas (aluno_id, escola_id, tipo, mes_referencia, enviado, created_at, updated_at)
             VALUES (?, ?, ?, ?, 0, NOW(), NOW())",
            [$alunoId, $escolaId, $tipo, $mes]
        );

        $this->dispararNotificacoes($alertaId, $alunoId, $tipo, $escolaId);

        db_run("UPDATE alertas SET enviado = 1, updated_at = NOW() WHERE id = ? AND escola_id = ?", [$alertaId, $escolaId]);
    }

    /**
     * Envia notificações para ORIENTADORA, DIRETOR e VICE
     */
    private function dispararNotificacoes(int $alertaId, int $alunoId, string $tipo, int $escolaId): void
    {
        $aluno = db_one("SELECT nome FROM alunos WHERE id = ? AND escola_id = ?", [$alunoId, $escolaId]);
        if (!$aluno) return;

        $tipoLabel = $tipo === 'CONSECUTIVA' ? '3 faltas consecutivas' : '10 faltas no mês';
        $titulo    = 'Alerta de Frequência';
        $mensagem  = "O aluno {$aluno->nome} atingiu o limite de faltas ({$tipoLabel}).";

        $usuarios = db_all(
            "SELECT id FROM users WHERE role IN ('ORIENTADORA','DIRETOR','VICE') AND escola_id = ? AND ativo = 1",
            [$escolaId]
        );

        foreach ($usuarios as $usuario) {
            db_insert(
                "INSERT INTO notificacoes (usuario_id, escola_id, titulo, mensagem, lida, created_at, updated_at)
                 VALUES (?, ?, ?, ?, 0, NOW(), NOW())",
                [$usuario->id, $escolaId, $titulo, $mensagem]
            );
        }
    }
}
