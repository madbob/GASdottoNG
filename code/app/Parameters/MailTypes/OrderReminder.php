<?php

namespace App\Parameters\MailTypes;

class OrderReminder extends MailType
{
    public function identifier()
    {
        return 'order_reminder';
    }

    public function description()
    {
        return _i('Notifica di promemoria per gli ordini in chiusura (inviato agli utenti che hanno esplicitamente abilitato le notifiche per il fornitore).');
    }

    public function params()
    {
        return [
            'orders_list' => _i('Elenco degli ordini in chiusura'),
        ];
    }

    public function enabled($gas)
    {
        return $gas->hasFeature('send_order_reminder');
    }
}
