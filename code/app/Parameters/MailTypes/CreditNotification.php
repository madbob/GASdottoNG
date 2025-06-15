<?php

/*
    Questa classe rappresenta la notifica inviabile dal pannello "Stato Crediti"
    in Contabilità.
    Viene ogni volta editata ed inviata in modo esplicito, incluso il contenuto,
    dunque non appare tra quelli implicite e configurabili in "Configurazioni"
*/

namespace App\Parameters\MailTypes;

class CreditNotification extends MailType
{
    public function identifier()
    {
        return 'credit_notification';
    }

    public function description()
    {
        return '';
    }

    public function params()
    {
        return [
            'current_credit' => __('texts.mail.credit.current'),
        ];
    }

    public function enabled($gas)
    {
        return false;
    }
}
