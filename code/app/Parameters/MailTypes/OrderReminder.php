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
        return __('mail.reminder.description');
    }

    public function params()
    {
        return [
            'orders_list' => __('mail.reminder.list'),
        ];
    }

    public function enabled($gas)
    {
        return $gas->hasFeature('send_order_reminder');
    }
}
