<?php

namespace App\Http\Middleware;

use Closure;

use App\Measure;
use App\Category;
use App\MovementType;

/*
    Questo middleware è destinato ad ospitare eventuali correzioni "in corsa" al
    database, per creare o ricreare elementi che per default dovrebbero sempre
    esserci
*/
class FixDatabase
{
    public function handle($request, Closure $next)
    {
        /*
            Qui faccio in modo di avere sempre dei default.
            Serve solo per sistemare le istanze già esistenti in cui questi
            valori sono stato rimossi prima del blocco a livello di
            amministrazione, codice da rimuovere tra qualche tempo
            Addì: 09/01/2018
        */
        if (is_null(Measure::find('non-specificato'))) {
            $measure = new Measure();
            $measure->name = _i('Non Specificato');
            $measure->save();
        }
        if (is_null(Category::find('non-specificato'))) {
            $category = new Category();
            $category->name = _i('Non Specificato');
            $category->save();
        }

        /*
            Questo è per creare il default per i pagamenti PayPal, introdotti
            solo successivamente.
            Addì: 26/04/2018
        */
        $gas = currentAbsoluteGas();
        if(!empty($gas->paypal['client_id'])) {
            $types = MovementType::paymentsByType('user-credit');
            if(!in_array('paypal', array_keys($types))) {
                $type = MovementType::findOrFail('user-credit');

                $data = json_decode($type->function);
                $data[] = (object) [
                    'method' => 'paypal',
                    'sender' => (object) [
                        'operations' => []
                    ],
                    'target' => (object) [
                        'operations' => [
                            (object) [
                                'operation' => 'increment',
                                'field' => 'bank'
                            ],
                        ]
                    ],
                    'master' => (object) [
                        'operations' => [
                            (object) [
                                'operation' => 'increment',
                                'field' => 'paypal'
                            ],
                        ]
                    ]
                ];

                $type->function = json_encode($data);
                $type->save();
            }
        }

        return $next($request);
    }
}
