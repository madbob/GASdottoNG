<?php

namespace App\Parameters\MailTypes;

class Approved extends MailType
{
    public function identifier()
    {
        return 'approved';
    }

    public function description()
    {
        return _i('Messaggio inviato agli iscritti approvati.');
    }

    public function params()
    {
        return [
            'username' => _i('Username assegnato al nuovo utente'),
            'gas_login_link' => _i('Link della pagina di login'),
        ];
    }

    public function enabled($gas)
    {
        return $gas->hasFeature('public_registrations') && $gas->public_registrations['manual'] == true;
    }
}
