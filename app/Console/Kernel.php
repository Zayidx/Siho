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
        // Pengecekan prakerin selesai setiap 10 detik
        $schedule->command('prakerin:check-selesai')
                ->everyTenSeconds()
                ->withoutOverlapping()
                ->runInBackground();

        // Trigger manual email penilaian setiap 30 detik (backup)
        $schedule->command('prakerin:trigger-email')
                ->everyThirtySeconds()
                ->withoutOverlapping()
                ->runInBackground();
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