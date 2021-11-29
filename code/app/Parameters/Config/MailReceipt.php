<?php

namespace App\Parameters\Config;

class MailReceipt extends Config
{
    public function identifier()
    {
        return 'mail_receipt';
    }

    public function type()
    {
        return 'object';
    }

    public function default()
    {
        return (object) [
            'subject' => _i("Nuova fattura da %[gas_name]"),
            'body' => _i("In allegato l'ultima fattura da %[gas_name]"),
        ];
    }
}
