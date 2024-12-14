<?php

/*
    Questa non è propriamente una configurazione quanto un parametro
    internamente gestito per tenere traccia della data dell'ultimo promemoria
    per gli ordini mandato nel contesto del GAS.
    Usato nel comando RemindOrders per evitare di inoltrare più volte nello
    stesso giorno le stesse notifiche
*/

namespace App\Parameters\Config;

class LastSentOrderReminder extends Config
{
    public function identifier()
    {
        return 'last_sent_order_reminder';
    }

    public function type()
    {
        return 'string';
    }

    public function default()
    {
        return '1970-01-01';
    }
}
