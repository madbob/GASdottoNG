<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class DeliveryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake('it_IT')->company();

        return [
            'name' => $name,
            'address' => sprintf(
                '%s, %s, %s, %s',
                $name,
                fake('it_IT')->streetAddress(),
                fake('it_IT')->state(),
                fake()->randomNumber(5, true)
            ),
            'default' => 0,
        ];
    }
}
