<?php

namespace App\Parameters\MailTypes;

class PasswordReset extends MailType
{
    public function identifier()
    {
        return 'password_reset';
    }

    public function description() {
        return _i('Messaggio per il ripristino della password.');
    }

    public function params() {
        return [
            'username' => _i("Username dell'utente"),
            'gas_reset_link' => _i("Link per il reset della password"),
        ];
    }

    public function enabled($gas) {
        return true;
    }
}
