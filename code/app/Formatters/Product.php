<?php

namespace App\Formatters;

class Product extends Formatter
{
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
                'format' => function($obj, $context) {
                    return $obj->measure->name;
                },
            ],
            'category' => (object) [
                'name' => _i('Categoria'),
                'format' => function($obj, $context) {
                    return $obj->category_name;
                },
            ],
            'price' => (object) [
                'name' => _i('Prezzo Unitario'),
                'checked' => true,
            ],
            'active' => (object) [
                'name' => _i('Ordinabile'),
                'format' => function($obj, $context) {
                    return $obj->active ? _i('Si') : _i('No');
                },
            ],
            'portion_quantity' => (object) [
                'name' => _i('Pezzatura'),
            ],
            'variable' => (object) [
                'name' => _i('Variabile'),
                'format' => function($obj, $context) {
                    return $obj->variable ? _i('Si') : _i('No');
                },
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
}
