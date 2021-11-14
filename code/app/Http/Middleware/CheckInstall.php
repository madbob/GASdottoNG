<?php

namespace App\Http\Middleware;

use App;
use Log;
use Closure;
use Artisan;

class CheckInstall
{
    public function handle($request, Closure $next)
    {
        /*
            Questa chiave è configurata come default in config/app.php
            Se è ancora quella vuol dire che l'applicazione non è ancora stata
            installata, ed eseguo i vari comandi che servono per inizializzarla
        */

        if (config('app.key') == 'base64:weJMCPc0SVAurD1YEeN7AmGoUuIH2P4qpbgv2zE1sUQ=') {
            Artisan::call('key:generate', ['--force' => true]);
            Artisan::call('migrate', ['--force' => true]);
            Artisan::call('db:seed', ['--force' => true]);
            Artisan::call('db:seed', ['--force' => true, '--class' => 'FirstInstallSeed']);

            if (env('GASDOTTO_NET', false) == true) {
                Artisan::call('db:seed', ['--force' => true, '--class' => 'GasdottoNetSeeder']);
            }

            return redirect(url('/'));
        }

        return $next($request);
    }
}
