<?php

namespace App\Formatters;

class Product extends Formatter
{
    use GenericProductFormat;

    public static function formatMeasure($obj, $context)
    {
        return $obj->measure->name;
    }

    public static function formatCategory($obj, $context)
    {
        return $obj->category_name;
    }

    public static function formatPrice($obj, $context)
    {
        return printablePrice($obj->getPrice());
    }

    public static function formatActive($obj, $context)
    {
        return $obj->active ? __('texts.generic.yes') : __('texts.generic.no');
    }

    public static function formatVat($obj, $context)
    {
        if ($obj->vat_rate) {
            return $obj->vat_rate->percentage;
        }
        else {
            return __('texts.generic.none');
        }
    }

    public static function formattableColumns($type = null)
    {
        $ret = self::genericColumns();
        $ret['measure']->format = 'static::formatMeasure';
        $ret['category']->format = 'static::formatCategory';
        $ret['price']->format = 'static::formatPrice';
        $ret['active']->format = 'static::formatActive';
        $ret['vat_rate']->format = 'static::formatVat';

        return $ret;
    }

    protected static function children($obj)
    {
        if ($obj->variant_combos->isEmpty() === false) {
            return (object) [
                'formatter' => VariantCombo::class,
                'children' => $obj->variant_combos,
            ];
        }
        else {
            return false;
        }
    }
}
