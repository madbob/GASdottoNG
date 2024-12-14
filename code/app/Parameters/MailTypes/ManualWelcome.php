<?php

namespace App\Parameters\MailTypes;

class ManualWelcome extends MailType
{
    public function identifier()
    {
        return 'manual_welcome';
    }

    public function description()
    {
        return _i('Messaggio inviato ai nuovi utenti creati sulla piattaforma.');
    }

    public function params()
    {
        return [
            'username' => _i('Username assegnato al nuovo utente'),
            'gas_access_link' => _i('Link per accedere la prima volta'),
            'gas_login_link' => _i('Link della pagina di login'),
        ];
    }

    public function enabled($gas)
    {
        return true;
    }
}
