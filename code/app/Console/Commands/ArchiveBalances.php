<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Services\MovementsService;

class ArchiveBalances extends Command
{
    protected $signature = 'balances:archive {date}';
    protected $description = 'Archivia i saldi ad una certa data';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $date = $this->argument('date');
        $service = new MovementsService();
        $service->closeBalance(['date' => $date]);
    }
}
