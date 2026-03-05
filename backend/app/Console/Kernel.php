<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Verificação noturna de alertas de frequência — roda todo dia às 23:59
        $schedule->command('edutrack:verificar-alertas')
                 ->dailyAt('23:59')
                 ->withoutOverlapping()
                 ->appendOutputTo(storage_path('logs/alertas-noturno.log'));
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
