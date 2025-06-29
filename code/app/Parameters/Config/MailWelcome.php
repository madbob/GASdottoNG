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
            'subject' => __('texts.mail.approved.defaults.subject'),
            'body' => __('texts.mail.approved.defaults.body'),
        ];
    }
}
