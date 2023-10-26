<?php

namespace App\Parameters\Config;

class MailDeclined extends Config
{
    public function identifier()
    {
        return 'mail_declined';
    }

    public function type()
    {
        return 'object';
    }

    public function default()
    {
        return (object) [
            'subject' => _i("Non sei stato approvato!"),
            'body' => _i("Spiacente, ma il tuo account non è stato approvato da %[gas_name]."),
        ];
    }
}
