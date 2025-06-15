<?php

namespace App\Parameters\Config;

class MailSupplierSummary extends Config
{
    public function identifier()
    {
        return 'mail_supplier_summary';
    }

    public function type()
    {
        return 'object';
    }

    public function default()
    {
        return (object) [
            'subject' => __('mail.supplier.defaults.subject'),
            'body' => __('mail.supplier.defaults.body'),
        ];
    }
}
