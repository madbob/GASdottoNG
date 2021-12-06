<?php

namespace App\Parameters\Config;

class MailNewOrder extends Config
{
    public function identifier()
    {
        return 'mail_new_order';
    }

    public function type()
    {
        return 'object';
    }

    public function default()
    {
        return (object) [
            'subject' => _i("Nuovo Ordine Aperto per %[supplier_name]"),
            'body' => _i("È stato aperto da %[gas_name] un nuovo ordine per il fornitore %[supplier_name].\nPer partecipare, accedi al seguente indirizzo:\n%[gas_booking_link]\nLe prenotazioni verranno chiuse %[closing_date]"),
        ];
    }
}
