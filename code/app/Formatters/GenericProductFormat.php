<?php

namespace App\Formatters;

class GenericProductFormat extends Formatter
{
        protected function genericColumns()
        {
            return [
                'name' => (object) [
                    'name' => _i('Nome'),
                    'checked' => true,
                ],
                'supplier_code' => (object) [
                    'name' => _i('Codice Fornitore'),
                ],
                'measure' => (object) [
                    'name' => _i('Unità di Misura'),
                ],
                'category' => (object) [
                    'name' => _i('Categoria'),
                ],
                'price' => (object) [
                    'name' => _i('Prezzo Unitario'),
                    'checked' => true,
                ],
                'active' => (object) [
                    'name' => _i('Ordinabile'),
                ],
                'vat_rate' => (object) [
                    'name' => _i('Aliquota IVA'),
                ],
                'portion_quantity' => (object) [
                    'name' => _i('Pezzatura'),
                ],
                'variable' => (object) [
                    'name' => _i('Variabile'),
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
        }
}
