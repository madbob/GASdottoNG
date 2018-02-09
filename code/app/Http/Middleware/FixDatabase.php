<?php

namespace App\Http\Middleware;

use Closure;

use App\Measure;
use App\Category;

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
        if (Measure::find('non-specificato') == null) {
            $measure = new Measure();
            $measure->name = _i('Non Specificato');
            $measure->save();
        }
        if (Category::find('non-specificato') == null) {
            $category = new Category();
            $category->name = _i('Non Specificato');
            $category->save();
        }

        return $next($request);
    }
}
