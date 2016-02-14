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
                if ($this->auth->check())
                        $gas = $this->auth->user()->gas;
                else
                        $gas = Gas::first();

                if ($gas->mail_conf != '') {
                        $conf = json_decode($gas->mail_conf);
                        $conf->driver = 'smtp';
                        $conf->from = array('address' => $conf->address, 'name' => $gas->name);
                        $conf->sendmail = '';
                        $conf->pretend = false;
                        Config::set('mail', (array) $conf);

                        /*
                                Per rendere effettivi i nuovi parametri occorre
                                rimpiazzare il singleton registrato all'inizio
                                del bootstrap coi parametri salvati sul file

                                cfr. http://laravel.io/forum/07-22-2014-swiftmailer-with-dynamic-mail-configuration
                        */

                        $app = App::getInstance();

                        $app['swift.transport'] = $app->share(function ($app) {
                                return new TransportManager($app);
                        });

                        $mailer = new \Swift_Mailer($app['swift.transport']->driver());
                        Mail::setSwiftMailer($mailer);
                        Mail::alwaysFrom($conf->address, $gas->name);
                }

                return $next($request);
        }
}
