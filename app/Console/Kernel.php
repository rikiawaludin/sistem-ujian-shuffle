<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        Commands\AutoSubmitExpiredExams::class, // Daftarkan command baru
    ];

    protected function schedule(Schedule $schedule)
    {
        $schedule->command('ujian:auto-submit-expired')->everyMinute(); // Jalankan setiap menit
    }

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}