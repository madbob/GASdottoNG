<?php

/*
    Questo comando viene usato per aggiornare i database delle istanze in
    produzione per eventuali modifiche allo schema.
    Il suo contenuto cambia nel tempo, man mano che avvengono gli aggiornamenti.
*/

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

use App\ModifierType;

class FixDatabase extends Command
{
    protected $signature = 'fix:database';
    protected $description = 'Sistema le informazioni sul DB per completare il deploy';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        /*
            I seeder dei tipi di movimento contabile e dei tipi di modificatore
            vengono sempre eseguiti, tanto comunque controllano se ogni tipo già
            esiste prima di ricrearlo
        */
        Artisan::call('db:seed', ['--force' => true, '--class' => 'MovementTypesSeeder']);
        Artisan::call('db:seed', ['--force' => true, '--class' => 'ModifierTypesSeeder']);

        $mod = ModifierType::where('name', 'Sconto')->where('system', true)->where('identifier', '')->first();
        if ($mod) {
            $mod->identifier = 'discount';
            $mod->save();
        }

        $mod = ModifierType::where('name', 'Spese Trasporto')->where('system', true)->where('identifier', '')->first();
        if ($mod) {
            $mod->identifier = 'shipping';
            $mod->save();
        }
    }
}
