<?php

/*
    Questo serve da collettore unico per gli attributi di formattazione dei
    prodotti e delle varianti (le quali comunque prelevano i propri attributi
    dal relativo prodotto)
*/

namespace App\Formatters;

trait GenericProductFormat
{
    protected static function genericColumns()
    {
        $attributes = [
            'name' => __('user.firstname'),
            'supplier_code' => __('products.code'),
            'measure' => __('generic.measure'),
            'category' => __('generic.category'),
            'price' => __('products.prices.unit'),
            'active' => __('products.bookable'),
            'vat_rate' => __('products.vat_rate'),
            'portion_quantity' => __('products.portion_quantity'),
            'package_size' => __('products.package_size'),
            'weight' => __('generic.weight'),
            'multiple' => __('products.multiple'),
            'min_quantity' => __('products.min_quantity'),
            'max_quantity' => __('products.max_quantity'),
            'max_available' => __('products.available'),
        ];

        $ret = [];
        $checked_by_default = ['name', 'price'];

        foreach ($attributes as $attr => $label) {
            $ret[$attr] = (object) [
                'name' => $label,
                'checked' => in_array($attr, $checked_by_default),
            ];
        }

        return $ret;
    }
}
