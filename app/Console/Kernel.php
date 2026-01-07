<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        Commands\DataRetentionCleanupCommand::class,
    ];

    protected function schedule(Schedule $schedule): void
    {
        // Run data retention cleanup daily
        $schedule->command('data-retention:cleanup')->daily();
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
        
        require base_path('routes/console.php');
    }
}