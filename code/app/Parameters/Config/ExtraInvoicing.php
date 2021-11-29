<?php

namespace App\Parameters\Config;

class ExtraInvoicing extends Config
{
    public function identifier()
    {
        return 'extra_invoicing';
    }

    public function type()
    {
        return 'object';
    }

    public function default()
    {
        return (object) [
            'business_name' => '',
            'taxcode' => '',
            'vat' => '',
            'address' => '',
            'invoices_counter' => 0,
            'invoices_counter_year' => date('Y'),
        ];
    }
}
