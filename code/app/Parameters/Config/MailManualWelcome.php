<?php

namespace App\Parameters\Config;

class MailManualWelcome extends Config
{
    public function identifier()
    {
        return 'mail_manual_welcome';
    }

    public function type()
    {
        return 'object';
    }

    public function default()
    {
        return (object) [
            'subject' => __('texts.mail.approved.defaults.subject'),
            'body' => __('texts.mail.welcome.defaults.body'),
        ];
    }
}
