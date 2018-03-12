<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Movement;
use App\Balance;

class EmptyMovements extends Command
{
    protected $signature = 'empty:movements';
    protected $description = 'Invalida tutti i movimenti contabili';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $movements = Movement::all();
        foreach($movements as $movement) {
            try {
                $movement->delete();
            }
            catch(\Exception $e) {
                echo "Errore rimozione movimento " . $movement->id . "\n";
            }
        }

        $balances = Balance::all();
        foreach($balances as $balance) {
            try {
                $balance->delete();
            }
            catch(\Exception $e) {
                echo "Errore rimozione bilancio " . $balance->id . "\n";
            }
        }
    }
}
