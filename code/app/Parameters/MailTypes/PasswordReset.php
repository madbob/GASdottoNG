<?php

namespace App\Parameters\MailTypes;

class PasswordReset extends MailType
{
    public function identifier()
    {
        return 'password_reset';
    }

    public function description()
    {
        return __('texts.mail.password.description');
    }

    public function params()
    {
        return [
            'username' => __('texts.auth.username'),
            'gas_reset_link' => __('texts.mail.password.link'),
        ];
    }

    public function enabled($gas)
    {
        return true;
    }
}
