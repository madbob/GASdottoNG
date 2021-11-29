<?php

namespace App\Parameters\Config;

class MailWelcome extends Config
{
    public function identifier()
    {
        return 'mail_welcome';
    }

    public function type()
    {
        return 'object';
    }

    public function default()
    {
        return (object) [
            'subject' => _i("Benvenuto!"),
            'body' => _i("Benvenuto in %[gas_name]!\nIn futuro potrai accedere usando il link qui sotto, lo username \"%[username]\" e la password da te scelta.\n%[gas_login_link]\nUna mail di notifica è stata inviata agli amministratori."),
        ];
    }
}
