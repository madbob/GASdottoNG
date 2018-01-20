<?php

namespace App\Http\Middleware;

use Illuminate\Contracts\Auth\Guard;
use Illuminate\Mail\TransportManager;
use Closure;
use Mail;
use Config;
use App;
use App\Gas;

/*
    Questo serve a sovrascrivere i parametri di connessione all'SMTP in
    funzione delle configurazioni dell'applicazione. Altrimenti utilizza
    i parametri statici salvati in config/mail.php

    Viene piazzato come middleware in quanto all'interno di un service
    provider sembra complesso verificare se un utente è loggato o meno, e
    dunque i parametri di quale GAS utilizzare
*/

class OverwriteMail
{
    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
    }

    public function handle($request, Closure $next)
    {
        $gas = currentAbsoluteGas();

        if ($gas != null) {
            $conf = (object) $gas->mail;
            $from_address = null;

            if ($conf->driver == 'smtp') {
                $from_address = $conf->address;
                $from_name = $gas->name;
            }
            else if ($conf->driver == 'ses') {
                $from_address = config('services.ses.from.address');
                $from_name = config('services.ses.from.name');
            }

            if ($from_address != null) {
                $conf->from = array('address' => $from_address, 'name' => $from_name);
                $conf->sendmail = '';
                $conf->pretend = false;
                Config::set('mail', (array) $conf);

                /*
                    Qua registro il service provider solo dopo aver alterato la
                    configurazione
                */
                $app = App::getInstance();
                $app->register('Illuminate\Mail\MailServiceProvider');

                Mail::alwaysFrom($from_address, $from_name);
            }
        }

        return $next($request);
    }
}
