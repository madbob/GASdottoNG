<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class RecalculateBalances extends Command
{
    protected $signature = 'balances:recalculate';
    protected $description = 'Effettua un ricalcolo saldi';

    public function handle()
    {
        app()->make('MovementsService')->recalculate();
    }
}
