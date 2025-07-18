<?php

namespace App\Parameters\Config;

class MailOrderReminder extends Config
{
    public function identifier()
    {
        return 'mail_order_reminder';
    }

    public function type()
    {
        return 'object';
    }

    public function default()
    {
        return (object) [
            'subject' => __('texts.mail.reminder.defaults.subject'),
            'body' => __('texts.mail.reminder.defaults.body'),
        ];
    }
}
