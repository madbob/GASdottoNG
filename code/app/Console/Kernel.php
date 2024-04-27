<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        // dummy
    ];

    protected function schedule(Schedule $schedule)
    {
        /*
            Le istanze su gasdotto.net vengono gestite con l'apposito script
            cron_daily.sh per evitare sovrapposizioni sugli orari di esecuzione.
            Se si fa qualche modifica nell'elenco di questi comandi, apportare
            le stesse modifiche nel suddetto script
        */
        if (env('GASDOTTO_NET', false) == false) {
            $schedule->command('check:fees')->daily();
            $schedule->command('close:orders')->daily();
            $schedule->command('open:orders')->daily();
            $schedule->command('remind:orders')->daily();
        }
    }

    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');
        require base_path('routes/console.php');
        $this->load(__DIR__ . '/Commands');
    }
}
