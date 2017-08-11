<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        \App\Console\Commands\ResetPassword::class,
        \App\Console\Commands\ImportLegacy::class,
        \App\Console\Commands\CheckFees::class,
    ];

    protected function schedule(Schedule $schedule)
    {
    	$schedule->command('check:fees')->daily();
    }

    protected function commands()
    {
        require base_path('routes/console.php');
    }
}
