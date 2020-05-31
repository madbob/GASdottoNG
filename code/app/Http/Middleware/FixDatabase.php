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
