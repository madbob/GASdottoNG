<?php

namespace App\Parameters\Config;

class ProductsGridDisplayColumns extends Config
{
    public function identifier()
    {
        return 'products_grid_display_columns';
    }

    public function type()
    {
        return 'array';
    }

    public function default()
    {
        return ['sorting', 'selection', 'name', 'category', 'measure', 'price', 'active'];
    }
}
