<?php

namespace App\Formatters;

class VariantCombo extends Formatter
{
    use GenericProductFormat;

    public static function formatName($obj, $context)
    {
        return $obj->printableName();
    }

    public static function formatCode($obj, $context)
    {
        return $obj->code;
    }

    public static function formatMeasure($obj, $context)
    {
        return $obj->product->measure->name;
    }

    public static function formatCategory($obj, $context)
    {
        return $obj->product->category_name;
    }

    public static function formatActive($obj, $context)
    {
        return ($obj->product->active && $obj->active) ? __('generic.yes') : __('generic.no');
    }

    public static function formatPrice($obj, $context)
    {
        return printablePrice($obj->getPrice());
    }

    public static function formatVat($obj, $context)
    {
        if ($obj->product->vat_rate) {
            return $obj->product->vat_rate->percentage;
        }
        else {
            return __('generic.none');
        }
    }

    public static function formatPortion($obj, $context)
    {
        return $obj->product->portion_quantity;
    }

    public static function formatPackage($obj, $context)
    {
        return $obj->product->package_size;
    }

    public static function formatWeight($obj, $context)
    {
        return $obj->product->weight + $obj->weight_offset;
    }

    public static function formatMultipe($obj, $context)
    {
        return $obj->product->multiple;
    }

    public static function formatMinimum($obj, $context)
    {
        return $obj->product->min_quantity;
    }

    public static function formatMaximum($obj, $context)
    {
        return $obj->product->max_quantity;
    }

    public static function formatAvailable($obj, $context)
    {
        return $obj->max_available;
    }

    public static function formattableColumns($type = null)
    {
        $ret = self::genericColumns();
        $ret['name']->format = 'static::formatName';
        $ret['supplier_code']->format = 'static::formatCode';
        $ret['measure']->format = 'static::formatMeasure';
        $ret['category']->format = 'static::formatCategory';
        $ret['price']->format = 'static::formatPrice';
        $ret['active']->format = 'static::formatActive';
        $ret['vat_rate']->format = 'static::formatVat';
        $ret['portion_quantity']->format = 'static::formatPortion';
        $ret['package_size']->format = 'static::formatPackage';
        $ret['weight']->format = 'static::formatWeight';
        $ret['multiple']->format = 'static::formatMultipe';
        $ret['min_quantity']->format = 'static::formatMinimum';
        $ret['max_quantity']->format = 'static::formatMaximum';
        $ret['max_available']->format = 'static::formatAvailable';

        return $ret;
    }
}
