<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Services\MovementsService;

class RecalculateBalances extends Command
{
    protected $signature = 'recalculate:balances';
    protected $description = 'Effettua un ricalcolo saldi';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $service = new MovementsService();
        $service->recalculate();
    }
}
