<?php

/*
    Questo comando viene usato per aggiornare i database delle istanze in
    produzione per eventuali modifiche allo schema.
    Il suo contenuto cambia nel tempo, man mano che avvengono gli aggiornamenti.
*/

namespace App\Console\Commands;

use Illuminate\Console\Command;

use DB;
use Artisan;

use App\Gas;
use App\MovementType;
use App\Currency;
use App\Balance;
use App\Movement;
use App\User;
use App\Supplier;

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

        if (Currency::where('symbol', '€')->first() == null) {
            $c = new Currency();
            $c->symbol = '€';
            $c->context = 'default';
            $c->enabled = true;
            $c->save();

            Balance::whereNull('currency_id')->update(['currency_id' => $c->id]);
            Movement::whereNull('currency_id')->update(['currency_id' => $c->id]);
        }
        else {
            $c = defaultCurrency();
            Movement::whereNull('currency_id')->update(['currency_id' => $c->id]);
            Balance::whereNull('currency_id')->update(['currency_id' => $c->id]);

            foreach(Gas::all() as $target) {
                $balances = $target->balances()->where('currency_id', $c->id)->where('current', true)->orderBy('date', 'desc')->get();
                if ($balances->count() == 2) {
                    $balances->first()->delete();
                }
            }

            foreach(User::all() as $target) {
                $balances = $target->balances()->where('currency_id', $c->id)->where('current', true)->orderBy('date', 'desc')->get();
                if ($balances->count() == 2) {
                    $balances->first()->delete();
                }
            }

            foreach(Supplier::all() as $target) {
                $balances = $target->balances()->where('currency_id', $c->id)->where('current', true)->orderBy('date', 'desc')->get();
                if ($balances->count() == 2) {
                    $balances->first()->delete();
                }
            }
        }

        $gas = Gas::all();

        foreach(systemParameters('MailTypes') as $identifier => $metadata) {
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
