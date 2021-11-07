<?php

/*
    Questo comando viene usato per aggiornare i database delle istanze in
    produzione per eventuali modifiche allo schema.
    Il suo contenuto cambia nel tempo, man mano che avvengono gli aggiornamenti.
*/

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Artisan;

use App\MovementType;

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

        $movements = MovementType::where('function', 'like', '%suppliers%')->get();
        foreach($movements as $movement) {
            $functions = json_decode($movement->function);
            $new_functions = [];

            foreach($functions as $function) {
                foreach(['sender', 'target', 'master'] as $target) {
                    $new_operations = [];
                    $operations = $function->$target ?? (object) ['operations' => []];

                    foreach($operations->operations as $operation) {
                        if ($operation->field != 'suppliers') {
                            $new_operations[] = $operation;
                        }
                    }

                    $function->$target->operations = $new_operations;
                }

                $new_functions[] = $function;
            }

            $movement->function = json_encode($new_functions);
            $movement->save();
        }
    }
}
