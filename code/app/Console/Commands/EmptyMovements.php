<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Movement;
use App\Balance;

class EmptyMovements extends Command
{
    protected $signature = 'empty:movements';

    protected $description = 'Invalida tutti i movimenti contabili';

    private function emptyMovements()
    {
        $movements = Movement::all();
        foreach ($movements as $movement) {
            try {
                $movement->delete();
            }
            catch (\Exception $e) {
                echo 'Errore rimozione movimento ' . $movement->id . "\n";
            }
        }
    }

    private function emptyBalances()
    {
        $balances = Balance::all();
        foreach ($balances as $balance) {
            try {
                $balance->delete();
            }
            catch (\Exception $e) {
                echo 'Errore rimozione bilancio ' . $balance->id . "\n";
            }
        }
    }

    public function handle()
    {
        $this->emptyMovements();
        $this->emptyBalances();
    }
}
