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
            'subject' => _i("Ordini in chiusura per %[gas_name]"),
            'body' => _i("Tra pochi giorni si chiuderanno gli ordini aperti da %[gas_name] per i seguenti fornitori:\n\n%[orders_list]"),
        ];
    }
}
