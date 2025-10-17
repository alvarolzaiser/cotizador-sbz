<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        // Aquí registrarás tus comandos personalizados
        \App\Console\Commands\FillNeumaticosTable::class,
        \App\Console\Commands\FillProductosTable::class,
        \App\Console\Commands\UpdateProductosTable::class,
    ];

    protected function schedule(Schedule $schedule)
    {
        // Lógica de scheduling (si aplica)
    }

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}