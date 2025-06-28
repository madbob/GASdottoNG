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
            'closing_date' => __('texts.orders.dates.end'),
            'orders_list' => __('texts.mail.reminder.list'),
            'suppliers_list' => __('texts.mail.reminder.suppliers'),
        ];
    }

    public function enabled($gas)
    {
        return $gas->hasFeature('send_order_reminder');
    }
}
