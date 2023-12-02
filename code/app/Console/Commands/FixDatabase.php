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

use App\Gas;
use App\User;
use App\Supplier;
use App\ModifierType;
use App\Role;

class FixDatabase extends Command
{
    protected $signature = 'fix:database';
    protected $description = 'Sistema le informazioni sul DB per completare il deploy';

    public function handle()
    {
        /*
            I seeder dei tipi di movimento contabile e dei tipi di modificatore
            vengono sempre eseguiti, tanto comunque controllano se ogni tipo già
            esiste prima di ricrearlo
        */
        Artisan::call('db:seed', ['--force' => true, '--class' => 'MovementTypesSeeder']);
        Artisan::call('db:seed', ['--force' => true, '--class' => 'ModifierTypesSeeder']);

        /*
            Per fixare gli URL dei listini autogenerati ed erroneamente
            sovrascritti
        */
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

        /*
            Per non attivare il tour di onboarding per gli utenti esistenti
        */
        User::query()->update(['tour' => true]);

        /*
            Per impostare un default sull'approvazione manuale delle
            registrazioni pubbliche degli utenti
        */
        foreach(Gas::all() as $gas) {
            $registrations_info = $gas->public_registrations;
            if (isset($registrations_info['manual']) == false) {
                $registrations_info['manual'] = false;
                $gas->setConfig('public_registrations', $registrations_info);
            }
        }

        /*
            Per azzerare le vecchie configurazioni Satispay non compatibili con
            la nuova implementazione
        */
        foreach(Gas::all() as $gas) {
            $satispay_info = $gas->satispay;
            if (isset($satispay_info['public']) == false) {
                $satispay_info = (object) [
                    'public' => '',
                    'secret' => '',
                    'key' => '',
                ];

                $gas->setConfig('satispay', $satispay_info);
            }
        }

        /*
            Per assegnare degli identificativi ai modificatori di default,
            compatibili con l'importazione GDXP
        */

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
