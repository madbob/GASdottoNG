<?php

namespace App\Parameters\Config;

class MailPasswordReset extends Config
{
    public function identifier()
    {
        return 'mail_password_reset';
    }

    public function type()
    {
        return 'object';
    }

    public function default()
    {
        return (object) [
            'subject' => __('auth.password_request_link'),
            'body' => __('mail.password.defaults.body'),
        ];
    }
}
