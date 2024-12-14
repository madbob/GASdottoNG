<?php

namespace App\Http\Middleware;

use Closure;
use LaravelGettext;
use Session;

class SetLanguage
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $locale = currentLang();
        setlocale(LC_TIME, $locale. '.UTF-8');

        /*
            LaravelGettext al suo interno setta un cookie con la locale corrente
            dell'utente. Ma a noi serve sovrascrivere tale impostazione, per
            forzare la configurazione del GAS.
            Non fosse che quando il valore di tale cookie corrisponde alla
            locale del GAS corrente, setLocale() è convinto di aver già caricato
            il file della traduzione dunque non lo ricarica più. Facendo fallire
            tutte le traduzioni seguenti.
            Sicché, qui sovrascrivo brutalmente il valore di tale cookie con un
            valore sempre invalido, in modo da costringere LaravelGettext a
            ricaricare il file
        */
        Session::put('laravel-gettext-locale-locale', 'rotto');
        LaravelGettext::setLocale($locale);

        return $next($request);
    }
}
