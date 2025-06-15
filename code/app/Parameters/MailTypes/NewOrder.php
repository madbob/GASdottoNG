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
        return __('mail.order.description');
    }

    public function params()
    {
        return [
            'supplier_name' => __('orders.supplier'),
            'order_comment' => __('mail.order.comment'),
            'gas_booking_link' => __('mail.order.link'),
            'contacts' => __('mail.order.mails'),
            'closing_date' => __('orders.dates.end'),
        ];
    }

    public function enabled($gas)
    {
        return true;
    }
}
