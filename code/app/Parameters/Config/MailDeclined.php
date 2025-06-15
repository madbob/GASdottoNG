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
            'subject' => __('texts.mail.declined.defaults.subject'),
            'body' => __('texts.mail.declined.defaults.body'),
        ];
    }
}
