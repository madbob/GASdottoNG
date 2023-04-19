<?php

namespace App\Formatters;

class VariantCombo extends Formatter
{
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
        return ($obj->product->active && $obj->active) ? _i('Si') : _i('No');
    }

    public static function formatPrice($obj, $context)
    {
        return printablePrice($obj->product->price + $obj->price_offset);
    }

    public static function formatVat($obj, $context)
    {
        if ($obj->product->vat_rate) {
            return $obj->product->vat_rate->percentage;
        }
        else {
            return _i('Nessuna');
        }
    }

    public static function formatPortion($obj, $context)
    {
        return $obj->product->portion_quantity;
    }

    public static function formatVariable($obj, $context)
    {
        return $obj->product->variable ? _i('Si') : _i('No');
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
        $ret = [
            'name' => (object) [
                'name' => _i('Nome'),
                'checked' => true,
                'format' => 'static::formatName',
            ],
            'supplier_code' => (object) [
                'name' => _i('Codice Fornitore'),
                'format' => 'static::formatCode',
            ],
            'measure' => (object) [
                'name' => _i('Unità di Misura'),
                'format' => 'static::formatMeasure',
            ],
            'category' => (object) [
                'name' => _i('Categoria'),
                'format' => 'static::formatCategory',
            ],
            'price' => (object) [
                'name' => _i('Prezzo Unitario'),
                'checked' => true,
                'format' => 'static::formatPrice',
            ],
            'active' => (object) [
                'name' => _i('Ordinabile'),
                'format' => 'static::formatActive',
            ],
            'vat_rate' => (object) [
                'name' => _i('Aliquota IVA'),
                'format' => 'static::formatVat',
            ],
            'portion_quantity' => (object) [
                'name' => _i('Pezzatura'),
                'format' => 'static::formatPortion',
            ],
            'variable' => (object) [
                'name' => _i('Variabile'),
                'format' => 'static::formatVariable',
            ],
            'package_size' => (object) [
                'name' => _i('Dimensione Confezione'),
                'format' => 'static::formatPackage',
            ],
            'weight' => (object) [
                'name' => _i('Peso'),
                'format' => 'static::formatWeight',
            ],
            'multiple' => (object) [
                'name' => _i('Multiplo'),
                'format' => 'static::formatMultipe',
            ],
            'min_quantity' => (object) [
                'name' => _i('Minimo'),
                'format' => 'static::formatMinimum',
            ],
            'max_quantity' => (object) [
                'name' => _i('Massimo Consigliato'),
                'format' => 'static::formatMaximum',
            ],
            'max_available' => (object) [
                'name' => _i('Disponibile'),
                'format' => 'static::formatAvailable',
            ],
        ];

        return $ret;
    }
}
