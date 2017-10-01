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
        \App\Console\Commands\CloseOrders::class,
        \App\Console\Commands\FixFinalPrices::class,
    ];

    protected function schedule(Schedule $schedule)
    {
    	$schedule->command('check:fees')->daily();
        $schedule->command('check:orders')->daily();
    }

    protected function commands()
    {
        require base_path('routes/console.php');
    }
}
