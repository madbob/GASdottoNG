<?php

namespace App\Parameters\Config;

class MailOrderReminder extends Config
{
    public function identifier()
    {
        return 'mail_order_reminder';
    }

    public function type()
    {
        return 'object';
    }

    public function default()
    {
        return (object) [
            'subject' => _i("Ordine in chiusura per %[supplier_name]"),
            'body' => _i("Tra pochi giorni si chiuderà l'ordine aperto da %[gas_name] per il fornitore %[supplier_name].\nPer partecipare, accedi al seguente indirizzo:\n%[gas_booking_link]\nLe prenotazioni verranno chiuse %[closing_date]"),
        ];
    }
}
