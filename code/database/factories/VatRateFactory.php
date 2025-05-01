<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class VatRateFactory extends Factory
{
    public function definition(): array
    {
        /**
         * Define the model's default state.
         *
         * @return array<string, mixed>
         */
        return [
            'name' => fake()->word(),
            'percentage' => fake()->numberBetween(4, 40),
        ];
    }
}
