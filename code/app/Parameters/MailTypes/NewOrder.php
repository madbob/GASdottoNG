<?php

namespace App\Parameters\MailTypes;

class NewOrder extends MailType
{
    public function identifier()
    {
        return 'new_order';
    }

    public function description()
    {
        return __('texts.mail.order.description');
    }

    public function params()
    {
        return [
            'supplier_name' => __('texts.orders.supplier'),
            'order_comment' => __('texts.mail.order.comment'),
            'gas_booking_link' => __('texts.mail.order.link'),
            'contacts' => __('texts.mail.order.mails'),
            'closing_date' => __('texts.orders.dates.end'),
        ];
    }

    public function enabled($gas)
    {
        return true;
    }
}
