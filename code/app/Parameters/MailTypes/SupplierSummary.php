<?php

namespace App\Parameters\MailTypes;

class SupplierSummary extends MailType
{
    public function identifier()
    {
        return 'supplier_summary';
    }

    public function description()
    {
        return __('texts.mail.supplier.description');
    }

    public function params()
    {
        return [
            'supplier_name' => __('texts.orders.supplier'),
            'order_number' => __('texts.orders.help.number'),
        ];
    }

    public function enabled($gas)
    {
        return $gas->suppliers()->where('notify_on_close_enabled', '!=', 'none')->count() != 0;
    }
}
