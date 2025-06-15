<?php

namespace App\Parameters\MailTypes;

class OrderReminder extends MailType
{
    public function identifier()
    {
        return 'order_reminder';
    }

    public function description()
    {
        return __('texts.mail.reminder.description');
    }

    public function params()
    {
        return [
            'orders_list' => __('texts.mail.reminder.list'),
        ];
    }

    public function enabled($gas)
    {
        return $gas->hasFeature('send_order_reminder');
    }
}
