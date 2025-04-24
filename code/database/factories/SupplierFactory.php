<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class SupplierFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake('it_IT')->company(),
            'description' => fake()->paragraph(),
            'order_method' => fake()->sentence(),
            'payment_method' => fake()->creditCardType(),
            'taxcode' => fake('it_IT')->taxId(),
            'vat' => fake('it_IT')->vat(),
        ];
    }

    public function ita(): SupplierFactory
    {
        /**
         * Set the dummy model's attributes for it_IT SupplierFactory.
         *
         * @return array<string, mixed>
         */
        return $this->state(function (): array {
            return [
                'payment_method' => fake()->randomElement(['Contanti', 'Assegno', 'Bonifico bancario', 'PayPal', 'Satispay']),
            ];
        });
    }
}
