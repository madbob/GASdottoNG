<?php

namespace App\Parameters\Config;

class MailPasswordReset extends Config
{
    public function identifier()
    {
        return 'mail_password_reset';
    }

    public function type()
    {
        return 'object';
    }

    public function default()
    {
        return (object) [
            'subject' => _i('Recupero Password'),
            'body' => _i("È stato chiesto l'aggiornamento della tua password su GASdotto.\nClicca il link qui sotto per aggiornare la tua password, o ignora la mail se non hai chiesto tu questa operazione.\n%[gas_reset_link]"),
        ];
    }
}
