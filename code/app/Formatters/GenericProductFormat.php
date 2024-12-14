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
            'name' => _i('Nome'),
            'supplier_code' => _i('Codice Fornitore'),
            'measure' => _i('Unità di Misura'),
            'category' => _i('Categoria'),
            'price' => _i('Prezzo Unitario'),
            'active' => _i('Ordinabile'),
            'vat_rate' => _i('Aliquota IVA'),
            'portion_quantity' => _i('Pezzatura'),
            'package_size' => _i('Dimensione Confezione'),
            'weight' => _i('Peso'),
            'multiple' => _i('Multiplo'),
            'min_quantity' => _i('Minimo'),
            'max_quantity' => _i('Massimo Consigliato'),
            'max_available' => _i('Disponibile'),
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
