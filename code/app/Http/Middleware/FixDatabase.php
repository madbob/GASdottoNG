<?php

namespace App\Http\Middleware;

use Closure;

use App\Measure;
use App\Category;
use App\ModifierType;

/*
    Questo middleware è destinato ad ospitare eventuali correzioni "in corsa" al
    database, per creare o ricreare elementi che per default dovrebbero sempre
    esserci
*/
class FixDatabase
{
    public function handle($request, Closure $next)
    {
        if (ModifierType::all()->isEmpty()) {
            $m = new ModifierType();
            $m->id = 'spese-trasporto';
            $m->name = _i('Spese Trasporto');
            $m->system = true;
            $m->classes = ['App\Product', 'App\Supplier'];
            $m->save();

            $m = new ModifierType();
            $m->id = 'sconto';
            $m->name = _i('Sconto');
            $m->system = true;
            $m->classes = ['App\Product', 'App\Supplier'];
            $m->save();
        }

        $gas = currentAbsoluteGas();

        /*
            Per aggiungere il default al link dei termini d'uso
            31/05/2020
        */
        $public_registrations = $gas->public_registrations;
        if (!isset($public_registrations['terms_link'])) {
            $public_registrations['terms_link'] = '';
            $gas->setConfig('public_registrations', $public_registrations);
        }

        return $next($request);
    }
}
