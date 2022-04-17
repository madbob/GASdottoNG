<?php

namespace App\Parameters\Config;

class ManualProductsSorting extends Config
{
    public function identifier()
    {
        return 'manual_products_sorting';
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
