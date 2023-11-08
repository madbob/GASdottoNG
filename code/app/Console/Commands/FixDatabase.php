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

use App\Config;
use App\User;
use App\Supplier;
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

        foreach(Supplier::all() as $supplier) {
            $attachments = $supplier->attachments;

            $pdf = $attachments->firstWhere('name', 'Listino PDF (autogenerato)');
            if ($pdf) {
                $pdf->url = route('suppliers.catalogue', ['id' => $supplier->id, 'format' => 'pdf']);
                $pdf->save();
            }

            $csv = $attachments->firstWhere('name', 'Listino CSV (autogenerato)');
            if ($csv) {
                $csv->url = route('suppliers.catalogue', ['id' => $supplier->id, 'format' => 'csv']);
                $csv->save();
            }
        }

        User::query()->update(['tour' => true]);

        foreach(Gas::all() as $gas) {
            $registrations_info = $gas->public_registrations;
            if (isset($registrations_info['manual']) == false) {
                $registrations_info['manual'] = false;
                $gas->setConfig('public_registrations', $registrations_info);
            }
        }
    }
}
