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
            'subject' => __('texts.auth.password_request_link'),
            'body' => __('texts.mail.password.defaults.body'),
        ];
    }
}
