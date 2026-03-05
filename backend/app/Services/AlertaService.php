<?php

namespace App\Services;

use App\Models\Frequencia;
use App\Models\Alerta;
use App\Models\Aluno;
use App\Models\User;
use App\Models\Notificacao;
use Carbon\Carbon;

class AlertaService
{
    /**
     * Ponto de entrada principal: verifica as duas regras para um aluno
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
        $frequencias = Frequencia::where('aluno_id', $alunoId)
            ->orderBy('data', 'desc')
            ->get();

        $consecutivas = 0;
        foreach ($frequencias as $f) {
            if ($f->status === 'FALTA') {
                $consecutivas++;
            } else {
                break; // parou na primeira presença
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
        $mes = now()->format('Y-m');

        $totalFaltas = Frequencia::where('aluno_id', $alunoId)
            ->where('status', 'FALTA')
            ->whereRaw("DATE_FORMAT(data, '%Y-%m') = ?", [$mes])
            ->count();

        if ($totalFaltas >= 10) {
            $this->criarAlertaSeNaoExistir($alunoId, 'INTERCALADA');
        }
    }

    /**
     * Cria o alerta e dispara notificações se ainda não existir no mês
     */
    private function criarAlertaSeNaoExistir(int $alunoId, string $tipo): void
    {
        $mes = now()->format('Y-m');

        $jaExiste = Alerta::where('aluno_id', $alunoId)
            ->where('tipo', $tipo)
            ->where('mes_referencia', $mes)
            ->exists();

        if ($jaExiste) return;

        $alerta = Alerta::create([
            'aluno_id'       => $alunoId,
            'tipo'           => $tipo,
            'mes_referencia' => $mes,
            'enviado'        => false,
        ]);

        $this->dispararNotificacoes($alerta);

        $alerta->update(['enviado' => true]);
    }

    /**
     * Envia notificações para ORIENTADORA, DIRETOR e VICE
     */
    private function dispararNotificacoes(Alerta $alerta): void
    {
        $aluno = Aluno::find($alerta->aluno_id);
        if (!$aluno) return;

        $tipoLabel = $alerta->tipo === 'CONSECUTIVA'
            ? '3 faltas consecutivas'
            : '10 faltas no mês';

        $titulo   = 'Alerta de Frequência';
        $mensagem = "O aluno {$aluno->nome} atingiu o limite de faltas ({$tipoLabel}).";

        // Busca usuários que devem receber a notificação
        $usuarios = User::whereIn('role', ['ORIENTADORA', 'DIRETOR', 'VICE'])
                        ->where('ativo', true)
                        ->get();

        foreach ($usuarios as $usuario) {
            Notificacao::create([
                'usuario_id' => $usuario->id,
                'titulo'     => $titulo,
                'mensagem'   => $mensagem,
                'lida'       => false,
            ]);

            // TODO: Enviar push Firebase quando a chave estiver configurada
            // $this->enviarPushFirebase($usuario->fcm_token, $titulo, $mensagem);
        }
    }
}
