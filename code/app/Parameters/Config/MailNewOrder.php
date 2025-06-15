<?php

namespace App\Parameters\Config;

class MailNewOrder extends Config
{
    public function identifier()
    {
        return 'mail_new_order';
    }

    public function type()
    {
        return 'object';
    }

    public function default()
    {
        return (object) [
            'subject' => __('mail.order.defaults.subject'),
            'body' => __('mail.order.defaults.body'),
        ];
    }
}
