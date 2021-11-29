<?php

namespace App\Parameters\Config;

class AutoSupplierOrderSummary extends Config
{
    public function identifier()
    {
        return 'auto_supplier_order_summary';
    }

    public function type()
    {
        return 'boolean';
    }

    public function default()
    {
        return 0;
    }
}
