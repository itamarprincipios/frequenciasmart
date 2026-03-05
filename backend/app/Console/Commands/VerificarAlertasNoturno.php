<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Aluno;
use App\Services\AlertaService;

class VerificarAlertasNoturno extends Command
{
    protected $signature   = 'edutrack:verificar-alertas';
    protected $description = 'Verifica alertas de frequência para todos os alunos ativos (roda às 23:59)';

    public function handle(AlertaService $alertaService): int
    {
        $alunos = Aluno::where('ativo', true)->get();

        $this->info("Verificando {$alunos->count()} alunos...");
        $bar = $this->output->createProgressBar($alunos->count());
        $bar->start();

        foreach ($alunos as $aluno) {
            $alertaService->verificar($aluno->id);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('✅ Verificação noturna concluída.');

        return Command::SUCCESS;
    }
}
