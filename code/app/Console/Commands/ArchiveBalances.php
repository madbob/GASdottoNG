<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ArchiveBalances extends Command
{
    protected $signature = 'balances:archive {date}';

    protected $description = 'Archivia i saldi ad una certa data';

    public function handle()
    {
        $date = $this->argument('date');
        app()->make('MovementsService')->closeBalance(['date' => $date]);
    }
}
