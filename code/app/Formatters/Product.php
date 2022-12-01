<?php

namespace App\Formatters;

class Product extends Formatter
{
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
        return printablePrice($obj->price);
    }

    public static function formatActive($obj, $context)
    {
        return $obj->active ? _i('Si') : _i('No');
    }

    public static function formatVat($obj, $context)
    {
        if ($obj->vat_rate) {
            return $obj->vat_rate->percentage;
        }
        else {
            return _i('Nessuna');
        }
    }

    public static function formatVariable($obj, $context)
    {
        return $obj->variable ? _i('Si') : _i('No');
    }

    public static function formattableColumns($type = null)
    {
        $ret = [
            'name' => (object) [
                'name' => _i('Nome'),
                'checked' => true,
            ],
            'supplier_code' => (object) [
                'name' => _i('Codice Fornitore'),
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
                'format' => 'static::formatPrice',
                'checked' => true,
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
            ],
            'variable' => (object) [
                'name' => _i('Variabile'),
                'format' => 'static::formatVariable',
            ],
            'package_size' => (object) [
                'name' => _i('Dimensione Confezione'),
            ],
            'weight' => (object) [
                'name' => _i('Peso'),
            ],
            'multiple' => (object) [
                'name' => _i('Multiplo'),
            ],
            'min_quantity' => (object) [
                'name' => _i('Minimo'),
            ],
            'max_quantity' => (object) [
                'name' => _i('Massimo Consigliato'),
            ],
            'max_available' => (object) [
                'name' => _i('Disponibile'),
            ],
        ];

        return $ret;
    }

    protected static function children($obj)
    {
        if ($obj->variant_combos->isEmpty() == false) {
            return (object) [
                'formatter' => VariantCombo::class,
                'children' => $obj->variant_combos
            ];
        }
        else {
            return false;
        }
    }
}
