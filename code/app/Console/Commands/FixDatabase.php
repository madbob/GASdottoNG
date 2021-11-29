<?php

/*
    Questo comando viene usato per aggiornare i database delle istanze in
    produzione per eventuali modifiche allo schema.
    Il suo contenuto cambia nel tempo, man mano che avvengono gli aggiornamenti.
*/

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Artisan;
use DB;

use App\MovementType;
use App\Config;
use App\Gas;

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

        $gas = Gas::all();
        foreach(Config::customMailTypes() as $identifier => $metadata) {
            foreach($gas as $g) {
                $subject = DB::table('configs')->select('value')->where('name', 'mail_' . $identifier . '_subject')->where('gas_id', $g->id)->first();
                if ($subject) {
                    $subject = $subject->value;
                }
                else {
                    continue;
                }

                $body = DB::table('configs')->select('value')->where('name', 'mail_' . $identifier . '_body')->where('gas_id', $g->id)->first();
                if ($body) {
                    $body = $body->value;
                }
                else {
                    continue;
                }

                $data = (object) [
                    'subject' => $subject,
                    'body' => $body,
                ];

                $g->setConfig('mail_' . $identifier, $data);

                DB::table('configs')->where('name', 'mail_' . $identifier . '_subject')->where('gas_id', $g->id)->delete();
                DB::table('configs')->where('name', 'mail_' . $identifier . '_body')->where('gas_id', $g->id)->delete();
            }
        }
    }
}
