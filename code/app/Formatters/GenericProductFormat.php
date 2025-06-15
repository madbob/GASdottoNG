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
            'name' => __('texts.user.firstname'),
            'supplier_code' => __('texts.products.code'),
            'measure' => __('texts.generic.measure'),
            'category' => __('texts.generic.category'),
            'price' => __('texts.products.prices.unit'),
            'active' => __('texts.products.bookable'),
            'vat_rate' => __('texts.products.vat_rate'),
            'portion_quantity' => __('texts.products.portion_quantity'),
            'package_size' => __('texts.products.package_size'),
            'weight' => __('texts.generic.weight'),
            'multiple' => __('texts.products.multiple'),
            'min_quantity' => __('texts.products.min_quantity'),
            'max_quantity' => __('texts.products.max_quantity'),
            'max_available' => __('texts.products.available'),
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
