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
        if ($this->auth->check()) {
            $gas = $this->auth->user()->gas;
        } else {
            $gas = Gas::first();
        }

        if ($gas != null && $gas->has_mail()) {
            $mailconf = $gas->getConfig('mail_conf');
            $conf = json_decode($mailconf);
            $conf->driver = 'smtp';
            $conf->from = array('address' => $conf->address, 'name' => $gas->name);
            $conf->sendmail = '';
            $conf->pretend = false;
            Config::set('mail', (array) $conf);

            /*
                Qua registro il service provider solo dopo aver alterato la
                configurazione
            */
            $app = App::getInstance();
            $app->register('Illuminate\Mail\MailServiceProvider');

            Mail::alwaysFrom($conf->address, $gas->name);
        }

        return $next($request);
    }
}
