<?php

namespace Database\Factories;

use App\Category;
use App\Measure;
use App\Supplier;
use App\VatRate;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    public function definition()
    {
        /**
         * Define the model's default state.
         *
         * @return array<string, mixed>
         */
        return [
            'name' => fake()->word(),
            'supplier_id' => Supplier::factory(),
            'active' => fake()->boolean(),
            'price' => fake()->randomFloat(2, 1, 19),
            'measure_id' => Measure::factory(),
            'category_id' => Category::factory(),
            'portion_quantity' => random_int(1, 4),
            'vat_rate_id' => VatRate::factory(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function ita(): ProductFactory
    {
        /**
         * Set the dummy model's attributes for it_IT ProductFactory.
         *
         * @return array<string, mixed>
         */

        $products = [
            'bevande' => (object) [
                'list' => ['Pespi', 'Dora Cola', 'Fantaasia', 'Sprita', 'Red Devil', 'Momstro', 'Dr Pippo', 'Olio', 'Latte', 'Olio Extra Vergine', 'Vino'],
                'measure' => ['litri', 'pezzi', 'non-specificato']
            ],
            'frutta' => (object) [
                'list' => ['Mela', 'Banana', 'Arancia', 'Mango', 'Pera', 'Pesca', 'Uva', 'Fico', 'Ciliegia', 'Mirtillo'],
                'measure' => ['chili', 'pezzi', 'non-specificato']
            ],
            'verdura' => (object) [
                'list' => ['Spinaci', 'Carote', 'Broccoli', 'Cavolfiore', 'Zucchine', 'Melanzane', 'Peperoni', 'Cipolle', 'Fagiolini', 'Lattuga'],
                'measure' => ['chili', 'pezzi', 'non-specificato']
            ],
            'non-specificato' => (object) [
                'list' => ['Computer', 'Libro', 'Bicicletta', 'Telefono', 'Scarpe', 'Orologio', 'Tavolo', 'Lampada', 'Radio', 'Fotocamera'],
                'measure' => ['pezzi', 'non-specificato']
            ],
            'cosmesi' => (object) [
                'list' => ['Trucco', 'Mascara', 'Rossetto', 'Fondotinta', 'Crema idratante', 'Shampoo', 'Condizionatore', 'Profumo', 'Smalto', 'Creme antirughe'],
                'measure' => ['pezzi', 'non-specificato']
            ]
        ];

        return $this->state(function () use ($products) {
            $category = array_rand($products);
            $products = $products[$category];

            return [
                'name' => fake()->randomElement($products->list),
                'active' => fake()->boolean(),
                'price' => fake()->randomFloat(2, 1, 19),
                'measure_id' => fake()->randomElement($products->measure),
                'category_id' => $category,
                'portion_quantity' => random_int(1, 30),
                'vat_rate_id' => random_int(1, 3),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        });
    }
}
