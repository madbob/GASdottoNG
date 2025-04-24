<?php

namespace Database\Factories;

use App\Delivery;
use App\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ModifierFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'definition' => fake()->randomElement([
                '[{"threshold":' . fake()->randomNumber(3, true) . fake()->creditCardNumber() . ',"amount":"' . fake()->numberBetween(1, int2: 20) . '"}]',
                '[{"threshold":"' . fake()->numberBetween(1, int2: 50) . '","amount":"' . fake()->numberBetween(1, int2: 50) . '"},{"threshold":"' . fake()->numberBetween(1, int2: 20) . '","amount":"' . fake()->numberBetween(1, int2: 20) . '"}]'
            ])
        ];
    }

    /**
     * Configure the factory modifier for a delivery.
     *
     * @param  \App\Delivery  $delivery
     */
    public function shipping(Delivery $delivery): ModifierFactory
    {
        return $this->state(function () use ($delivery): array {
            return [
                'target_type' => Delivery::class,
                'target_id' => $delivery->id,
                'applies_type' => 'booking',
                'applies_target' => 'booking',
            ];
        });
    }

    /**
     * Configure the factory modifier for a products.
     *
     * @param  \App\Product  $product
     */
    public function discount(Product $product): ModifierFactory
    {
        return $this->state(function () use ($product): array {
            return [
                'movement_type_id' => 'sconto',
                'target_type' => Product::class,
                'target_id' => $product->id,
                'value' => fake()->randomElement(['price', 'percentage']),
                'arithmetic' => fake()->randomElement(['sub', 'apply']),
                'scale' => 'major',
                'applies_type' => fake()->randomElement(['quantity', 'none']),
                'applies_target' => fake()->randomElement(['order', 'product']),
            ];
        });
    }
}
